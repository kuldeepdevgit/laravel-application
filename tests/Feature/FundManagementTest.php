<?php

namespace Tests\Feature;

use App\Models\FundAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $address = FundAddress::factory()->create();

        $this->actingAs($member)->get('/admin/funds/requests')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/address')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/address/create')->assertForbidden();
        $this->actingAs($member)->post('/admin/funds/address')->assertForbidden();
        $this->actingAs($member)->get('/admin/funds/address/'.$address->id.'/edit')->assertForbidden();
        $this->actingAs($member)->put('/admin/funds/address/'.$address->id)->assertForbidden();
        $this->actingAs($member)->delete('/admin/funds/address/'.$address->id)->assertForbidden();
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

    public function test_the_super_admin_can_add_usdt_and_inr_fund_addresses(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $wallet = '0x1234567890abcdef1234567890abcdef12345678';

        $this->actingAs($admin)
            ->get('/admin/funds/address')
            ->assertOk()
            ->assertSee('No fund addresses yet')
            ->assertSee('Add address');

        $this->actingAs($admin)
            ->get('/admin/funds/address/create')
            ->assertOk()
            ->assertSee('Add fund address')
            ->assertSee('Wallet address')
            ->assertSee('Wallet screenshot')
            ->assertSee('Bank name')
            ->assertSee('IFSC code');

        $this->actingAs($admin)
            ->post('/admin/funds/address', [
                'address_type' => 'usdt',
                'wallet_address' => $wallet,
                'wallet_screenshot' => UploadedFile::fake()->image('wallet.jpg', 800, 500),
            ])
            ->assertRedirect('/admin/funds/address')
            ->assertSessionHas('status', 'Fund address added.');

        $this->actingAs($admin)
            ->post('/admin/funds/address', [
                'address_type' => 'inr',
                'bank_name' => 'State Bank',
                'account_name' => 'Green Smart India',
                'account_number' => '123456789012',
                'ifsc_code' => 'sbin0001234',
            ])
            ->assertRedirect('/admin/funds/address')
            ->assertSessionHas('status', 'Fund address added.');

        $usdt = FundAddress::query()->where('address_type', 'usdt')->first();
        $inr = FundAddress::query()->where('address_type', 'inr')->first();

        $this->assertNotNull($usdt);
        $this->assertSame($wallet, $usdt->wallet_address);
        $this->assertNotNull($usdt->wallet_screenshot);
        Storage::disk('public')->assertExists($usdt->wallet_screenshot);

        $this->assertNotNull($inr);
        $this->assertSame('State Bank', $inr->bank_name);
        $this->assertSame('SBIN0001234', $inr->ifsc_code);
        $this->assertNull($inr->wallet_address);

        $this->actingAs($admin)
            ->get('/admin/funds/address')
            ->assertOk()
            ->assertSee($wallet)
            ->assertSee('State Bank')
            ->assertSee('123456789012')
            ->assertSee('Edit')
            ->assertSee('Delete');
    }

    public function test_a_fund_address_needs_the_fields_for_its_type(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from('/admin/funds/address/create')
            ->post('/admin/funds/address', [
                'address_type' => 'usdt',
                'wallet_address' => 'not-a-wallet',
            ])
            ->assertRedirect('/admin/funds/address/create')
            ->assertSessionHasErrors(['wallet_address', 'wallet_screenshot']);

        $this->actingAs($admin)
            ->from('/admin/funds/address/create')
            ->post('/admin/funds/address', [
                'address_type' => 'inr',
                'bank_name' => '',
                'account_number' => '12',
                'ifsc_code' => 'BAD',
            ])
            ->assertRedirect('/admin/funds/address/create')
            ->assertSessionHasErrors(['bank_name', 'account_name', 'account_number', 'ifsc_code']);

        $this->assertSame(0, FundAddress::query()->count());
    }

    public function test_the_super_admin_can_edit_and_delete_a_fund_address(): void
    {
        Storage::fake('public');
        $screenshot = UploadedFile::fake()->image('wallet.jpg')->store('fund-addresses', 'public');
        $address = FundAddress::factory()->create([
            'wallet_address' => '0x1234567890abcdef1234567890abcdef12345678',
            'wallet_screenshot' => $screenshot,
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/funds/address/'.$address->id.'/edit')
            ->assertOk()
            ->assertSee('Edit fund address')
            ->assertSee('value="0x1234567890abcdef1234567890abcdef12345678"', false);

        $this->actingAs($admin)
            ->put('/admin/funds/address/'.$address->id, [
                'address_type' => 'inr',
                'bank_name' => 'HDFC Bank',
                'account_name' => 'Green Smart India',
                'account_number' => '998877665544',
                'ifsc_code' => 'HDFC0001234',
            ])
            ->assertRedirect('/admin/funds/address')
            ->assertSessionHas('status', 'Fund address saved.');

        $address->refresh();
        $this->assertSame('inr', $address->address_type);
        $this->assertSame('HDFC Bank', $address->bank_name);
        $this->assertNull($address->wallet_screenshot);
        Storage::disk('public')->assertMissing($screenshot);

        $this->actingAs($admin)
            ->get('/admin/funds/address?q=HDFC')
            ->assertOk()
            ->assertSee('HDFC Bank')
            ->assertDontSee('0x1234567890abcdef1234567890abcdef12345678');

        $this->actingAs($admin)
            ->delete('/admin/funds/address/'.$address->id)
            ->assertRedirect('/admin/funds/address')
            ->assertSessionHas('status', 'Fund address deleted.');

        $this->assertNull(FundAddress::query()->find($address->id));
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
