<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(array_keys(Order::STATUSES))]]);

        return view('admin.orders.index', [
            'orders' => Order::when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load(['items.addons', 'items.product', 'user']),
        ]);
    }

    public function update(Request $request, Order $order, OrderService $service)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'payment_received' => [
                Rule::excludeIf($order->payment_status === 'paid'),
                'exclude_unless:status,completed',
                'accepted',
            ],
        ]);

        $service->transition($order, $data['status']);

        return back()->with('success', 'Status pesanan diperbarui.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => ['required', Rule::in(['paid', 'unpaid'])],
        ]);

        $order->update([
            'payment_status' => $data['payment_status'],
            'paid_at' => $data['payment_status'] === 'paid' ? ($order->paid_at ?? now()) : null,
        ]);

        return back()->with('success', 'Status pembayaran berhasil diperbarui menjadi: ' . ($data['payment_status'] === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR') . '.');
    }
}
