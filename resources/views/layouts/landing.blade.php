<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fff1dc">
    <meta name="description" content="Burger Mak Yay. Pilih burger dan topping favoritmu, pesan pickup atau delivery, dan bayar dengan QRIS.">
    <title>Burger Mak Yay</title>
    <link rel="icon" href="{{ asset('images/landing/burger-icon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/LilitaOne-Regular.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <script src="{{ asset('js/landing.js') }}" defer></script>
    <script src="{{ asset('js/store.js') }}" defer></script>
    <script src="{{ asset('js/animations.js') }}" defer></script>
</head>
<body class="landing-page">
<a class="skip-link" href="#main-content">Lewati ke konten</a>
<div class="landing-shell">
    <header class="site-header">
        <a class="brand-pill" href="{{ route('home') }}" aria-label="Burger Mak Yay, beranda">
            <img src="{{ asset('images/landing/burger-icon.svg') }}" width="22" height="22" alt="">
            <span>{{ config('landing.brand') }}</span>
        </a>
        <nav class="header-actions" aria-label="Navigasi utama">
            <details class="nav-menu" data-nav-menu>
                <summary class="pill"><span class="menu-icon" aria-hidden="true">☰</span> Menu</summary>
                <div class="nav-panel">
                    <a href="{{ route('home') }}#menu">Lihat semua menu <span aria-hidden="true">↗</span></a>
                    <a href="{{ route('cart.index') }}">Keranjang <span data-cart-count>{{ app(\App\Services\Cart::class)->count() }}</span></a>
                    @auth
                        <a href="{{ route('orders.index') }}">Riwayat pesanan</a>
                        @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">Dashboard admin</a>@endif
                        <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Keluar</button></form>
                    @else
                        <a href="{{ route('login') }}">Masuk</a>
                        <a href="{{ route('register') }}">Buat akun</a>
                    @endauth
                </div>
            </details>
            <a class="pill order-pill" href="{{ route('home') }}#menu"><span class="bag-icon" aria-hidden="true"></span> Order Now</a>
            @auth
                <a class="pill login-pill" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('orders.index') }}">
                    <span class="user-icon" aria-hidden="true"></span>
                    <span>{{ auth()->user()->isAdmin() ? 'Admin' : 'Akun' }}</span>
                </a>
            @else
                <a class="pill login-pill" href="{{ route('login') }}">
                    <span class="user-icon" aria-hidden="true"></span>
                    <span>Login</span>
                </a>
            @endauth
        </nav>
    </header>
    <main id="main-content">
        @if(session('success'))<p class="landing-notice" role="status">{{ session('success') }}</p>@endif
        @if($errors->any())<div class="landing-notice" role="alert"><strong>Ada yang perlu diperiksa:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
