<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_a_member_sees_their_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Avery Chen',
            'email' => 'avery@example.com',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome back, Avery Chen')
            ->assertSee('avery@example.com')
            ->assertSee('Ref Link')
            ->assertSee('Manage Profile')
            ->assertSee('Edit Profile')
            ->assertSee('Change Password')
            ->assertSee('Update Wallet Address')
            ->assertSee('Team Report')
            ->assertSee('Fund Management')
            ->assertSee('Income Report')
            ->assertSee('Withdrawal')
            ->assertSee('Support')
            ->assertSee('Document List')
            ->assertSee('Logout')
            ->assertDontSee('Overview')
            ->assertDontSee('Member desk');
    }

    public function test_menu_sections_open_for_a_member(): void
    {
        $user = User::factory()->create([
            'user_name' => 'GSI6185588',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/ref-link')
            ->assertOk()
            ->assertSee('Ref Link')
            ->assertSee(route('register', ['ref' => 'GSI6185588']), false);

        $this->actingAs($user)
            ->get('/dashboard/edit-profile')
            ->assertOk()
            ->assertSee('Edit Profile')
            ->assertSee('Change Password')
            ->assertSee('Update Wallet Address');

        $this->actingAs($user)
            ->get('/dashboard/not-a-section')
            ->assertNotFound();
    }
}
