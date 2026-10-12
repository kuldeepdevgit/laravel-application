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
            ->assertSee('Member desk');
    }
}
