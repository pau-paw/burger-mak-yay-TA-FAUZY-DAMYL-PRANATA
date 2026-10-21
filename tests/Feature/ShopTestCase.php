<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class ShopTestCase extends TestCase
{
    use RefreshDatabase;

    protected function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Burger']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Burger Uji', 'description' => 'Menu untuk pengujian.',
            'price' => 25000, 'stock' => 10, 'is_active' => true,
        ], $attributes));
    }

    protected function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    protected function checkoutData(string $token): array
    {
        return ['customer_name' => 'Pelanggan Uji', 'phone' => '081234567890', 'notes' => 'Tanpa saus', 'checkout_token' => $token];
    }

    protected function checkoutSession(Product $product, int $quantity = 2, ?string $token = null): array
    {
        return ['cart' => [$product->id => $quantity], 'checkout_token' => $token ?? (string) Str::uuid(), 'checkout_prices' => [$product->id => $product->price]];
    }

    protected function placeOrder(?User $user = null, ?Product $product = null): Order
    {
        $user ??= User::factory()->create();
        $product ??= $this->product();
        $session = $this->checkoutSession($product);
        $this->actingAs($user)->withSession($session)->post(route('checkout.store'), $this->checkoutData($session['checkout_token']))->assertSessionHasNoErrors()->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }
}
