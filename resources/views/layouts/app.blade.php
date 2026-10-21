<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pilih menu Burger Mak Yay, pesan online, bayar via QRIS atau di kedai, ambil langsung atau delivery.">
    <title>@yield('title', 'Menu') · Burger Mak Yay</title>
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="preload" href="{{ asset('fonts/LilitaOne-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    @if(!request()->is('admin*'))
        <link rel="stylesheet" href="{{ asset('css/shop-theme.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="icon" href="{{ asset('images/landing/burger-icon.svg') }}" type="image/svg+xml">
    <script src="{{ asset('js/store.js') }}" defer></script>
    <script src="{{ asset('js/animations.js') }}" defer></script>
</head>
<body @class(['storefront' => !request()->is('admin*')])>
<a class="skip" href="#content">Lewati ke konten</a>
@if(!request()->is('admin*'))
@include('partials.shop-header')
@else
<div class="admin-topbar">
    <span>OPERASIONAL &amp; KELOLA KEDAI · BURGER MAK YAY ADMIN</span>
</div>
<header class="admin-site-header">
    <div class="admin-header-shell">
        <a class="brand-pill" href="{{ route('home') }}" aria-label="Burger Mak Yay, beranda">
            <img src="{{ asset('images/landing/burger-icon.svg') }}" width="22" height="22" alt="">
            <span>Mak Yay</span>
            <span class="admin-chip">ADMIN</span>
        </a>

        <nav class="admin-pills-nav" aria-label="Navigasi admin">
            <a href="{{ route('admin.dashboard') }}" @class(['pill', 'active' => request()->routeIs('admin.dashboard')])>Ringkasan</a>
            <a href="{{ route('admin.products.index') }}" @class(['pill', 'active' => request()->routeIs('admin.products.*')])>Menu</a>
            <a href="{{ route('admin.categories.index') }}" @class(['pill', 'active' => request()->routeIs('admin.categories.*')])>Kategori</a>
            <a href="{{ route('admin.addons.index') }}" @class(['pill', 'active' => request()->routeIs('admin.addons.*')])>Topping / Add-on</a>
            <a href="{{ route('admin.orders.index') }}" @class(['pill', 'active' => request()->routeIs('admin.orders.*')])>Pesanan</a>
        </nav>

        <div class="admin-header-actions">
            <a class="pill store-pill" href="{{ route('home') }}" target="_blank">Lihat Toko <span aria-hidden="true">↗</span></a>
            <form action="{{ route('logout') }}" method="POST" class="admin-logout-form">
                @csrf
                <button type="submit" class="pill logout-pill">Keluar</button>
            </form>
        </div>
    </div>
</header>
@endif
<main class="container" id="content">
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert error" role="alert"><strong>Ada yang perlu diperiksa:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
@if(!request()->is('admin*'))
<footer class="footer"><strong>BURGER MAK YAY.</strong><span>Pesan online · Pembayaran QRIS & Tunai · Ambil di Kedai & Delivery</span><small>© {{ date('Y') }} Burger Mak Yay</small></footer>
@else
<footer class="admin-footer-panel">
    <div class="admin-footer-shell">
        <div class="admin-footer-brand">
            <a class="brand-pill" href="{{ route('home') }}">
                <img src="{{ asset('images/landing/burger-icon.svg') }}" width="20" height="20" alt="">
                <span>Burger Mak Yay</span>
            </a>
            <p>Ruang Kelola &amp; Operasional Kedai Burger Mak Yay. Smoky, Cheesy, Unforgettable.</p>
        </div>
        <nav class="admin-footer-links" aria-label="Tautan navigasi admin cepat">
            <a class="pill" href="{{ route('admin.dashboard') }}">Ringkasan</a>
            <a class="pill" href="{{ route('admin.products.index') }}">Menu</a>
            <a class="pill" href="{{ route('admin.categories.index') }}">Kategori</a>
            <a class="pill" href="{{ route('admin.addons.index') }}">Topping</a>
            <a class="pill" href="{{ route('admin.orders.index') }}">Pesanan</a>
            <a class="pill" href="{{ route('home') }}">Ke Toko ↗</a>
        </nav>
    </div>
    <div class="admin-footer-bottom">
        <small>© {{ date('Y') }} Burger Mak Yay. Hak cipta dilindungi.</small>
        <small>Dibuat saat dipesan, dinikmati sepenuh hati.</small>
    </div>
</footer>
@endif
</body>
</html>
