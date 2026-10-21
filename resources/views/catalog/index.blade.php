@extends('layouts.app')
@section('title', 'Menu favoritmu')
@section('content')
<section class="hero">
    <div class="hero-copy"><span class="eyebrow">LAPAR? MAK YAY AJA.</span><h1>Gigitan kecil.<br><em>Bahagia besar.</em></h1><p>Temukan burger favoritmu dan teman makannya. Pesan dari sini, ambil di kedai saat sudah siap.</p><a href="#menu" class="button">Lihat menu <span aria-hidden="true">↗</span></a><div class="hero-note">01. Pilih menu &nbsp; 02. Pesan &nbsp; 03. Ambil & nikmati</div></div>
    <div class="hero-art"><span class="stamp">GOOD FOOD.<br>GOOD MOOD.</span><img src="{{ asset('images/menu-placeholder.svg') }}" alt="Ilustrasi burger Mak Yay" width="480" height="400"><span class="art-caption">BURGER TIME, ANYTIME.</span></div>
</section>
<section id="menu" class="section">
    <div class="section-heading"><div><span class="eyebrow">PILIH YANG KAMU SUKA</span><h2>Menu Mak Yay</h2></div><p class="muted">{{ $products->total() }} menu ditemukan</p></div>
    <form class="filter-bar" action="{{ route('catalog.index') }}" method="GET">
        <div class="field grow"><label for="q">Cari menu</label><input id="q" name="q" placeholder="Lagi ingin makan apa?" value="{{ request('q') }}" maxlength="100"></div>
        <div class="field">
            <label for="category">Kategori</label>
            <select id="category" name="category" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button class="button" type="submit">Cari</button><a class="text-link" href="{{ route('catalog.index') }}#menu">Reset</a>
    </form>
    @php
        $groupedProducts = $products->groupBy(function($item) {
            return $item->category->name ?? 'Lainnya';
        });
    @endphp
    @forelse($groupedProducts as $categoryName => $categoryProducts)
        <div class="category-section-header" style="margin-top: 2.2rem; margin-bottom: 1.2rem;">
            <span class="eyebrow" style="margin-bottom: 4px;">MENU PILIHAN</span>
            <h3 style="font-size: 32px; margin: 0;">{{ $categoryName }}</h3>
        </div>
        <div class="product-grid">
            @foreach($categoryProducts as $product)
            <article class="product-card">
                <a class="product-image" href="{{ route('products.show', $product) }}"><img src="{{ $product->image_url }}" alt="{{ $product->image_path ? $product->name : 'Ilustrasi placeholder; foto menu belum ditambahkan' }}" loading="lazy" width="400" height="300">@if(!$product->image_path)<span class="image-note">Foto belum ditambahkan</span>@endif</a>
                <div class="product-body"><span class="eyebrow">{{ $product->category->name }}</span><h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                    <div class="product-bottom"><strong class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</strong><span class="stock">{{ $product->stock > 0 ? 'Stok '.$product->stock : 'Habis' }}</span></div>
                    <form action="{{ route('cart.store', $product) }}" method="POST" data-once>@csrf<input type="hidden" name="quantity" value="1"><button class="button outline full" type="submit" @disabled($product->stock < 1)>{{ $product->stock > 0 ? '+ Tambah ke keranjang' : 'Stok habis' }}</button></form>
                </div>
            </article>
            @endforeach
        </div>
    @empty
        <div class="empty"><h3>Belum ada menu yang cocok.</h3><p>Coba kata kunci lain, atau tunggu admin menambahkan menu.</p><a class="text-link" href="{{ route('catalog.index') }}">Lihat semua menu</a></div>
    @endforelse
    @include('partials.pagination', ['paginator' => $products])
</section>
@endsection
