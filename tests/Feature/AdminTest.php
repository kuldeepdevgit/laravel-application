<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/categories')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_the_admin_layout(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/categories')
            ->assertForbidden();
    }

    public function test_the_super_admin_identity_must_match_id_email_and_role(): void
    {
        $wrongEmail = User::factory()->create([
            'email' => 'other@software.com',
            'user_role' => 'super_admin',
        ]);

        $this->assertSame(1, $wrongEmail->id);
        $this->assertFalse($wrongEmail->isSuperAdmin());
        $this->actingAs($wrongEmail)->get('/admin')->assertForbidden();
    }

    public function test_the_matching_email_is_not_enough_without_the_super_admin_role(): void
    {
        $wrongRole = User::factory()->superAdmin()->create([
            'user_role' => 'users',
        ]);

        $this->assertSame(1, $wrongRole->id);
        $this->assertSame('superadmin@software.com', $wrongRole->email);
        $this->assertFalse($wrongRole->isSuperAdmin());
        $this->actingAs($wrongRole)->get('/admin')->assertForbidden();
        $this->actingAs($wrongRole)->get('/dashboard')->assertOk()->assertDontSee('Category');
    }

    public function test_a_later_account_with_the_same_email_shape_is_not_the_super_admin(): void
    {
        User::factory()->create();

        $imposter = User::factory()->create([
            'email' => 'superadmin@software.com',
            'user_role' => 'super_admin',
        ]);

        $this->assertNotSame(1, $imposter->id);
        $this->assertFalse($imposter->isSuperAdmin());
        $this->actingAs($imposter)->get('/admin')->assertForbidden();
    }

    public function test_the_super_admin_sees_the_admin_layout_and_category(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->assertTrue($admin->isSuperAdmin());

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Welcome back, Super Admin')
            ->assertSee('superadmin@software.com')
            ->assertSee('Super Admin')
            ->assertSee('Category')
            ->assertSee('Open categories')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link')
            ->assertDontSee('My Team');

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/admin');
    }

    public function test_the_super_admin_is_sent_to_the_admin_layout_after_login(): void
    {
        User::factory()->superAdmin()->create([
            'password' => 'password1',
        ]);

        $this->post('/login', [
            'email' => 'superadmin@software.com',
            'password' => 'password1',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }
}
