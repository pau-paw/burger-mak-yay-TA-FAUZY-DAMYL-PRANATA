<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show(Request $request, Cart $cart)
    {
        $items = $cart->items();
        if ($items->isEmpty() || $items->count() !== count($cart->raw())) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Periksa keranjang: kosong atau ada menu yang telah dihapus.']);
        }

        // Group total quantities per product
        $quantitiesPerProduct = [];
        foreach ($items as $item) {
            $pid = $item['product']->id;
            $quantitiesPerProduct[$pid] = ($quantitiesPerProduct[$pid] ?? 0) + $item['quantity'];
        }

        foreach ($items as $item) {
            $product = $item['product'];
            $totalReq = $quantitiesPerProduct[$product->id] ?? 0;
            if (! $product->is_active || $product->stock < $totalReq) {
                return redirect()->route('cart.index')->withErrors(['cart' => 'Menu ' . $product->name . ' tidak tersedia atau jumlah pesanan melebihi stok yang tersedia.']);
            }
        }

        $token = (string) Str::uuid();
        $request->session()->put('checkout_token', $token);
        $request->session()->put('checkout_prices', $items->mapWithKeys(fn ($item) => [$item['key'] => $item['unit_price']])->all());

        $deliveryFee = (int) config('shop.delivery_fee', 10000);
        $subtotal = $items->sum('subtotal');

        return view('checkout.show', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'token' => $token,
        ]);
    }

    public function store(Request $request, Cart $cart, OrderService $service)
    {
        if (is_string($request->input('phone'))) {
            $request->merge(['phone' => preg_replace('/[\s().-]+/', '', $request->input('phone'))]);
        }

        $request->mergeIfMissing([
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
        ]);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'checkout_token' => ['required', 'uuid'],
            'fulfillment_type' => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:fulfillment_type,delivery', 'nullable', 'string', 'min:5', 'max:500'],
            'payment_method' => ['required', 'in:qris,cash_on_pickup'],
        ], [
            'delivery_address.required_if' => 'Alamat pengiriman wajib diisi jika memilih layanan Dikirim ke Alamat.',
            'delivery_address.min' => 'Alamat pengiriman minimal 5 karakter.',
        ]);

        if ($data['fulfillment_type'] === 'delivery' && $data['payment_method'] === 'cash_on_pickup') {
            throw ValidationException::withMessages([
                'payment_method' => 'Untuk pesanan pengantaran ke alamat (Delivery), pembayaran wajib menggunakan QRIS.',
            ]);
        }

        $existing = $request->user()->orders()->where('checkout_token', $data['checkout_token'])->first();
        if ($existing) {
            return redirect()->route('orders.show', $existing);
        }

        if (! hash_equals((string) $request->session()->get('checkout_token'), $data['checkout_token'])) {
            throw ValidationException::withMessages(['cart' => 'Sesi checkout berubah. Buka kembali halaman checkout.']);
        }

        $data['delivery_fee'] = $data['fulfillment_type'] === 'delivery' ? (int) config('shop.delivery_fee', 10000) : 0;

        $order = $service->checkout(
            $request->user(),
            $cart->raw(),
            $data,
            $request->session()->get('checkout_prices', [])
        );

        $cart->clear();

        $successMsg = $order->payment_method === 'qris'
            ? 'Pesanan berhasil dibuat! Silakan scan QRIS dan konfirmasi via WhatsApp.'
            : 'Pesanan diterima! Bayar tunai saat pengambilan di kedai.';

        return redirect()->route('orders.show', $order)->with('success', $successMsg);
    }
}
