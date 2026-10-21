<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;

class CheckoutTest extends ShopTestCase
{
    public function test_checkout_renders_server_price_and_issues_token(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$product->id => 2]])->get('/checkout')->assertOk()->assertSee('50.000')->assertSessionHas('checkout_token')->assertSessionHas('checkout_prices.'.$product->id, 25000);
    }

    public function test_checkout_is_atomic_and_ignores_client_total_and_payment_status(): void
    {
        $product = $this->product();
        $session = $this->checkoutSession($product);
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', [...$this->checkoutData($session['checkout_token']), 'total' => 1, 'payment_status' => 'paid', 'status' => 'completed'])->assertSessionHasNoErrors()->assertSessionMissing('cart');
        $this->assertDatabaseHas('orders', ['total' => 50000, 'status' => 'pending', 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('order_items', ['unit_price' => 25000, 'quantity' => 2, 'subtotal' => 50000]);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_retry_of_same_checkout_does_not_duplicate_order_or_stock_deduction(): void
    {
        $product = $this->product();
        $session = $this->checkoutSession($product);
        $data = $this->checkoutData($session['checkout_token']);
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', $data)->assertRedirect();
        $this->post('/checkout', $data)->assertRedirect('/orders/'.Order::firstOrFail()->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_invalid_checkout_token_and_empty_cart_cannot_create_order(): void
    {
        $this->actingAs(User::factory()->create())->post('/checkout', $this->checkoutData((string) \Illuminate\Support\Str::uuid()))->assertSessionHasErrors('cart');
        $this->get('/checkout')->assertRedirect('/cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_price_change_requires_review(): void
    {
        $product = $this->product();
        $session = $this->checkoutSession($product);
        $product->update(['price' => 30000]);
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', $this->checkoutData($session['checkout_token']))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_insufficient_stock_rolls_back_whole_multi_item_order(): void
    {
        $first = $this->product();
        $second = $this->product(['stock' => 0]);
        $session = $this->checkoutSession($first);
        $session['cart'][$second->id] = 1;
        $session['checkout_prices'][$second->id] = $second->price;
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', $this->checkoutData($session['checkout_token']))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $first->fresh()->stock);
    }

    public function test_inactive_or_deleted_product_cannot_be_checked_out(): void
    {
        $product = $this->product();
        $session = $this->checkoutSession($product);
        $product->update(['is_active' => false]);
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', $this->checkoutData($session['checkout_token']))->assertSessionHasErrors('cart');
        $product->delete();
        $this->post('/checkout', $this->checkoutData($session['checkout_token']))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_validates_customer_and_phone(): void
    {
        $product = $this->product();
        $session = $this->checkoutSession($product);
        $this->actingAs(User::factory()->create())->withSession($session)->post('/checkout', ['checkout_token' => $session['checkout_token'], 'phone' => 'invalid'])->assertSessionHasErrors(['customer_name', 'phone']);
    }

    public function test_customer_can_only_see_own_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->placeOrder($owner);
        $this->get('/orders/'.$order->id)->assertOk()->assertSee($order->number);
        $this->actingAs(User::factory()->create())->get('/orders/'.$order->id)->assertNotFound();
        $this->get('/orders')->assertOk()->assertDontSee($order->number);
    }

    public function test_order_items_keep_original_price_and_name_after_menu_changes(): void
    {
        $product = $this->product(['name' => 'Original Burger']);
        $order = $this->placeOrder(product: $product);
        $product->update(['price' => 100000, 'name' => 'New Burger']);
        $product->delete();
        $this->get('/orders/'.$order->id)->assertOk()->assertSee('Original Burger')->assertSee('25.000')->assertDontSee('New Burger');
    }
}
