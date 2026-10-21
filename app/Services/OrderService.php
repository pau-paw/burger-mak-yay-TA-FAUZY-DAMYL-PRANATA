<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function checkout(User $user, array $cart, array $data, array $quotedPrices): Order
    {
        return DB::transaction(function () use ($user, $cart, $data, $quotedPrices) {
            // Serialize customer checkout
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = $user->orders()->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return $existing;
            }

            if (count($cart) === 0 || count($cart) > 50) {
                throw ValidationException::withMessages(['cart' => 'Keranjang kosong atau terlalu banyak jenis menu.']);
            }

            $productIds = array_unique(array_column($cart, 'product_id'));
            $products = Product::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw ValidationException::withMessages(['cart' => 'Ada menu yang telah dihapus. Perbarui keranjang.']);
            }

            // Calculate total quantity per product to check against stock
            $productQuantities = [];
            foreach ($cart as $item) {
                $pid = (int) $item['product_id'];
                $productQuantities[$pid] = ($productQuantities[$pid] ?? 0) + (int) $item['quantity'];
            }

            foreach ($productQuantities as $pid => $totalQty) {
                $product = $products->get($pid);
                if (! $product->is_active || $product->stock < $totalQty) {
                    throw ValidationException::withMessages(['cart' => 'Menu ' . $product->name . ' tidak tersedia atau stok tidak cukup.']);
                }
            }

            // Fetch all addons in cart
            $allAddonIds = [];
            foreach ($cart as $item) {
                if (! empty($item['addon_ids'])) {
                    $allAddonIds = array_merge($allAddonIds, $item['addon_ids']);
                }
            }
            $allAddonIds = array_unique($allAddonIds);
            $addons = empty($allAddonIds) ? collect() : Addon::whereIn('id', $allAddonIds)->get()->keyBy('id');

            $orderTotal = 0;
            $orderItemsData = [];

            foreach ($cart as $key => $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ($quantity < 1 || $quantity > 99) {
                    throw ValidationException::withMessages(['cart' => 'Jumlah menu tidak valid.']);
                }

                $addonUnitPrice = 0;
                $itemAddons = [];
                foreach ($item['addon_ids'] as $addonId) {
                    $addon = $addons->get($addonId);
                    if (! $addon || ! $addon->is_active) {
                        throw ValidationException::withMessages(['cart' => 'Topping/Add-on pilihan tidak tersedia lagi. Periksa keranjang Anda.']);
                    }
                    $addonUnitPrice += $addon->price;
                    $itemAddons[] = [
                        'addon_id' => $addon->id,
                        'addon_name' => $addon->name,
                        'addon_price' => $addon->price,
                    ];
                }

                $effectiveUnitPrice = $product->price + $addonUnitPrice;
                $quotedPrice = $quotedPrices[$key] ?? null;

                if ($quotedPrice === null || (int) $quotedPrice !== $effectiveUnitPrice) {
                    throw ValidationException::withMessages(['cart' => 'Harga menu atau topping berubah. Tinjau ulang halaman checkout sebelum memesan.']);
                }

                $subtotal = $effectiveUnitPrice * $quantity;
                $orderTotal += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'addons_total' => $addonUnitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'addons' => $itemAddons,
                ];
            }

            $fulfillmentType = $data['fulfillment_type'] ?? 'pickup';
            $deliveryFee = $fulfillmentType === 'delivery' ? (int) ($data['delivery_fee'] ?? config('shop.delivery_fee', 10000)) : 0;
            $deliveryAddress = $fulfillmentType === 'delivery' ? ($data['delivery_address'] ?? null) : null;
            $paymentMethod = $data['payment_method'] ?? 'qris';

            $order = $user->orders()->create([
                'number' => (string) Str::uuid(),
                'checkout_token' => $data['checkout_token'],
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'notes' => $data['notes'] ?? null,
                'fulfillment_type' => $fulfillmentType,
                'delivery_address' => $deliveryAddress,
                'delivery_fee' => $deliveryFee,
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'total' => $orderTotal,
            ]);

            foreach ($orderItemsData as $itemData) {
                $addonsData = $itemData['addons'];
                unset($itemData['addons']);

                $orderItem = $order->items()->create($itemData);
                if (! empty($addonsData)) {
                    $orderItem->addons()->createMany($addonsData);
                }
            }

            // Decrement product stock
            foreach ($productQuantities as $pid => $qty) {
                $product = $products->get($pid);
                $product->forceFill([
                    'stock' => $product->stock - $qty,
                    'revision' => $product->revision + 1,
                ])->save();
            }

            return $order;
        }, 3);
    }

    public function transition(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($status, $locked->nextStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak diizinkan. Muat ulang halaman.']);
            }

            if ($status === 'cancelled') {
                $items = $locked->items()->orderBy('product_id')->get();
                $products = Product::withTrashed()->whereIn('id', $items->pluck('product_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($items as $item) {
                    if ($product = $products->get($item->product_id)) {
                        $product->forceFill(['stock' => $product->stock + $item->quantity, 'revision' => $product->revision + 1])->save();
                    }
                }
            }

            $locked->update([
                'status' => $status,
                'payment_status' => $status === 'completed' ? 'paid' : $locked->payment_status,
                'paid_at' => $status === 'completed' && ! $locked->paid_at ? now() : $locked->paid_at,
            ]);

            return $locked;
        }, 3);
    }
}
