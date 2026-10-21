<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminManagementTest extends ShopTestCase
{
    private function productData(Category $category): array
    {
        return ['category_id' => $category->id, 'name' => 'New Burger', 'description' => 'Burger baru', 'price' => 30000, 'stock' => 20, 'is_active' => 1, 'revision' => 0];
    }

    public function test_admin_pages_render_including_order_and_forms(): void
    {
        $product = $this->product();
        $order = $this->placeOrder(product: $product);
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/categories', '/admin/categories/create', '/admin/categories/'.$product->category_id.'/edit', '/admin/products', '/admin/products/create', '/admin/products/'.$product->id.'/edit', '/admin/orders', '/admin/orders/'.$order->id] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_category_create_update_delete_and_in_use_protection(): void
    {
        $this->actingAs($this->admin())->post('/admin/categories', ['name' => 'Sides'])->assertRedirect('/admin/categories');
        $category = Category::where('name', 'Sides')->firstOrFail();
        $this->put('/admin/categories/'.$category->id, ['name' => 'Side Dishes'])->assertSessionHasNoErrors();
        $this->delete('/admin/categories/'.$category->id)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $product = $this->product();
        $this->delete('/admin/categories/'.$product->category_id)->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    }

    public function test_menu_upload_update_archive_and_restore(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Burger']);
        $this->actingAs($this->admin())->post('/admin/products', [...$this->productData($category), 'image' => UploadedFile::fake()->image('burger.jpg')])->assertSessionHasNoErrors()->assertRedirect('/admin/products');
        $product = Product::firstOrFail();
        $oldPath = $product->image_path;
        Storage::disk('public')->assertExists($oldPath);
        $this->put('/admin/products/'.$product->id, [...$this->productData($category), 'price' => 35000, 'image' => UploadedFile::fake()->image('new.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->fresh()->image_path);
        $this->assertSame(35000, $product->fresh()->price);
        $this->delete('/admin/products/'.$product->id)->assertSessionHasNoErrors();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->get('/admin/products?archived=1')->assertOk()->assertSee('New Burger');
        $this->post('/admin/products/'.$product->id.'/restore')->assertRedirect('/admin/products/'.$product->id.'/edit');
        $this->assertFalse($product->fresh()->is_active);
        $this->assertNull($product->fresh()->deleted_at);
    }

    public function test_invalid_price_category_and_file_upload_are_rejected(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Burger']);
        $this->actingAs($this->admin())->post('/admin/products', [...$this->productData($category), 'category_id' => 99999, 'price' => -1, 'image' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])->assertSessionHasErrors(['category_id', 'price', 'image']);
        $this->post('/admin/products', [...$this->productData($category), 'image' => UploadedFile::fake()->create('active.svg', 1, 'image/svg+xml')])->assertSessionHasErrors('image');
        $this->post('/admin/products', [...$this->productData($category), 'image' => UploadedFile::fake()->image('big.jpg')->size(3000)])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_customer_cannot_update_menu_or_order(): void
    {
        $product = $this->product();
        $order = $this->placeOrder(product: $product);
        $this->put('/admin/products/'.$product->id, ['price' => 1])->assertForbidden();
        $this->put('/admin/orders/'.$order->id, ['status' => 'completed', 'payment_received' => 1])->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancellation_restores_stock_once_and_preserves_order(): void
    {
        $product = $this->product();
        $order = $this->placeOrder(product: $product);
        $this->actingAs($this->admin())->put('/admin/orders/'.$order->id, ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->assertSame(10, $product->fresh()->stock);
        $this->put('/admin/orders/'.$order->id, ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancel_restores_stock_even_for_archived_product(): void
    {
        $product = $this->product();
        $order = $this->placeOrder(product: $product);
        $product->delete();
        $this->actingAs($this->admin())->put('/admin/orders/'.$order->id, ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->assertSame(10, Product::withTrashed()->findOrFail($product->id)->stock);
    }

    public function test_status_must_follow_lifecycle_and_payment_requires_confirmation(): void
    {
        $order = $this->placeOrder();
        $this->actingAs($this->admin())->put('/admin/orders/'.$order->id, ['status' => 'completed', 'payment_received' => 1])->assertSessionHasErrors('status');
        $this->put('/admin/orders/'.$order->id, ['status' => 'processing'])->assertSessionHasNoErrors();
        $this->put('/admin/orders/'.$order->id, ['status' => 'ready'])->assertSessionHasNoErrors();
        $this->put('/admin/orders/'.$order->id, ['status' => 'completed'])->assertSessionHasErrors('payment_received');
        $this->put('/admin/orders/'.$order->id, ['status' => 'completed', 'payment_received' => 1])->assertSessionHasNoErrors();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->put('/admin/orders/'.$order->id, ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_dashboard_revenue_counts_only_completed_paid_orders(): void
    {
        $order = $this->placeOrder();
        $this->actingAs($this->admin())->get('/admin')->assertViewHas('revenue', fn ($total) => (int) $total === 0);
        $order->update(['status' => 'completed', 'payment_status' => 'paid']);
        $this->get('/admin')->assertViewHas('revenue', fn ($total) => (int) $total === 50000);
    }

    public function test_stale_admin_form_cannot_overwrite_stock_after_checkout(): void
    {
        $product = $this->product();
        $this->placeOrder(product: $product);
        $this->actingAs($this->admin())->put('/admin/products/'.$product->id, [...$this->productData($product->category), 'stock' => 10, 'revision' => 0])->assertSessionHasErrors('stock');
        $this->assertSame(8, $product->fresh()->stock);
    }
}
