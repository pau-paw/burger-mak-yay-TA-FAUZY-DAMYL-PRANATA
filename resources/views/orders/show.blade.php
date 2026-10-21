@extends('layouts.app')
@section('title', 'Detail pesanan')
@section('content')
<a class="back" href="{{ route('orders.index') }}">← Semua pesananku</a><div class="page-heading"><span class="eyebrow">TERIMA KASIH SUDAH MEMESAN</span><h1>{{ $order->status === 'ready' ? 'Pesanan siap diambil!' : 'Pesananmu di sini.' }}</h1><p class="muted">Status diperbarui admin. Muat ulang halaman untuk informasi terbaru.</p></div>
@include('partials.order-detail')
<div class="info-box"><strong>Alur pesanan</strong><p>Menunggu → Disiapkan → Siap diambil → Selesai. Pembayaran dikonfirmasi di kedai. Jika perlu membatalkan, hubungi kedai agar admin dapat memprosesnya sebelum pesanan selesai.</p></div>
@endsection
