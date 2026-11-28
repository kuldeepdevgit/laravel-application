<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAutoPoolTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AutoPoolManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_auto_pool_tree_table_stores_a_seat_and_is_indexed(): void
    {
        $parent = User::factory()->create();
        $member = User::factory()->create();

        $seat = UserAutoPoolTree::factory()->positionTwo()->gold()->create([
            'user_id' => $member->id,
            'tree_id' => $parent->id,
        ]);

        $this->assertTrue(Schema::hasTable('user_auto_pool_tree'));
        $this->assertTrue(Schema::hasColumns('user_auto_pool_tree', [
            'user_id',
            'tree_id',
            'position',
            'pool_type',
        ]));
        $this->assertFalse(Schema::hasIndex('user_auto_pool_tree', ['user_id', 'pool_type'], 'unique'));
        $this->assertFalse(Schema::hasIndex('user_auto_pool_tree', ['pool_type', 'tree_id', 'position'], 'unique'));
        $this->assertTrue(Schema::hasIndex('user_auto_pool_tree', ['user_id']));
        $this->assertTrue(Schema::hasIndex('user_auto_pool_tree', ['tree_id']));
        $this->assertTrue(Schema::hasIndex('user_auto_pool_tree', ['pool_type', 'tree_id', 'position']));
        $this->assertTrue(Schema::hasIndex('user_auto_pool_tree', ['pool_type']));
        $this->assertTrue(Schema::hasIndex('user_auto_pool_tree', ['pool_type', 'position']));
        $this->assertTrue(Schema::hasForeignKey('user_auto_pool_tree', ['user_id']));
        $this->assertFalse(Schema::hasForeignKey('user_auto_pool_tree', ['tree_id']));

        $treeId = collect(Schema::getColumns('user_auto_pool_tree'))->firstWhere('name', 'tree_id');
        $this->assertTrue(is_array($treeId));
        $this->assertFalse($treeId['nullable'] ?? true);
        $this->assertSame(0, (int) trim((string) ($treeId['default'] ?? ''), "'"));

        $this->assertDatabaseHas('user_auto_pool_tree', [
            'id' => $seat->id,
            'user_id' => $member->id,
            'tree_id' => $parent->id,
            'position' => UserAutoPoolTree::POSITION_TWO,
            'pool_type' => UserAutoPoolTree::POOL_GOLD,
        ]);
        $this->assertTrue($seat->user->is($member));
        $this->assertTrue($seat->tree->is($parent));
        $this->assertSame('Gold', $seat->poolLabel());
    }

    public function test_the_same_member_and_parent_can_be_stored_more_than_once_and_tree_id_defaults_to_zero(): void
    {
        $parent = User::factory()->create();
        $member = User::factory()->create();

        UserAutoPoolTree::factory()->create([
            'user_id' => $member->id,
            'tree_id' => $parent->id,
            'position' => UserAutoPoolTree::POSITION_ONE,
            'pool_type' => UserAutoPoolTree::POOL_SILVER,
        ]);
        UserAutoPoolTree::factory()->positionTwo()->create([
            'user_id' => $member->id,
            'tree_id' => $parent->id,
            'pool_type' => UserAutoPoolTree::POOL_SILVER,
        ]);
        $root = UserAutoPoolTree::factory()->create([
            'user_id' => $member->id,
            'position' => UserAutoPoolTree::POSITION_ONE,
            'pool_type' => UserAutoPoolTree::POOL_GOLD,
        ]);

        $this->assertSame(3, UserAutoPoolTree::query()->where('user_id', $member->id)->count());
        $this->assertSame(2, UserAutoPoolTree::query()->where('tree_id', $parent->id)->count());
        $this->assertSame(0, $root->refresh()->tree_id);
        $this->assertNull($root->tree);
    }

    public function test_guests_and_members_cannot_open_auto_pool_management(): void
    {
        $this->get('/admin/auto-pool')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/auto-pool')
            ->assertForbidden();
    }

    public function test_the_super_admin_sees_auto_pool_seats(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $parent = User::factory()->create([
            'name' => 'Pool Parent',
            'user_name' => 'GSI2000001',
        ]);
        $member = User::factory()->create([
            'name' => 'Pool Member',
            'user_name' => 'GSI2000002',
        ]);

        UserAutoPoolTree::factory()->diamond()->create([
            'user_id' => $member->id,
            'tree_id' => $parent->id,
            'position' => UserAutoPoolTree::POSITION_ONE,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Auto Pool Management')
            ->assertSee('Open auto pool');

        $this->actingAs($admin)
            ->get('/admin/auto-pool')
            ->assertOk()
            ->assertSee('Auto Pool Management')
            ->assertSee('Pool Member')
            ->assertSee('GSI2000002')
            ->assertSee('Diamond')
            ->assertSee('Pool Parent')
            ->assertSee('GSI2000001')
            ->assertSee('>1<', false);

        $this->actingAs($admin)
            ->get('/admin/auto-pool?pool=silver')
            ->assertOk()
            ->assertSee('No matches')
            ->assertDontSee('Pool Member');

        $this->actingAs($admin)
            ->get('/admin/auto-pool?q=GSI2000002')
            ->assertOk()
            ->assertSee('Pool Member')
            ->assertSee('Diamond');
    }

    public function test_auto_pool_management_shows_an_empty_state(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/auto-pool')
            ->assertOk()
            ->assertSee('No auto pool seats yet')
            ->assertSee('Auto Pool Management');
    }
}
