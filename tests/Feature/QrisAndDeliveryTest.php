<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrisAndDeliveryTest extends ShopTestCase
{
    public function test_catalog_shows_addons_and_cart_calculates_with_addons(): void
    {
        $product = $this->product(['price' => 25000]);
        $cheese = Addon::create(['name' => 'Extra Cheese', 'price' => 5000, 'is_active' => true]);
        $patty = Addon::create(['name' => 'Extra Patty', 'price' => 12000, 'is_active' => true]);

        // Catalog show displays addons
        $this->get('/menu/' . $product->id)
            ->assertOk()
            ->assertSee('Extra Cheese')
            ->assertSee('+Rp 5.000')
            ->assertSee('Extra Patty')
            ->assertSee('+Rp 12.000');

        // Add to cart with 2 addons: 25.000 + 5.000 + 12.000 = 42.000
        $this->post('/cart/' . $product->id, [
            'quantity' => 2,
            'addons' => [$cheese->id, $patty->id],
        ])->assertRedirect('/cart');

        // Cart page displays product with addons and subtotal = 42.000 * 2 = 84.000
        $this->get('/cart')
            ->assertOk()
            ->assertSee('Extra Cheese')
            ->assertSee('Extra Patty')
            ->assertSee('84.000');
    }

    public function test_checkout_delivery_requires_address_and_adds_delivery_fee(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 30000]);

        // Delivery without address should fail validation
        $token = (string) Str::uuid();
        $this->actingAs($user)
            ->withSession([
                'cart' => [$product->id => 1],
                'checkout_token' => $token,
                'checkout_prices' => [$product->id => 30000],
            ])
            ->post('/checkout', [
                'customer_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'checkout_token' => $token,
                'fulfillment_type' => 'delivery',
                'payment_method' => 'qris',
                'delivery_address' => '', // empty
            ])
            ->assertSessionHasErrors('delivery_address');

        // Delivery with address and QRIS payment should succeed
        $token2 = (string) Str::uuid();
        $this->actingAs($user)
            ->withSession([
                'cart' => [$product->id => 1],
                'checkout_token' => $token2,
                'checkout_prices' => [$product->id => 30000],
            ])
            ->post('/checkout', [
                'customer_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'checkout_token' => $token2,
                'fulfillment_type' => 'delivery',
                'delivery_address' => 'Jl. Kenanga No. 45, RT 02/RW 03',
                'payment_method' => 'qris',
            ])
            ->assertRedirect();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertSame('delivery', $order->fulfillment_type);
        $this->assertSame('Jl. Kenanga No. 45, RT 02/RW 03', $order->delivery_address);
        $this->assertSame(10000, $order->delivery_fee);
        $this->assertSame(30000, $order->total);
        $this->assertSame(40000, $order->grand_total);
        $this->assertSame('qris', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);

        // Order detail page should show QRIS card and WhatsApp link
        $this->get('/orders/' . $order->id)
            ->assertOk()
            ->assertSee('PEMBAYARAN QRIS')
            ->assertSee('40.000')
            ->assertSee('Konfirmasi ke WhatsApp')
            ->assertSee('wa.me');
    }

    public function test_customer_can_upload_payment_proof(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $order = Order::create([
            'number' => (string) Str::uuid(),
            'user_id' => $user->id,
            'checkout_token' => (string) Str::uuid(),
            'customer_name' => 'Ahmad',
            'phone' => '081987654321',
            'total' => 25000,
            'delivery_fee' => 0,
            'fulfillment_type' => 'pickup',
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800);

        $this->actingAs($user)
            ->post('/orders/' . $order->id . '/payment-proof', [
                'payment_proof' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    public function test_admin_can_manage_addons_and_confirm_order_payment(): void
    {
        $admin = $this->admin();

        // 1. Admin Addon CRUD
        $this->actingAs($admin)
            ->post('/admin/addons', [
                'name' => 'Keju Mozzarella',
                'price' => 7000,
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/addons')
            ->assertSessionHas('success');

        $addon = Addon::where('name', 'Keju Mozzarella')->firstOrFail();
        $this->assertSame(7000, $addon->price);

        // 2. Admin verifies payment status
        $order = Order::create([
            'number' => (string) Str::uuid(),
            'user_id' => $admin->id,
            'checkout_token' => (string) Str::uuid(),
            'customer_name' => 'Siti',
            'phone' => '081122334455',
            'total' => 50000,
            'delivery_fee' => 10000,
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'Jl. Mawar No. 10',
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post('/admin/orders/' . $order->id . '/payment-status', [
                'payment_status' => 'paid',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }
}
