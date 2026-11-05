<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/packages')->assertRedirect('/login');
        $this->get('/admin/packages/upgrades')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_package_management(): void
    {
        $member = User::factory()->create();
        $package = Package::factory()->create();

        $this->actingAs($member)
            ->get('/admin/packages')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/packages/create')
            ->assertForbidden();

        $this->actingAs($member)
            ->post('/admin/packages', [
                'package_name' => 'Starter',
                'package_amount' => '500',
                'package_status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/packages/'.$package->id.'/edit')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/packages/upgrades')
            ->assertForbidden();
    }

    public function test_the_super_admin_can_open_the_package_list(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/packages')
            ->assertOk()
            ->assertSee('Package Management')
            ->assertSee('List package')
            ->assertSee('Upgrade History')
            ->assertSee('Package BV')
            ->assertSee('SILVER PACKAGE')
            ->assertSee('GOLD PACKAGE')
            ->assertSee('DAIMOND')
            ->assertSee('1,500.00')
            ->assertSee('5,000.00')
            ->assertSee('7,000.00')
            ->assertSee('Add package')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_open_upgrade_history(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/packages/upgrades')
            ->assertOk()
            ->assertSee('Package Management')
            ->assertSee('List package')
            ->assertSee('Upgrade History')
            ->assertSee('No upgrades yet')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_add_a_package(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/packages/create')
            ->assertOk()
            ->assertSee('Add package')
            ->assertSee('Package name')
            ->assertSee('Package amount');

        $this->actingAs($admin)
            ->post('/admin/packages', [
                'package_name' => 'Starter',
                'package_amount' => '1500',
                'package_status' => 'active',
            ])
            ->assertRedirect('/admin/packages')
            ->assertSessionHas('status', 'Package added.');

        $package = Package::query()->where('package_name', 'Starter')->first();

        $this->assertNotNull($package);
        $this->assertSame('1500.00', $package->package_amount);
        $this->assertSame('1500.00', $package->package_bv);
        $this->assertSame('active', $package->package_status);

        $this->actingAs($admin)
            ->get('/admin/packages')
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('₹1,500.00')
            ->assertSee('Active');
    }

    public function test_a_package_needs_a_name_and_a_valid_amount(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from('/admin/packages/create')
            ->post('/admin/packages', [
                'package_name' => '',
                'package_amount' => '12.345',
                'package_status' => 'paused',
            ])
            ->assertRedirect('/admin/packages/create')
            ->assertSessionHasErrors(['package_name', 'package_amount', 'package_status']);

        $this->assertSame(3, Package::query()->count());
    }

    public function test_a_package_name_must_be_unique(): void
    {
        Package::factory()->create(['package_name' => 'Starter']);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from('/admin/packages/create')
            ->post('/admin/packages', [
                'package_name' => 'Starter',
                'package_amount' => '500',
                'package_status' => 'active',
            ])
            ->assertRedirect('/admin/packages/create')
            ->assertSessionHasErrors('package_name');
    }

    public function test_the_super_admin_can_edit_a_package(): void
    {
        $package = Package::factory()->create([
            'package_name' => 'Starter',
            'package_amount' => '500.00',
            'package_bv' => '500.00',
            'package_status' => 'active',
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/packages/'.$package->id.'/edit')
            ->assertOk()
            ->assertSee('Edit package')
            ->assertSee('value="Starter"', false)
            ->assertSee('value="500.00"', false);

        $this->actingAs($admin)
            ->put('/admin/packages/'.$package->id, [
                'package_name' => 'Starter',
                'package_amount' => '2500.50',
                'package_status' => 'inactive',
            ])
            ->assertRedirect('/admin/packages')
            ->assertSessionHas('status', 'Package saved.');

        $package->refresh();

        $this->assertSame('Starter', $package->package_name);
        $this->assertSame('2500.50', $package->package_amount);
        $this->assertSame('2500.50', $package->package_bv);
        $this->assertSame('inactive', $package->package_status);
        $this->assertFalse($package->isActive());

        $this->actingAs($admin)
            ->get('/admin/packages')
            ->assertOk()
            ->assertSee('₹2,500.50')
            ->assertSee('Inactive');
    }

    public function test_the_default_packages_store_the_same_amount_and_bv(): void
    {
        $packages = Package::query()->orderBy('package_amount')->get();

        $this->assertSame(
            ['SILVER PACKAGE', 'GOLD PACKAGE', 'DAIMOND'],
            $packages->pluck('package_name')->all(),
        );

        $this->assertSame(
            ['1500.00', '5000.00', '7000.00'],
            $packages->pluck('package_amount')->all(),
        );

        $this->assertSame(
            $packages->pluck('package_amount')->all(),
            $packages->pluck('package_bv')->all(),
        );
    }

    public function test_the_super_admin_can_search_and_delete_a_package(): void
    {
        $starter = Package::factory()->create(['package_name' => 'Starter']);
        Package::factory()->create(['package_name' => 'Growth']);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/packages?q=Start')
            ->assertOk()
            ->assertSee('Starter')
            ->assertDontSee('Growth');

        $this->actingAs($admin)
            ->delete('/admin/packages/'.$starter->id)
            ->assertRedirect('/admin/packages')
            ->assertSessionHas('status', 'Package deleted.');

        $this->assertNull(Package::query()->find($starter->id));
    }
}
