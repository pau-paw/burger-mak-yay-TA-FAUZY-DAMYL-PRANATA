<header class="shop-header">
    <a class="shop-brand" href="{{ route('home') }}" aria-label="Burger Mak Yay, beranda"><img src="{{ asset('images/landing/burger-icon.svg') }}" alt="" width="24" height="24"><span>Mak Yay</span></a>
    <nav class="shop-nav" aria-label="Navigasi pelanggan">
        <a href="{{ route('home') }}#menu">Menu</a>
        <a href="{{ route('cart.index') }}" @class(['current' => request()->routeIs('cart.*', 'checkout.*')])>Keranjang <span class="shop-count">{{ app(\App\Services\Cart::class)->count() }}</span></a>
        @auth
            <a href="{{ route('orders.index') }}" @class(['current' => request()->routeIs('orders.*')])>Pesananku</a>
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">Admin</a>@endif
            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Keluar</button></form>
        @else
            <a href="{{ route('login') }}" @class(['current' => request()->routeIs('login', 'register')])>Masuk</a>
        @endauth
    </nav>
</header>
