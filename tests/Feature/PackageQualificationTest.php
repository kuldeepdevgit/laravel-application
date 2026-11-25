<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackageQualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PackageQualificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_qualification_table_indexes_the_member_and_package(): void
    {
        $indexes = Schema::getIndexes('users_package_qualification');

        $this->assertTrue(collect($indexes)->contains(fn (array $index): bool => $index['columns'] === ['user_id'] && (bool) $index['unique']));
        $this->assertTrue(collect($indexes)->contains(fn (array $index): bool => $index['columns'] === ['is_qualified']));
        $this->assertTrue(collect($indexes)->contains(fn (array $index): bool => $index['columns'] === ['package_id', 'is_qualified']));
    }

    public function test_a_qualification_row_belongs_to_the_member_and_package(): void
    {
        $user = User::factory()->create();
        $package = Package::query()->where('package_name', 'SILVER PACKAGE')->firstOrFail();

        $row = UserPackageQualification::factory()->create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'self_bv' => 1500,
            'direct_bv' => 4500,
            'is_qualified' => true,
            'qualified_at' => '2026-11-25 10:00:00',
        ]);

        $this->assertTrue($row->user->is($user));
        $this->assertTrue($row->package->is($package));
        $this->assertTrue($user->packageQualification->is($row));
        $this->assertSame('1500.00', $row->self_bv);
        $this->assertSame('4500.00', $row->direct_bv);
        $this->assertTrue($row->is_qualified);
        $this->assertTrue($row->qualifiesFor($package));
    }

    public function test_approving_an_order_stores_the_highest_package_the_member_qualifies_for(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'name' => 'Sponsor One',
            'user_name' => 'GSI7000001',
        ]);
        $direct = User::factory()->create([
            'name' => 'Direct One',
            'user_name' => 'GSI7000002',
            'sponsor_name' => 'GSI7000001',
        ]);
        $otherDirect = User::factory()->create([
            'name' => 'Direct Two',
            'user_name' => 'GSI7000003',
            'sponsor_name' => 'Sponsor One',
        ]);
        $deeper = User::factory()->create([
            'name' => 'Deeper One',
            'user_name' => 'GSI7000004',
            'sponsor_name' => 'GSI7000002',
        ]);
        $silver = Package::query()->where('package_name', 'SILVER PACKAGE')->firstOrFail();
        $gold = Package::query()->where('package_name', 'GOLD PACKAGE')->firstOrFail();
        $diamond = Package::query()->where('package_name', 'DAIMOND')->firstOrFail();

        $sponsorOrder = Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]);
        $directOrder = Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 4500,
        ]);

        $this->confirm($admin, $sponsorOrder);
        $this->assertQualification($sponsor, '1500.00', '0.00', null);

        $this->confirm($admin, $directOrder);
        $this->assertQualification($sponsor, '1500.00', '4500.00', $silver->id);
        $this->assertQualification($direct, '4500.00', '0.00', null);

        $deeperOrder = Order::factory()->for($deeper)->create([
            'status' => 'pending',
            'total_bv' => 21000,
        ]);
        $this->confirm($admin, $deeperOrder);
        $this->assertQualification($sponsor, '1500.00', '4500.00', $silver->id);
        $this->assertQualification($direct, '4500.00', '21000.00', $silver->id);
        $this->assertQualification($deeper, '21000.00', '0.00', null);

        $extraSelf = Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 3500,
        ]);
        $this->confirm($admin, $extraSelf);
        $silverRow = $this->assertQualification($sponsor, '5000.00', '4500.00', $silver->id);

        $this->travel(2)->days();
        $this->confirm($admin, $extraSelf);
        $this->assertTrue($silverRow->qualified_at->equalTo($sponsor->packageQualification()->first()?->qualified_at));
        $this->assertSame(1, UserPackageQualification::query()->where('user_id', $sponsor->id)->count());

        $moreDirect = Order::factory()->for($otherDirect)->create([
            'status' => 'pending',
            'total_bv' => 10500,
        ]);
        $this->confirm($admin, $moreDirect);
        $this->assertQualification($sponsor, '5000.00', '15000.00', $gold->id);

        $restSelf = Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 2000,
        ]);
        $this->confirm($admin, $restSelf);
        $this->assertQualification($sponsor, '7000.00', '15000.00', $gold->id);

        $restDirect = Order::factory()->for($otherDirect)->create([
            'status' => 'pending',
            'total_bv' => 6000,
        ]);
        $this->confirm($admin, $restDirect);
        $this->assertQualification($sponsor, '7000.00', '21000.00', $diamond->id);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$restDirect->id.'/status', ['status' => 'rejected'])
            ->assertRedirect();

        $this->assertQualification($sponsor, '7000.00', '15000.00', $gold->id);
    }

    public function test_both_totals_must_reach_the_package(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $shortDirect = User::factory()->create([
            'name' => 'Short Direct',
            'user_name' => 'GSI7100001',
        ]);
        $shortSelf = User::factory()->create([
            'name' => 'Short Self',
            'user_name' => 'GSI7100002',
        ]);
        $directOfShort = User::factory()->create([
            'sponsor_name' => 'GSI7100001',
        ]);
        $directOfSelf = User::factory()->create([
            'sponsor_name' => 'GSI7100002',
        ]);

        $this->confirm($admin, Order::factory()->for($shortDirect)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]));
        $this->confirm($admin, Order::factory()->for($directOfShort)->create([
            'status' => 'pending',
            'total_bv' => 4499.99,
        ]));
        $this->assertQualification($shortDirect, '1500.00', '4499.99', null);

        $this->confirm($admin, Order::factory()->for($shortSelf)->create([
            'status' => 'pending',
            'total_bv' => 1499.99,
        ]));
        $this->confirm($admin, Order::factory()->for($directOfSelf)->create([
            'status' => 'pending',
            'total_bv' => 4500,
        ]));
        $this->assertQualification($shortSelf, '1499.99', '4500.00', null);
    }

    public function test_an_inactive_package_is_skipped(): void
    {
        Package::query()->where('package_name', 'SILVER PACKAGE')->update([
            'package_status' => 'inactive',
        ]);

        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'user_name' => 'GSI7200001',
        ]);
        $direct = User::factory()->create([
            'sponsor_name' => 'GSI7200001',
        ]);
        $gold = Package::query()->where('package_name', 'GOLD PACKAGE')->firstOrFail();

        $this->confirm($admin, Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]));
        $this->confirm($admin, Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 4500,
        ]));
        $this->assertQualification($sponsor, '1500.00', '4500.00', null);

        $this->confirm($admin, Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 3500,
        ]));
        $this->confirm($admin, Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 10500,
        ]));
        $this->assertQualification($sponsor, '5000.00', '15000.00', $gold->id);
    }

    public function test_rejecting_a_pending_order_or_approving_a_guest_order_does_not_write_a_row(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create();
        $pending = Order::factory()->for($member)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]);
        $guest = Order::factory()->create([
            'user_id' => null,
            'status' => 'pending',
            'total_bv' => 7000,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$pending->id.'/status', ['status' => 'rejected'])
            ->assertRedirect();
        $this->confirm($admin, $guest);

        $this->assertDatabaseCount('users_package_qualification', 0);
    }

    public function test_a_sponsor_without_a_package_is_not_qualified_by_direct_bv_alone(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'user_name' => 'GSI7400001',
        ]);
        $direct = User::factory()->create([
            'sponsor_name' => 'GSI7400001',
        ]);

        $this->confirm($admin, Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 21000,
        ]));

        $this->assertQualification($sponsor, '0.00', '21000.00', null);
    }

    public function test_approval_uses_the_direct_bv_saved_on_the_package(): void
    {
        Package::query()->where('package_name', 'SILVER PACKAGE')->update([
            'direct_bv' => '100.00',
        ]);

        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'user_name' => 'GSI7500001',
        ]);
        $direct = User::factory()->create([
            'sponsor_name' => 'GSI7500001',
        ]);
        $silver = Package::query()->where('package_name', 'SILVER PACKAGE')->firstOrFail();

        $this->confirm($admin, Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]));
        $this->confirm($admin, Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 100,
        ]));

        $this->assertQualification($sponsor, '1500.00', '100.00', $silver->id);
    }

    public function test_the_qualification_section_shows_each_package_and_the_saved_result(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'name' => 'Sponsor One',
            'user_name' => 'GSI7300001',
        ]);
        $direct = User::factory()->create([
            'sponsor_name' => 'GSI7300001',
        ]);

        $this->actingAs($sponsor)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Qualification');

        $this->actingAs($sponsor)
            ->get('/dashboard/qualification')
            ->assertOk()
            ->assertSee('Qualification')
            ->assertSee('They qualify when they hold a package')
            ->assertSee('SILVER PACKAGE')
            ->assertSee('GOLD PACKAGE')
            ->assertSee('DAIMOND')
            ->assertSee('1,500.00')
            ->assertSee('4,500.00')
            ->assertSee('15,000.00')
            ->assertSee('21,000.00')
            ->assertSee('Not qualified')
            ->assertSee('0.00')
            ->assertDontSee('>Qualified<', false);

        $this->confirm($admin, Order::factory()->for($sponsor)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]));
        $this->confirm($admin, Order::factory()->for($direct)->create([
            'status' => 'pending',
            'total_bv' => 4500,
        ]));

        $this->actingAs($sponsor)
            ->get('/dashboard/qualification')
            ->assertOk()
            ->assertSeeInOrder(['SILVER PACKAGE', 'Qualified'])
            ->assertSeeInOrder(['GOLD PACKAGE', 'Not qualified'])
            ->assertSeeInOrder(['DAIMOND', 'Not qualified'])
            ->assertSee('4,500.00');
    }

    private function confirm(User $admin, Order $order): void
    {
        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();
    }

    private function assertQualification(User $user, string $selfBv, string $directBv, ?int $packageId): UserPackageQualification
    {
        $row = $user->packageQualification()->first();

        $this->assertInstanceOf(UserPackageQualification::class, $row);
        $this->assertSame($selfBv, $row->self_bv);
        $this->assertSame($directBv, $row->direct_bv);
        $this->assertSame($packageId, $row->package_id);
        $this->assertSame($packageId !== null, $row->is_qualified);

        if ($packageId === null) {
            $this->assertNull($row->qualified_at);
        } else {
            $this->assertNotNull($row->qualified_at);
        }

        return $row;
    }
}
