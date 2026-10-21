<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Pesanan</th>
                <th>Pemesan</th>
                <th>Pengantaran</th>
                <th>Pembayaran</th>
                <th>Status Pesanan</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>
                    <span class="short-id">#{{ substr($order->number, 0, 8) }}</span>
                    <small class="block muted">{{ $order->created_at->format('d M Y H:i') }}</small>
                </td>
                <td>
                    <strong>{{ $order->customer_name }}</strong>
                    <small class="block muted">{{ $order->phone }}</small>
                </td>
                <td>
                    <span class="badge outline">
                        {{ $order->fulfillment_type === 'delivery' ? '🛵 Delivery' : '🏪 Pickup' }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'completed' : 'pending' }}">
                        {{ $order->payment_status === 'paid' ? '✓ Lunas' : 'Belum Bayar' }}
                    </span>
                    <small class="block muted">{{ $order->payment_method_label }}</small>
                </td>
                <td>
                    <span class="badge {{ $order->status }}">{{ $order->status_label }}</span>
                </td>
                <td>
                    <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                </td>
                <td>
                    <a class="text-link" href="{{ route('admin.orders.show', $order) }}">Detail & Proses →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Belum ada pesanan di daftar ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
