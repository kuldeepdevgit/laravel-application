<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/funds/requests')->assertRedirect('/login');
        $this->get('/admin/funds/address')->assertRedirect('/login');
        $this->get('/admin/funds/send')->assertRedirect('/login');
        $this->get('/admin/funds/history')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_fund_management(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/funds/requests')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/address')->assertForbidden();
        $this->actingAs($member)->put('/admin/funds/address')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/send')->assertForbidden();
        $this->actingAs($member)->post('/admin/funds/send')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/history')->assertForbidden();
    }

    public function test_the_super_admin_can_open_the_fund_request_list(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/funds/requests')
            ->assertOk()
            ->assertSee('Fund Management')
            ->assertSee('Fund Request List')
            ->assertSee('Update Fund Address')
            ->assertSee('Send Fund')
            ->assertSee('Fund Transfer History')
            ->assertSee('No fund requests yet')
            ->assertSee('Amount (USDT)')
            ->assertSee('Wallet Address')
            ->assertDontSee('Manage Profile')
            ->assertDontSee('Ref Link');
    }

    public function test_the_super_admin_can_open_the_fund_address_form(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/funds/address')
            ->assertOk()
            ->assertSee('Update Fund Address')
            ->assertSee('USDT BEP 20')
            ->assertSee('Submit');

        $this->actingAs($admin)
            ->put('/admin/funds/address', [
                'usdt_bep20' => '0x1234567890abcdef1234567890abcdef12345678',
            ])
            ->assertRedirect('/admin/funds/address')
            ->assertSessionHas('status', 'The address form is on screen. Saving is not connected yet.');
    }

    public function test_the_super_admin_can_open_the_send_fund_form(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/funds/send')
            ->assertOk()
            ->assertSee('Send Fund')
            ->assertSee('User name')
            ->assertSee('Amount (USDT)')
            ->assertSee('Remark');

        $this->actingAs($admin)
            ->post('/admin/funds/send', [
                'user_name' => 'memberone',
                'amount' => '25.00',
                'remark' => 'Welcome credit',
            ])
            ->assertRedirect('/admin/funds/send')
            ->assertSessionHas('status', 'The send form is on screen. Sending is not connected yet.');
    }

    public function test_the_super_admin_can_open_fund_transfer_history(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/funds/history')
            ->assertOk()
            ->assertSee('Fund Transfer History')
            ->assertSee('No transfers yet')
            ->assertSee('Amount (USDT)')
            ->assertSee('Remark');
    }
}
