<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Cart
{
    public static function itemKey(int $productId, array $addonIds = []): string
    {
        $addonIds = array_values(array_unique(array_map('intval', $addonIds)));
        sort($addonIds);

        return empty($addonIds) ? (string) $productId : $productId . '_' . implode('-', $addonIds);
    }

    public function raw(): array
    {
        $cart = session('cart', []);
        $normalized = [];

        foreach ($cart as $key => $value) {
            if (is_numeric($value)) {
                $productId = (int) $key;
                $normalized[(string) $productId] = [
                    'product_id' => $productId,
                    'quantity' => (int) $value,
                    'addon_ids' => [],
                ];
            } elseif (is_array($value) && isset($value['product_id'], $value['quantity'])) {
                $normalized[(string) $key] = [
                    'product_id' => (int) $value['product_id'],
                    'quantity' => (int) $value['quantity'],
                    'addon_ids' => array_values(array_map('intval', $value['addon_ids'] ?? [])),
                ];
            }
        }

        return $normalized;
    }

    public function count(): int
    {
        $cart = $this->raw();
        $total = 0;
        foreach ($cart as $item) {
            $total += (int) $item['quantity'];
        }

        return $total;
    }

    public function quantities(): array
    {
        return $this->raw();
    }

    public function items(): Collection
    {
        $raw = $this->raw();
        if (empty($raw)) {
            return collect();
        }

        $productIds = array_unique(array_column($raw, 'product_id'));
        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');

        $allAddonIds = [];
        foreach ($raw as $item) {
            if (! empty($item['addon_ids'])) {
                $allAddonIds = array_merge($allAddonIds, $item['addon_ids']);
            }
        }
        $allAddonIds = array_unique($allAddonIds);
        $addons = empty($allAddonIds) ? collect() : Addon::whereIn('id', $allAddonIds)->get()->keyBy('id');

        $items = collect();
        foreach ($raw as $key => $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                continue;
            }

            $itemAddons = collect();
            $addonsUnitPrice = 0;
            foreach ($item['addon_ids'] as $addonId) {
                if ($addon = $addons->get($addonId)) {
                    $itemAddons->push($addon);
                    $addonsUnitPrice += $addon->price;
                }
            }

            $quantity = (int) $item['quantity'];
            $unitPrice = $product->price + $addonsUnitPrice;
            $subtotal = $unitPrice * $quantity;

            $items->push([
                'key' => (string) $key,
                'product' => $product,
                'quantity' => $quantity,
                'addons' => $itemAddons,
                'addons_total' => $addonsUnitPrice,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);
        }

        return $items;
    }

    public function put(Product $product, int $quantity, array $addonIds = []): void
    {
        if (! $product->is_active || $quantity < 1 || $quantity > 99 || $quantity > $product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah tidak valid, menu nonaktif, atau stok tidak cukup.']);
        }

        $cart = session('cart', []);
        $cleanAddons = array_values(array_unique(array_map('intval', $addonIds)));
        sort($cleanAddons);

        $key = self::itemKey($product->id, $cleanAddons);

        if (! isset($cart[$key]) && count($cart) >= 50) {
            throw ValidationException::withMessages(['cart' => 'Maksimal 50 jenis menu per keranjang.']);
        }

        if (empty($cleanAddons)) {
            $cart[$product->id] = $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'addon_ids' => $cleanAddons,
            ];
        }

        session(['cart' => $cart]);
        session()->forget(['checkout_token', 'checkout_prices']);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $cart = session('cart', []);
        if (! isset($cart[$key])) {
            return;
        }

        if ($quantity < 1 || $quantity > 99) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah harus antara 1 sampai 99.']);
        }

        if (is_array($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
        } else {
            $cart[$key] = $quantity;
        }

        session(['cart' => $cart]);
        session()->forget(['checkout_token', 'checkout_prices']);
    }

    public function remove(string $key): void
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);
        session()->forget(['checkout_token', 'checkout_prices']);
    }

    public function clear(): void
    {
        session()->forget(['cart', 'checkout_token', 'checkout_prices']);
    }
}
