<?php

namespace Tests\Feature;

class CatalogAndCartTest extends ShopTestCase
{
    public function test_catalog_only_lists_active_products_and_search_works(): void
    {
        $visible = $this->product(['name' => 'Visible Burger']);
        $hidden = $this->product(['name' => 'Hidden Burger', 'is_active' => false]);
        $this->get('/')->assertOk()->assertSee('Visible Burger')->assertDontSee('Hidden Burger');
        $this->get('/?q=not-found')->assertOk()->assertDontSee('Visible Burger');
        $this->get('/menu/'.$hidden->id)->assertNotFound();
        $this->get('/menu/'.$visible->id)->assertOk()->assertSee('Visible Burger');
    }

    public function test_category_filter_excludes_other_categories(): void
    {
        $burger = $this->product(['name' => 'Burger Filter']);
        $drinks = \App\Models\Category::create(['name' => 'Drinks']);
        $this->product(['name' => 'Tea Filter', 'category_id' => $drinks->id]);
        $this->get('/?category='.$burger->category_id)->assertSee('Burger Filter')->assertDontSee('Tea Filter');
    }

    public function test_cart_add_update_remove_and_total_come_from_database(): void
    {
        $product = $this->product();
        $this->post('/cart/'.$product->id, ['quantity' => 2, 'price' => 1])->assertRedirect('/cart')->assertSessionHas('cart.'.$product->id, 2);
        $this->get('/cart')->assertOk()->assertSee('50.000');
        $this->patch('/cart/'.$product->id, ['quantity' => 3])->assertSessionHas('cart.'.$product->id, 3);
        $this->delete('/cart/'.$product->id)->assertSessionMissing('cart.'.$product->id);
    }

    public function test_invalid_quantities_and_unavailable_menu_are_rejected(): void
    {
        $product = $this->product(['stock' => 2]);
        foreach ([0, -1, 3, 100, 'bad'] as $quantity) {
            $this->post('/cart/'.$product->id, ['quantity' => $quantity])->assertSessionHasErrors('quantity');
        }
        $product->update(['is_active' => false]);
        $this->post('/cart/'.$product->id, ['quantity' => 1])->assertSessionHasErrors('quantity');
    }

    public function test_deleted_menu_remains_removable_from_cart(): void
    {
        $product = $this->product();
        $product->delete();
        $this->withSession(['cart' => [$product->id => 1]])->get('/cart')->assertOk()->assertSee('sudah tidak tersedia');
        $this->delete('/cart/'.$product->id)->assertSessionMissing('cart.'.$product->id);
    }

    public function test_cart_mutation_invalidates_old_checkout_quote(): void
    {
        $product = $this->product();
        $this->withSession($this->checkoutSession($product))->patch('/cart/'.$product->id, ['quantity' => 1])->assertSessionMissing('checkout_token')->assertSessionMissing('checkout_prices');
    }
}
