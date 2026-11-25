<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Product;
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
        $this->get('/dashboard/order-history')->assertRedirect('/login');
        $this->get('/dashboard/orders/1')->assertRedirect('/login');
    }

    public function test_a_member_sees_their_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Avery Chen',
            'email' => 'avery@example.com',
            'user_name' => 'GSI2000002',
            'sponsor_name' => 'Super Admin',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('User ID')
            ->assertSee('GSI2000002')
            ->assertSee('Avery Chen')
            ->assertSee('Sponsor')
            ->assertSee('Super Admin')
            ->assertDontSee('Welcome back, Avery Chen')
            ->assertSee('Green Smart India E Services')
            ->assertSee('images/green-smart-india.png', false)
            ->assertSee('avery@example.com')
            ->assertSee('Ref Link')
            ->assertSee('Order History')
            ->assertDontSee('Category')
            ->assertDontSee('Company')
            ->assertSee('Manage Profile')
            ->assertSee('Edit Profile')
            ->assertSee('Change Password')
            ->assertSee('Update Wallet Address')
            ->assertSee('Team Report')
            ->assertSee('Qualification')
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
            ->assertSee('Franchise Income History')
            ->assertSee('3x Matrix Level Income')
            ->assertSeeInOrder(['Franchise Income History', '3x Matrix Level Income', 'Wallet', 'Self ROI Income'])
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
            ->assertDontSee('Member desk')
            ->assertSee('Account status')
            ->assertSee('Inactive')
            ->assertSee('Not activated');
    }

    public function test_a_paid_member_is_shown_as_active(): void
    {
        $user = User::factory()->create([
            'user_paid' => User::PAID,
            'activation_date' => '2026-03-04',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Account status')
            ->assertSee('Active')
            ->assertSee('Mar 4, 2026')
            ->assertDontSee('Inactive')
            ->assertDontSee('Not activated');
    }

    public function test_dashboard_shows_the_package_for_the_members_current_bv(): void
    {
        $none = User::factory()->create();
        $silver = User::factory()->create();
        $between = User::factory()->create();
        $gold = User::factory()->create();
        $diamond = User::factory()->create();
        $other = User::factory()->create();

        Order::factory()->for($silver)->create([
            'status' => 'confirmed',
            'total_bv' => 1500,
        ]);
        Order::factory()->for($between)->create([
            'status' => 'confirmed',
            'total_bv' => 2000,
        ]);
        Order::factory()->for($gold)->create([
            'status' => 'pending',
            'total_bv' => 9000,
        ]);
        Order::factory()->for($gold)->create([
            'status' => 'confirmed',
            'total_bv' => 5000,
        ]);
        Order::factory()->for($diamond)->create([
            'status' => 'confirmed',
            'total_bv' => 1500,
        ]);
        Order::factory()->for($diamond)->create([
            'status' => 'confirmed',
            'total_bv' => 5500,
        ]);
        Order::factory()->for($diamond)->create([
            'status' => 'rejected',
            'total_bv' => 5000,
        ]);
        Order::factory()->for($other)->create([
            'status' => 'confirmed',
            'total_bv' => 7000,
        ]);

        $this->actingAs($none)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Current BV')
            ->assertSee('0.00')
            ->assertSee('Package')
            ->assertDontSee('SILVER PACKAGE')
            ->assertDontSee('GOLD PACKAGE')
            ->assertDontSee('DAIMOND');

        $this->actingAs($silver)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('1,500.00')
            ->assertSee('SILVER PACKAGE')
            ->assertSee('Active')
            ->assertDontSee('GOLD PACKAGE')
            ->assertDontSee('DAIMOND');

        $this->actingAs($between)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('2,000.00')
            ->assertSee('SILVER PACKAGE')
            ->assertDontSee('GOLD PACKAGE');

        $this->actingAs($gold)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('5,000.00')
            ->assertSee('GOLD PACKAGE')
            ->assertDontSee('SILVER PACKAGE')
            ->assertDontSee('DAIMOND');

        $this->actingAs($diamond)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('7,000.00')
            ->assertSee('DAIMOND')
            ->assertDontSee('SILVER PACKAGE')
            ->assertDontSee('GOLD PACKAGE');

        Package::query()->where('package_name', 'SILVER PACKAGE')->update([
            'package_status' => 'inactive',
        ]);

        $this->actingAs($silver)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('1,500.00')
            ->assertDontSee('SILVER PACKAGE');
    }

    public function test_dashboard_shows_the_sponsor_id_and_name(): void
    {
        $sponsor = User::factory()->create([
            'name' => 'Priya Shah',
            'user_name' => 'GSI1000008',
        ]);

        $byId = User::factory()->create([
            'name' => 'Avery Chen',
            'user_name' => 'GSI2000003',
            'sponsor_name' => 'GSI1000008',
        ]);

        $this->actingAs($byId)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('GSI2000003')
            ->assertSee('Avery Chen')
            ->assertSee('Priya Shah')
            ->assertSee('GSI1000008');

        $byName = User::factory()->create([
            'name' => 'Jordan Lee',
            'user_name' => 'GSI2000004',
            'sponsor_name' => 'Priya Shah',
        ]);

        $this->actingAs($byName)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Jordan Lee')
            ->assertSee('Priya Shah')
            ->assertSee('GSI1000008');

        $this->assertTrue($sponsor->is($byId->sponsor()));
        $this->assertTrue($sponsor->is($byName->sponsor()));
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
            ->assertSee('Franchise Income History')
            ->assertSee('3x Matrix Level Income')
            ->assertSeeInOrder(['Franchise Income History', '3x Matrix Level Income', 'Wallet', 'Self ROI Income'])
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
            'user_paid' => User::UNPAID,
            'activation_date' => null,
        ]);
        $byUsername = User::factory()->create([
            'name' => 'Noah Patel',
            'user_name' => 'GSI2000002',
            'phone_number' => '9123456789',
            'sponsor_name' => 'gsi6185588',
            'user_paid' => User::PAID,
            'activation_date' => '2026-03-04',
        ]);
        User::factory()->create([
            'name' => 'Someone Else',
            'sponsor_name' => 'Other Sponsor',
        ]);
        Order::factory()->for($byName)->create([
            'status' => 'rejected',
            'total_bv' => 2500,
        ]);
        Order::factory()->for($byUsername)->create([
            'status' => 'confirmed',
            'total_bv' => 1500,
        ]);
        Order::factory()->for($byUsername)->create([
            'status' => 'pending',
            'total_bv' => 4000,
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
            ->assertDontSee('Self+Team RV')
            ->assertSeeInOrder(['Riley Shaw', 'GSI2000001', '9876543210', '0.00', 'Inactive', $byName->created_at->format('M j, Y')])
            ->assertSeeInOrder(['Noah Patel', 'GSI2000002', '9123456789', '1,500.00', 'Active', 'Mar 4, 2026'])
            ->assertDontSee('4,000.00')
            ->assertDontSee('2,500.00')
            ->assertDontSee('Someone Else')
            ->assertDontSee('Other Sponsor');

        $this->assertTrue($byUsername->is($sponsor->directMembers()->last()));
    }

    public function test_my_downline_team_counts_ten_levels(): void
    {
        $root = User::factory()->create([
            'name' => 'Avery Chen',
            'user_name' => 'GSI6185588',
        ]);
        $direct = User::factory()->create([
            'name' => 'Riley Shaw',
            'user_name' => 'GSI2000001',
            'phone_number' => '9876543210',
            'sponsor_name' => 'gsi6185588',
        ]);
        User::factory()->create([
            'name' => 'Noah Patel',
            'user_name' => 'GSI2000002',
            'phone_number' => '9123456789',
            'sponsor_name' => 'Riley Shaw',
        ]);
        User::factory()->create([
            'name' => 'Second Branch',
            'user_name' => 'GSI2000003',
            'sponsor_name' => 'GSI2000001',
        ]);

        $sponsorName = 'GSI2000002';

        for ($level = 3; $level <= 11; $level++) {
            $userName = 'GSI4'.str_pad((string) $level, 6, '0', STR_PAD_LEFT);
            User::factory()->create([
                'name' => 'Level '.$level.' Member',
                'user_name' => $userName,
                'sponsor_name' => $sponsorName,
            ]);
            $sponsorName = $userName;
        }

        User::factory()->create([
            'name' => 'Someone Else',
            'sponsor_name' => 'Other Sponsor',
        ]);

        $downline = $root->downlineMembers();

        $this->assertCount(11, $downline);
        $this->assertTrue($direct->is($downline->first()));
        $this->assertSame(1, (int) $downline->first()->getAttribute('downline_level'));
        $this->assertSame(2, $downline->where('downline_level', 2)->count());
        $this->assertSame(10, (int) $downline->firstWhere('name', 'Level 10 Member')?->getAttribute('downline_level'));
        $this->assertNull($downline->firstWhere('name', 'Level 11 Member'));

        $this->actingAs($root)
            ->get('/dashboard/my-downline-team')
            ->assertOk()
            ->assertSee('My Downline Team')
            ->assertSee('counted through 10 levels')
            ->assertSee('11')
            ->assertSee('members across 10 levels')
            ->assertSee('Level 1')
            ->assertSee('Level 10')
            ->assertDontSee('Level 11')
            ->assertSee('Riley Shaw')
            ->assertSee('GSI2000001')
            ->assertSee('9876543210')
            ->assertSee('0.00')
            ->assertDontSee('Self+Team RV')
            ->assertSee('Noah Patel')
            ->assertSee('Second Branch')
            ->assertSee('Level 10 Member')
            ->assertDontSee('Level 11 Member')
            ->assertDontSee('Someone Else');
    }

    public function test_downline_counting_stops_when_sponsors_point_at_each_other(): void
    {
        $first = User::factory()->create([
            'name' => 'Member A',
            'user_name' => 'GSI3000001',
            'sponsor_name' => 'GSI3000002',
        ]);
        $second = User::factory()->create([
            'name' => 'Member B',
            'user_name' => 'GSI3000002',
            'sponsor_name' => 'GSI3000001',
        ]);

        $downline = $first->downlineMembers();

        $this->assertCount(1, $downline);
        $this->assertTrue($second->is($downline->first()));
        $this->assertSame(1, (int) $downline->first()->getAttribute('downline_level'));
    }

    public function test_update_wallet_address_shows_the_wallet_field(): void
    {
        $user = User::factory()->create([
            'usdt_bep20' => '0x1111111111111111111111111111111111111111',
            'bank_name' => 'State Bank of India',
            'account_name' => 'Asha Rao',
            'account_number' => '123456789012',
            'ifsc_code' => 'SBIN0001234',
            'google_pay' => 'asha@okaxis',
            'phonepe' => 'asha@ybl',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/update-wallet-address')
            ->assertOk()
            ->assertSee('Update Wallet Address')
            ->assertSee('USDT BEP 20 :')
            ->assertSee('Bank name :')
            ->assertSee('Account name :')
            ->assertSee('Account number :')
            ->assertSee('IFSC code :')
            ->assertSee('Google Pay :')
            ->assertSee('PhonePe :')
            ->assertSee('Submit')
            ->assertSee('value="0x1111111111111111111111111111111111111111"', false)
            ->assertSee('value="State Bank of India"', false)
            ->assertSee('value="Asha Rao"', false)
            ->assertSee('value="123456789012"', false)
            ->assertSee('value="SBIN0001234"', false)
            ->assertSee('value="asha@okaxis"', false)
            ->assertSee('value="asha@ybl"', false);
    }

    public function test_a_member_can_save_a_usdt_bep20_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/dashboard/update-wallet-address', [
                'usdt_bep20' => '0x2222222222222222222222222222222222222222',
            ])
            ->assertRedirect('/dashboard/update-wallet-address')
            ->assertSessionHas('status', 'Wallet and bank details saved.');

        $this->assertSame('0x2222222222222222222222222222222222222222', $user->fresh()->usdt_bep20);
    }

    public function test_a_member_can_save_bank_google_pay_and_phonepe_details(): void
    {
        $user = User::factory()->create([
            'usdt_bep20' => '0x2222222222222222222222222222222222222222',
        ]);

        $this->actingAs($user)
            ->post('/dashboard/update-wallet-address', [
                'usdt_bep20' => '0x2222222222222222222222222222222222222222',
                'bank_name' => 'HDFC Bank',
                'account_name' => 'Asha Rao',
                'account_number' => '50100123456789',
                'ifsc_code' => 'hdfc0001234',
                'google_pay' => '+91 98765 43210',
                'phonepe' => 'asha.rao@ybl',
            ])
            ->assertRedirect('/dashboard/update-wallet-address')
            ->assertSessionHas('status', 'Wallet and bank details saved.');

        $user->refresh();

        $this->assertSame('0x2222222222222222222222222222222222222222', $user->usdt_bep20);
        $this->assertSame('HDFC Bank', $user->bank_name);
        $this->assertSame('Asha Rao', $user->account_name);
        $this->assertSame('50100123456789', $user->account_number);
        $this->assertSame('HDFC0001234', $user->ifsc_code);
        $this->assertSame('9876543210', $user->google_pay);
        $this->assertSame('asha.rao@ybl', $user->phonepe);
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

    public function test_bank_and_upi_details_must_be_complete_and_valid(): void
    {
        $user = User::factory()->create([
            'usdt_bep20' => '0x3333333333333333333333333333333333333333',
            'bank_name' => 'HDFC Bank',
            'account_name' => 'Asha Rao',
            'account_number' => '50100123456789',
            'ifsc_code' => 'HDFC0001234',
            'google_pay' => 'asha@okaxis',
            'phonepe' => '9876543210',
        ]);

        $this->actingAs($user)
            ->from('/dashboard/update-wallet-address')
            ->post('/dashboard/update-wallet-address', [
                'usdt_bep20' => '0x3333333333333333333333333333333333333333',
                'bank_name' => 'HDFC Bank',
                'account_number' => '12AB',
                'ifsc_code' => 'not-ifsc',
                'google_pay' => 'not an upi',
                'phonepe' => '12345',
            ])
            ->assertRedirect('/dashboard/update-wallet-address')
            ->assertSessionHasErrors(['account_name', 'account_number', 'ifsc_code', 'google_pay', 'phonepe']);

        $user->refresh();

        $this->assertSame('HDFC Bank', $user->bank_name);
        $this->assertSame('Asha Rao', $user->account_name);
        $this->assertSame('50100123456789', $user->account_number);
        $this->assertSame('HDFC0001234', $user->ifsc_code);
        $this->assertSame('asha@okaxis', $user->google_pay);
        $this->assertSame('9876543210', $user->phonepe);
    }

    public function test_a_member_sees_only_their_own_order_history(): void
    {
        $member = User::factory()->create(['name' => 'Asha Rao']);
        $other = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Personal care']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Neem Soap',
            'description' => 'A gentle neem bar.',
        ]);

        $order = Order::factory()->for($member)->create([
            'number' => 'NB-100001',
            'name' => 'Asha Rao',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'address' => '12 Market Road',
            'city' => 'Pune',
            'region' => 'Maharashtra',
            'postal' => '411001',
            'country' => 'India',
            'pay_method' => 'cash',
            'promo' => 'SAVE10',
            'subtotal' => 200,
            'discount' => 20,
            'shipping' => 40,
            'total' => 220,
            'total_bv' => 160,
        ]);

        OrderItem::factory()->for($order)->for($product)->create([
            'sku' => 'neem-soap',
            'name' => 'Neem Soap',
            'qty' => 2,
            'price' => 100,
            'bv' => 80,
        ]);

        $hidden = Order::factory()->for($other)->create([
            'number' => 'NB-200002',
            'name' => 'Other Buyer',
        ]);

        OrderItem::factory()->for($hidden)->create([
            'sku' => 'hidden-oil',
            'name' => 'Hidden Oil',
            'qty' => 1,
            'price' => 50,
        ]);

        $this->actingAs($member)
            ->get('/dashboard/order-history')
            ->assertOk()
            ->assertSee('Order History')
            ->assertSee('NB-100001')
            ->assertSee('Neem Soap')
            ->assertSee('neem-soap')
            ->assertSee('Qty 2')
            ->assertSee('₹100.00')
            ->assertSee('₹200.00')
            ->assertSee('₹220.00')
            ->assertSee('BV 80.00')
            ->assertSee('Line BV 160.00')
            ->assertSee('Total BV')
            ->assertSee('Pending')
            ->assertDontSee('NB-200002')
            ->assertDontSee('Hidden Oil');

        $this->actingAs($member)
            ->get('/dashboard/order-history?q=missing')
            ->assertOk()
            ->assertSee('No matches')
            ->assertDontSee('Neem Soap');

        $this->actingAs($member)
            ->get('/dashboard/orders/'.$order->id)
            ->assertOk()
            ->assertSee('NB-100001')
            ->assertSee('12 Market Road')
            ->assertSee('Pune')
            ->assertSee('Cash')
            ->assertSee('SAVE10')
            ->assertSee('Personal care')
            ->assertSee('A gentle neem bar.')
            ->assertSee('₹20.00')
            ->assertSee('₹40.00')
            ->assertSee('Unit BV')
            ->assertSee('80.00')
            ->assertSee('Line BV')
            ->assertSee('160.00')
            ->assertSee('Total BV')
            ->assertDontSee('Hidden Oil');

        $this->actingAs($member)
            ->get('/dashboard/orders/'.$hidden->id)
            ->assertNotFound();
    }

    public function test_order_history_is_empty_until_the_member_checks_out(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/dashboard/order-history')
            ->assertOk()
            ->assertSee('No orders yet')
            ->assertSee('Orders appear here after you check out.');
    }

    public function test_the_super_admin_is_sent_away_from_order_history(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/dashboard/order-history')
            ->assertRedirect('/admin');
    }

    public function test_matrix_level_income_shows_the_two_level_layout(): void
    {
        $user = User::factory()->create([
            'name' => 'Avery Chen',
            'user_name' => 'GSI2000002',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/matrix-level-income')
            ->assertOk()
            ->assertSee('3x Matrix Level Income')
            ->assertSee('This income has two levels')
            ->assertSee('Level 1')
            ->assertSee('Level 2')
            ->assertSee('No level income yet')
            ->assertSee('₹0.00')
            ->assertDontSee('Seat 1')
            ->assertDontSee('3 seats')
            ->assertDontSee('9 seats');
    }
}
