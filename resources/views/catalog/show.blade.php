@extends('layouts.app')
@section('title', $product->name)
@section('content')
<a class="back" href="{{ route('home') }}#menu">← Kembali ke menu</a>
<section class="detail-grid">
    <div class="detail-image">
        <img src="{{ $product->image_url }}" alt="{{ $product->image_path ? $product->name : 'Ilustrasi placeholder menu' }}">
        @if(!$product->image_path)
            <p class="muted">Ilustrasi. Foto produk belum ditambahkan.</p>
        @endif
    </div>
    <div class="detail-copy">
        <span class="eyebrow">{{ $product->category->name }}</span>
        <h1>{{ $product->name }}</h1>
        <p class="large-price" id="base-price" data-price="{{ $product->price }}">
            Rp {{ number_format($product->price, 0, ',', '.') }}
        </p>
        <p class="stock-info">
            {{ $product->stock > 0 ? '✓ Tersedia '.$product->stock.' porsi' : '✕ Stok sedang habis' }}
        </p>

        <form action="{{ route('cart.store', $product) }}" method="POST" class="stack" id="product-form" data-once>
            @csrf

            @if(isset($addons) && $addons->isNotEmpty())
            <div class="addons-section">
                <span class="eyebrow">TAMBAHAN TOPPING (OPSIONAL)</span>
                <div class="addons-list">
                    @foreach($addons as $addon)
                    <label class="addon-card">
                        <input type="checkbox" name="addons[]" value="{{ $addon->id }}" data-price="{{ $addon->price }}" class="addon-checkbox">
                        <span class="addon-info">
                            <span class="addon-name">{{ $addon->name }}</span>
                            <span class="addon-price">
                                {{ $addon->price > 0 ? '+Rp ' . number_format($addon->price, 0, ',', '.') : 'Gratis' }}
                            </span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="field">
                <label for="quantity">Jumlah Porsi</label>
                <input class="quantity" id="quantity" type="number" name="quantity" value="1" min="1" max="{{ max(1, min(99, $product->stock)) }}" required>
            </div>

            <div class="product-total-preview">
                <span>Subtotal menu ini:</span>
                <strong id="live-total-preview">Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
            </div>

            <button class="button" type="submit" @disabled($product->stock < 1)>
                Tambah ke keranjang →
            </button>
        </form>
    </div>
</section>
@endsection
