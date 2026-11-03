<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/customers')->assertRedirect('/login');
        $this->post('/admin/customers/1/login')->assertRedirect('/login');
        $this->post('/impersonation/stop')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_customer_management(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/customers')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/customers/'.$other->id.'/edit')
            ->assertForbidden();

        $this->actingAs($member)
            ->put('/admin/customers/'.$other->id, [
                'name' => 'Changed Name',
                'user_name' => 'GSI3000003',
                'email' => 'changed@example.com',
                'phone_number' => '9876543210',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post('/admin/customers/'.$other->id.'/login')
            ->assertForbidden();
    }

    public function test_the_super_admin_sees_every_account_in_the_table(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'name' => 'Asha Patel',
            'user_name' => 'GSI2000002',
            'email' => 'asha@example.com',
            'phone_number' => '9876543210',
            'sponsor_name' => 'Super Admin',
            'position' => 'left',
        ]);

        $this->actingAs($admin)
            ->get('/admin/customers')
            ->assertOk()
            ->assertSee('Customer Management')
            ->assertSee('Asha Patel')
            ->assertSee('GSI2000002')
            ->assertSee('asha@example.com')
            ->assertSee('9876543210')
            ->assertSee('Super Admin')
            ->assertSee('Left')
            ->assertSee('Customer')
            ->assertSee('Edit')
            ->assertSee('Auto login')
            ->assertSee(route('admin.customers.login', $member), false)
            ->assertDontSee(route('admin.customers.login', $admin), false)
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_search_matches_a_customer_and_hides_the_rest(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->create([
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
        ]);
        User::factory()->create([
            'name' => 'Rohan Das',
            'email' => 'rohan@example.com',
        ]);

        $this->actingAs($admin)
            ->get('/admin/customers?q=Asha')
            ->assertOk()
            ->assertSee('Asha Patel')
            ->assertDontSee('Rohan Das');

        $this->actingAs($admin)
            ->get('/admin/customers?q=nobody-here')
            ->assertOk()
            ->assertSee('No matches')
            ->assertDontSee('Rohan Das');
    }

    public function test_the_super_admin_can_edit_a_customer_without_changing_the_password(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'name' => 'Asha Patel',
            'user_name' => 'GSI2000002',
            'email' => 'asha@example.com',
            'phone_number' => '9876543210',
        ]);

        $this->actingAs($admin)
            ->get('/admin/customers/'.$member->id.'/edit')
            ->assertOk()
            ->assertSee('Edit customer')
            ->assertSee('Asha Patel')
            ->assertSee('Save customer')
            ->assertDontSee('Manage Profile');

        $this->actingAs($admin)
            ->put('/admin/customers/'.$member->id, [
                'name' => 'Asha R. Patel',
                'user_name' => 'GSI2000002',
                'email' => 'asha.patel@example.com',
                'phone_number' => '9123456780',
                'sponsor_name' => 'Super Admin',
                'position' => 'right',
                'address' => '12 Market Road',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'zip_code' => '411001',
                'usdt_bep20' => '0x1111111111111111111111111111111111111111',
            ])
            ->assertRedirect('/admin/customers')
            ->assertSessionHas('status', 'Customer saved.');

        $member->refresh();

        $this->assertSame('Asha R. Patel', $member->name);
        $this->assertSame('asha.patel@example.com', $member->email);
        $this->assertSame('9123456780', $member->phone_number);
        $this->assertSame('right', $member->position);
        $this->assertSame('12 Market Road', $member->address);
        $this->assertSame('Pune', $member->city);
        $this->assertSame('Maharashtra', $member->state);
        $this->assertSame('411001', $member->zip_code);
        $this->assertSame('0x1111111111111111111111111111111111111111', $member->usdt_bep20);
        $this->assertSame('users', $member->user_role);
        $this->assertTrue(Hash::check('password', $member->password));
    }

    public function test_a_new_password_replaces_the_customer_password(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'user_name' => 'GSI2000002',
            'email' => 'asha@example.com',
            'phone_number' => '9876543210',
        ]);

        $this->actingAs($admin)
            ->put('/admin/customers/'.$member->id, [
                'name' => $member->name,
                'user_name' => 'GSI2000002',
                'email' => 'asha@example.com',
                'phone_number' => '9876543210',
                'password' => 'newpassword1',
                'password_confirmation' => 'newpassword1',
            ])
            ->assertRedirect('/admin/customers');

        $this->assertTrue(Hash::check('newpassword1', $member->refresh()->password));
    }

    public function test_a_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'user_name' => 'GSI2000002',
            'email' => 'asha@example.com',
            'phone_number' => '9876543210',
        ]);
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $this->actingAs($admin)
            ->from('/admin/customers/'.$member->id.'/edit')
            ->put('/admin/customers/'.$member->id, [
                'name' => $member->name,
                'user_name' => 'GSI2000002',
                'email' => 'taken@example.com',
                'phone_number' => '9876543210',
            ])
            ->assertRedirect('/admin/customers/'.$member->id.'/edit')
            ->assertSessionHasErrors('email');

        $this->assertSame('asha@example.com', $member->refresh()->email);
    }

    public function test_the_super_admin_email_cannot_be_changed(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->put('/admin/customers/'.$admin->id, [
                'name' => 'Super Admin',
                'user_name' => 'GSI1000001',
                'email' => 'other@software.com',
                'phone_number' => '9876543210',
            ])
            ->assertRedirect('/admin/customers');

        $admin->refresh();

        $this->assertSame('superadmin@software.com', $admin->email);
        $this->assertSame('9876543210', $admin->phone_number);
        $this->assertTrue($admin->isSuperAdmin());
    }

    public function test_auto_login_opens_the_customer_dashboard_and_can_return(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'name' => 'Asha Patel',
            'user_name' => 'GSI2000002',
        ]);

        $this->actingAs($admin)
            ->post('/admin/customers/'.$member->id.'/login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($member);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Signed in as Asha Patel')
            ->assertSee('Back to admin')
            ->assertSee('Manage Profile');

        $this->post('/impersonation/stop')
            ->assertRedirect('/admin/customers')
            ->assertSessionHas('status', 'Signed back in as Super Admin.');

        $this->assertAuthenticatedAs($admin);

        $this->get('/admin/customers')
            ->assertOk()
            ->assertSee('Asha Patel');
    }

    public function test_auto_login_cannot_target_the_super_admin(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post('/admin/customers/'.$admin->id.'/login')
            ->assertForbidden();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_member_cannot_return_to_admin_without_an_auto_login(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($member)
            ->post('/impersonation/stop')
            ->assertForbidden();

        $this->actingAs($member)
            ->withSession(['impersonator_id' => $other->id])
            ->post('/impersonation/stop')
            ->assertForbidden();

        $this->assertAuthenticatedAs($member);
    }
}
