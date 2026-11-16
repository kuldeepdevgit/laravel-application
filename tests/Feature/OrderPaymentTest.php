<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_history_opens_an_upload_payment_popup(): void
    {
        $member = User::factory()->create();
        $order = Order::factory()->for($member)->create([
            'number' => 'NB-300003',
            'total' => 220,
        ]);

        $this->actingAs($member)
            ->get('/dashboard/order-history')
            ->assertOk()
            ->assertSee('Upload payment')
            ->assertSee('Franchise Sponsor ID')
            ->assertSee('Screenshot')
            ->assertSee('Pay mode')
            ->assertSee('Cash')
            ->assertSee('Google Pay')
            ->assertSee('PhonePe')
            ->assertSee('Bank')
            ->assertSee('Remark')
            ->assertSee('id="upload-payment"', false)
            ->assertSee('data-amount="220.00"', false)
            ->assertSee(route('dashboard.orders.payment', $order), false);
    }

    public function test_a_member_can_upload_payment_for_their_order(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create();
        $order = Order::factory()->for($member)->create([
            'number' => 'NB-300004',
            'total' => 500,
        ]);

        $this->actingAs($member)
            ->post('/dashboard/orders/'.$order->id.'/payment', [
                'franchise_sponsor_id' => ' GSI6185588 ',
                'amount' => '250.50',
                'screenshot' => UploadedFile::fake()->image('proof.png', 400, 300),
                'pay_mode' => 'google_pay',
                'remark' => ' Paid at the counter ',
            ])
            ->assertRedirect('/dashboard/order-history')
            ->assertSessionHas('status', 'Payment uploaded for NB-300004.');

        $payment = $order->refresh()->payment;
        $this->assertInstanceOf(OrderPayment::class, $payment);
        $this->assertSame($member->id, $payment->user_id);
        $this->assertSame('GSI6185588', $payment->franchise_sponsor_id);
        $this->assertSame('250.50', $payment->amount);
        $this->assertSame('google_pay', $payment->pay_mode);
        $this->assertSame('Paid at the counter', $payment->remark);
        $this->assertStringStartsWith('uploads/'.now()->format('Y/m').'/payment/payment-img/', $payment->screenshot);
        $this->assertFileExists(public_path($payment->screenshot));

        $this->actingAs($member)
            ->get('/dashboard/order-history')
            ->assertSee('Payment uploaded')
            ->assertSee('data-sponsor="GSI6185588"', false)
            ->assertSee('data-pay-mode="google_pay"', false);

        $this->actingAs($member)
            ->get('/dashboard/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Uploaded payment')
            ->assertSee('GSI6185588')
            ->assertSee('₹250.50')
            ->assertSee('Google Pay')
            ->assertSee('Paid at the counter');

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Uploaded payment')
            ->assertSee('Franchise Sponsor ID')
            ->assertSee('GSI6185588')
            ->assertSee('₹250.50')
            ->assertSee('Google Pay')
            ->assertSee('Paid at the counter');

        $image = $payment->screenshot;
        $payment->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_a_replacement_screenshot_replaces_the_saved_file(): void
    {
        $member = User::factory()->create();
        $order = Order::factory()->for($member)->create();
        $previous = OrderPayment::storeImage(UploadedFile::fake()->image('first.jpg', 200, 200));
        OrderPayment::factory()->for($order)->for($member)->create([
            'screenshot' => $previous,
            'franchise_sponsor_id' => 'GSI100001',
            'amount' => 10,
            'pay_mode' => 'cash',
        ]);

        $this->actingAs($member)
            ->post('/dashboard/orders/'.$order->id.'/payment', [
                'franchise_sponsor_id' => 'GSI100001',
                'amount' => '80',
                'pay_mode' => 'bank',
                'remark' => 'Bank transfer',
            ])
            ->assertRedirect('/dashboard/order-history');

        $this->assertSame($previous, $order->refresh()->payment->screenshot);
        $this->assertFileExists(public_path($previous));

        $this->actingAs($member)
            ->post('/dashboard/orders/'.$order->id.'/payment', [
                'franchise_sponsor_id' => 'GSI100001',
                'amount' => '80.00',
                'screenshot' => UploadedFile::fake()->image('second.webp', 300, 200),
                'pay_mode' => 'phone_pe',
                'remark' => '',
            ])
            ->assertRedirect('/dashboard/order-history');

        $payment = $order->refresh()->payment;
        $this->assertNotSame($previous, $payment->screenshot);
        $this->assertSame('phone_pe', $payment->pay_mode);
        $this->assertNull($payment->remark);
        $this->assertStringStartsWith('uploads/'.now()->format('Y/m').'/payment/payment-img/', $payment->screenshot);
        $this->assertFileDoesNotExist(public_path($previous));
        $this->assertFileExists(public_path($payment->screenshot));

        $image = $payment->screenshot;
        $payment->delete();
        $this->assertFileDoesNotExist(public_path($image));
    }

    public function test_payment_upload_rejects_a_missing_screenshot_and_another_members_order(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->for($member)->create(['number' => 'NB-300005']);
        $hidden = Order::factory()->for($other)->create();

        $this->post('/dashboard/orders/'.$order->id.'/payment')
            ->assertRedirect('/login');

        $this->actingAs($member)
            ->from('/dashboard/order-history')
            ->post('/dashboard/orders/'.$order->id.'/payment', [
                'franchise_sponsor_id' => 'GSI100009',
                'amount' => '15',
                'pay_mode' => 'wallet',
            ])
            ->assertRedirect('/dashboard/order-history')
            ->assertSessionHasErrors(['screenshot', 'pay_mode'])
            ->assertSessionHas('payment_order', $order->id);

        $this->assertNull($order->refresh()->payment);

        $this->actingAs($member)
            ->post('/dashboard/orders/'.$hidden->id.'/payment', [
                'franchise_sponsor_id' => 'GSI100009',
                'amount' => '15',
                'screenshot' => UploadedFile::fake()->image('proof.jpg'),
                'pay_mode' => 'cash',
            ])
            ->assertNotFound();

        $this->assertSame(0, OrderPayment::query()->count());
    }
}
