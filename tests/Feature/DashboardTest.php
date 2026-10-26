<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            ->assertSee('Green Smart India E Services')
            ->assertSee('images/green-smart-india.png', false)
            ->assertSee('avery@example.com')
            ->assertSee('Ref Link')
            ->assertSee('Manage Profile')
            ->assertSee('Edit Profile')
            ->assertSee('Change Password')
            ->assertSee('Update Wallet Address')
            ->assertSee('Team Report')
            ->assertSee('My Team')
            ->assertSee('My Downline Team')
            ->assertSee('Fund Management')
            ->assertSee('Fund Request (USDT)')
            ->assertSee('Fund Request List')
            ->assertSee('Fund From Admin')
            ->assertSee('Upgrade List')
            ->assertSee('Activation/ Upgrade')
            ->assertSee('Fund Wallet')
            ->assertSee('Income Report')
            ->assertSee('Self ROI Income')
            ->assertSee('Level Income History')
            ->assertSee('Level ROI Income History')
            ->assertSee('Booster Income')
            ->assertSee('Life Time Reward')
            ->assertSee('Salary Income')
            ->assertSee('Withdrawal')
            ->assertSee('Withdrawal List')
            ->assertSee('Withdrawal Now')
            ->assertDontSee('Support')
            ->assertDontSee('Document List')
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
            ->get('/dashboard/my-team')
            ->assertOk()
            ->assertSee('My Team')
            ->assertSee('My Downline Team');

        $this->actingAs($user)
            ->get('/dashboard/fund-wallet')
            ->assertOk()
            ->assertSee('Fund Request (USDT)')
            ->assertSee('Fund Wallet');

        $this->actingAs($user)
            ->get('/dashboard/salary-income')
            ->assertOk()
            ->assertSee('Self ROI Income')
            ->assertSee('Salary Income');

        $this->actingAs($user)
            ->get('/dashboard/withdrawal-now')
            ->assertOk()
            ->assertSee('Withdrawal List')
            ->assertSee('Withdrawal Now');

        $this->actingAs($user)
            ->get('/dashboard/not-a-section')
            ->assertNotFound();
    }

    public function test_edit_profile_shows_the_profile_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Avery Chen',
            'phone_number' => '9958359394',
            'email' => 'avery@example.com',
            'address' => '18 Harbor Lane',
            'city' => 'Portland',
            'state' => 'Oregon',
            'zip_code' => '97201',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/edit-profile')
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('Full Name :')
            ->assertSee('Mobile Number:')
            ->assertSee('Email ID:')
            ->assertSee('Address:')
            ->assertSee('City:')
            ->assertSee('State:')
            ->assertSee('Zip Code:')
            ->assertSee('Submit')
            ->assertSee('value="Avery Chen"', false)
            ->assertSee('value="9958359394"', false)
            ->assertSee('value="avery@example.com"', false)
            ->assertSee('value="18 Harbor Lane"', false)
            ->assertSee('value="Portland"', false)
            ->assertSee('value="Oregon"', false)
            ->assertSee('value="97201"', false);
    }

    public function test_a_member_can_save_profile_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'before@example.com',
        ]);

        $this->actingAs($user)
            ->post('/dashboard/edit-profile', [
                'name' => 'Avery Chen',
                'phone_number' => '9958359394',
                'email' => 'after@example.com',
                'address' => '18 Harbor Lane',
                'city' => 'Portland',
                'state' => 'Oregon',
                'zip_code' => '97201',
            ])
            ->assertRedirect('/dashboard/edit-profile')
            ->assertSessionHas('status', 'Profile saved.');

        $user->refresh();

        $this->assertSame('Avery Chen', $user->name);
        $this->assertSame('9958359394', $user->phone_number);
        $this->assertSame('after@example.com', $user->email);
        $this->assertSame('18 Harbor Lane', $user->address);
        $this->assertSame('Portland', $user->city);
        $this->assertSame('Oregon', $user->state);
        $this->assertSame('97201', $user->zip_code);
    }

    public function test_profile_email_must_stay_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $this->actingAs($user)
            ->from('/dashboard/edit-profile')
            ->post('/dashboard/edit-profile', [
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => 'taken@example.com',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
            ])
            ->assertRedirect('/dashboard/edit-profile')
            ->assertSessionHasErrors('email');

        $this->assertSame('mine@example.com', $user->fresh()->email);
    }

    public function test_change_password_shows_the_password_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard/change-password')
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('New Password :')
            ->assertSee('Confirm Password :')
            ->assertSee('Submit');
    }

    public function test_a_member_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/dashboard/change-password', [
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])
            ->assertRedirect('/dashboard/change-password')
            ->assertSessionHas('status', 'Password saved.');

        $this->assertTrue(Hash::check('new-secret', $user->fresh()->password));
    }

    public function test_a_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/dashboard/change-password')
            ->post('/dashboard/change-password', [
                'password' => 'new-secret',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect('/dashboard/change-password')
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_my_team_lists_direct_members(): void
    {
        $sponsor = User::factory()->create([
            'name' => 'Avery Chen',
            'user_name' => 'GSI6185588',
        ]);
        $byName = User::factory()->create([
            'name' => 'Riley Shaw',
            'user_name' => 'GSI2000001',
            'phone_number' => '9876543210',
            'sponsor_name' => 'Avery Chen',
        ]);
        $byUsername = User::factory()->create([
            'name' => 'Noah Patel',
            'user_name' => 'GSI2000002',
            'phone_number' => '9123456789',
            'sponsor_name' => 'gsi6185588',
        ]);
        User::factory()->create([
            'name' => 'Someone Else',
            'sponsor_name' => 'Other Sponsor',
        ]);

        $this->actingAs($sponsor)
            ->get('/dashboard/my-team')
            ->assertOk()
            ->assertSee('My Team')
            ->assertSee('Full Name')
            ->assertSee('User Name')
            ->assertSee('Phone Number')
            ->assertSee('Self Investment')
            ->assertSee('Status')
            ->assertSee('Join Date')
            ->assertSee('Activation Date')
            ->assertSee('Self+Team RV')
            ->assertSee('Riley Shaw')
            ->assertSee('GSI2000001')
            ->assertSee('9876543210')
            ->assertSee('Noah Patel')
            ->assertSee('GSI2000002')
            ->assertSee('9123456789')
            ->assertSee($byName->created_at->format('M j, Y'))
            ->assertDontSee('Someone Else')
            ->assertDontSee('Other Sponsor');

        $this->assertTrue($byUsername->is($sponsor->directMembers()->last()));
    }

    public function test_update_wallet_address_shows_the_wallet_field(): void
    {
        $user = User::factory()->create([
            'usdt_bep20' => '0x1111111111111111111111111111111111111111',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/update-wallet-address')
            ->assertOk()
            ->assertSee('Update Wallet Address')
            ->assertSee('USDT BEP 20 :')
            ->assertSee('Submit')
            ->assertSee('value="0x1111111111111111111111111111111111111111"', false);
    }

    public function test_a_member_can_save_a_usdt_bep20_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/dashboard/update-wallet-address', [
                'usdt_bep20' => '0x2222222222222222222222222222222222222222',
            ])
            ->assertRedirect('/dashboard/update-wallet-address')
            ->assertSessionHas('status', 'Wallet address saved.');

        $this->assertSame('0x2222222222222222222222222222222222222222', $user->fresh()->usdt_bep20);
    }

    public function test_a_wallet_address_must_be_a_bep20_address(): void
    {
        $user = User::factory()->create([
            'usdt_bep20' => '0x3333333333333333333333333333333333333333',
        ]);

        $this->actingAs($user)
            ->from('/dashboard/update-wallet-address')
            ->post('/dashboard/update-wallet-address', [
                'usdt_bep20' => 'not-an-address',
            ])
            ->assertRedirect('/dashboard/update-wallet-address')
            ->assertSessionHasErrors('usdt_bep20');

        $this->assertSame('0x3333333333333333333333333333333333333333', $user->fresh()->usdt_bep20);
    }
}
