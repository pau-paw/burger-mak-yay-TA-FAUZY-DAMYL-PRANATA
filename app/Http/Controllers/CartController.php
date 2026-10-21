<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Cart $cart)
    {
        $items = $cart->items();
        $raw = $cart->raw();
        $foundProductIds = $items->pluck('product.id')->all();
        $missingKeys = [];

        foreach ($raw as $key => $rawItem) {
            if (! in_array($rawItem['product_id'], $foundProductIds, true)) {
                $missingKeys[] = $key;
            }
        }

        return view('cart.index', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'missingKeys' => $missingKeys,
        ]);
    }

    public function store(Request $request, Product $product, Cart $cart)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', 'exists:addons,id'],
        ]);

        $addonIds = $data['addons'] ?? [];
        $key = Cart::itemKey($product->id, $addonIds);
        $raw = $cart->raw();
        $existingQty = isset($raw[$key]) ? (int) $raw[$key]['quantity'] : 0;
        $newQty = min(99, (int) $data['quantity'] + $existingQty);

        $cart->put($product, $newQty, $addonIds);

        return redirect()->route('cart.index')->with('success', 'Menu dan topping berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $item, Cart $cart)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart->updateQuantity($item, (int) $data['quantity']);

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function destroy(string $item, Cart $cart)
    {
        $cart->remove($item);

        return back()->with('success', 'Menu berhasil dihapus dari keranjang.');
    }
}
