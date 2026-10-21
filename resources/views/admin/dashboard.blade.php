@extends('layouts.app')
@section('title', 'Dashboard Admin · Burger Mak Yay')

@section('content')
{{-- 1. HERO BANNER — Sesuai Konsep Flavor-Card & Hero Landing Page --}}
<section class="admin-hero-card" aria-labelledby="admin-hero-title">
    <div class="admin-hero-copy">
        <span class="hero-tag">OPERASIONAL KEDAI · RUANG ADMIN</span>
        <h1 id="admin-hero-title" class="display">Halo, {{ auth()->user()->name }}!<br>Hari Enak Dimulai Di Sini.</h1>
        <p class="hero-desc">Ringkasan seluruh pesanan masuk, pantauan omzet kedai, dan kontrol ketersediaan menu secara langsung.</p>
        <div class="admin-hero-actions">
            <a class="pill pill-primary" href="{{ route('admin.orders.index') }}">
                <span class="pulse-dot"></span>
                <span>Pesanan Aktif ({{ $openOrders }}) &rarr;</span>
            </a>
            <a class="pill pill-secondary" href="{{ route('admin.products.create') }}">
                <span>+ Tambah Menu Baru</span>
            </a>
        </div>
    </div>
    <div class="admin-hero-art" aria-hidden="true">
        <img class="hero-burger-img" src="{{ asset('images/landing/hero-burger.webp') }}" width="1254" height="1254" alt="Burger Mak Yay">
        <div class="flavor-stamp admin-flavor-stamp" lang="en">
            <span>BURGER</span>
            <strong>MAK YAY</strong>
            <span>ADMIN</span>
        </div>
    </div>
</section>

{{-- 2. STATS SECTION — Sesuai Konsep Promo-Grid Landing Page --}}
<section class="admin-promo-grid" aria-label="Statistik Utama Kedai">
    {{-- Card 1: Omzet / Pendapatan (Brown & Yellow Theme - Fries Card Style) --}}
    <article class="stat-card stat-brown">
        <div class="stat-card-head">
            <span class="stat-eyebrow">TOTAL OMZET BERSIH</span>
            <span class="crown-badge" aria-hidden="true">♛</span>
        </div>
        <strong class="stat-number display">Rp {{ number_format($revenue, 0, ',', '.') }}</strong>
        <p class="stat-note">Pesanan berstatus selesai &amp; lunas</p>
        <img class="stat-corner-asset" src="{{ asset('images/landing/fries.webp') }}" width="300" height="300" alt="" aria-hidden="true">
    </article>

    {{-- Card 2: Pesanan Aktif (Vibrant Cheese Yellow - Flavor Card Style) --}}
    <article class="stat-card stat-yellow">
        <div class="stat-card-head">
            <span class="stat-eyebrow">PESANAN AKTIF</span>
            <span class="live-pill"><span class="pulse-dot"></span> Perlu Tindakan</span>
        </div>
        <strong class="stat-number display">{{ $openOrders }}</strong>
        <p class="stat-note">Menunggu sampai siap diambil / diantar</p>
        <a class="stat-link" href="{{ route('admin.orders.index') }}">Lihat antrean pesanan &rarr;</a>
    </article>

    {{-- Card 3: Menu Aktif (Cream Card Style) --}}
    <article class="stat-card stat-cream">
        <div class="stat-card-head">
            <span class="stat-eyebrow">VARIASI MENU</span>
            <span class="stat-mini-badge">🍔 KATALOG</span>
        </div>
        <strong class="stat-number display">{{ $productCount }}</strong>
        <p class="stat-note">Varian produk &amp; paket terdaftar di kedai</p>
        <a class="stat-link" href="{{ route('admin.products.index') }}">Kelola daftar menu &rarr;</a>
    </article>

    {{-- Card 4: Pelanggan (Honey Warm Card Style) --}}
    <article class="stat-card stat-honey">
        <div class="stat-card-head">
            <span class="stat-eyebrow">TOTAL PELANGGAN</span>
            <span class="stat-mini-badge">👥 MEMBER</span>
        </div>
        <strong class="stat-number display">{{ $customerCount }}</strong>
        <p class="stat-note">Akun pembeli terdaftar di Mak Yay</p>
        <span class="stat-tag">Komunitas Mak Yay</span>
    </article>
</section>

{{-- 3. CONTENT TWO-COLUMN — Pesanan Terbaru & Stok Menipis --}}
<div class="admin-main-grid">
    {{-- Kolom Kiri: Tabel Pesanan Terbaru --}}
    <section class="admin-card-panel table-panel" aria-labelledby="heading-recent-orders">
        <div class="panel-heading">
            <div>
                <span class="panel-eyebrow">AKTIVITAS TRANSAKSI</span>
                <h2 id="heading-recent-orders" class="display">Pesanan Terbaru</h2>
            </div>
            <a class="pill" href="{{ route('admin.orders.index') }}">Semua Pesanan <span aria-hidden="true">&rarr;</span></a>
        </div>
        @include('admin.orders.table')
    </section>

    {{-- Kolom Kanan: Stok Menipis & Aksi Cepat --}}
    <aside class="admin-card-panel aside-panel" aria-labelledby="heading-low-stock">
        <div class="panel-heading">
            <div>
                <span class="panel-eyebrow">PERINGATAN BAHAN</span>
                <h2 id="heading-low-stock" class="display">Stok Menipis</h2>
            </div>
            <span class="stock-warn-icon" aria-hidden="true">⚠️</span>
        </div>
        <p class="panel-desc">Menu aktif dengan sisa porsi &le; 5 unit.</p>

        <div class="stock-list">
            @forelse($lowStocks as $product)
                <div class="stock-item">
                    <div class="stock-item-info">
                        <a class="stock-name" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                        <span class="stock-cat">{{ $product->category->name ?? 'Menu' }}</span>
                    </div>
                    <span class="stock-count-badge">Sisa <strong>{{ $product->stock }}</strong></span>
                </div>
            @empty
                <div class="stock-empty">
                    <span class="check-icon">✓</span>
                    <p>Semua menu aktif memiliki persediaan stok yang aman.</p>
                </div>
            @endforelse
        </div>

        <div class="aside-actions">
            <a class="menu-button full" href="{{ route('admin.products.create') }}">+ Tambah Menu Baru</a>
            <a class="pill full text-center" href="{{ route('admin.products.index') }}">Lihat Seluruh Stok Menu</a>
        </div>
    </aside>
</div>
@endsection
