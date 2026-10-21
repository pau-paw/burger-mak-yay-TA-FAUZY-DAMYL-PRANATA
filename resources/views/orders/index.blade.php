@extends('layouts.app')
@section('title', 'Riwayat Pesanan')
@section('content')
<div class="page-heading">
    <span class="eyebrow">PESANAN DAN KENANGAN ENAK</span>
    <h1>Pesananku.</h1>
    <p class="muted">Muat ulang halaman untuk memantau status pesanan dan konfirmasi pembayaran terbaru.</p>
</div>

<div class="stack">
    @forelse($orders as $order)
    <article class="panel order-card">
        <div>
            <div class="row badges-row">
                <span class="badge {{ $order->status }}">{{ $order->status_label }}</span>
                <span class="badge {{ $order->payment_status === 'paid' ? 'completed' : 'pending' }}">
                    {{ $order->payment_status === 'paid' ? '✓ Lunas' : 'Belum Dibayar' }}
                </span>
                <span class="badge outline">
                    {{ $order->fulfillment_type === 'delivery' ? '🛵 Delivery' : '🏪 Pickup' }}
                </span>
            </div>
            <h3>#{{ $order->number }}</h3>
            <p class="muted">{{ $order->created_at->format('d M Y, H:i') }} WIB · Metode: {{ $order->payment_method_label }}</p>
        </div>
        <div>
            <strong class="price">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
            <a class="button small outline" href="{{ route('orders.show', $order) }}">Detail pesanan →</a>
        </div>
    </article>
    @empty
    <div class="empty panel">
        <h2>Belum ada pesanan.</h2>
        <p>Menu burger lezat Mak Yay menunggumu.</p>
        <a class="button" href="{{ route('home') }}#menu">Pesan sekarang</a>
    </div>
    @endforelse
</div>

@include('partials.pagination', ['paginator' => $orders])
@endsection
