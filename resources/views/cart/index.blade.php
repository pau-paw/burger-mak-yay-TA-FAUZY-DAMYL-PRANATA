@extends('layouts.app')
@section('title', 'Keranjang')
@section('content')
<div class="page-heading">
    <span class="eyebrow">SELENGKAH LAGI KE MAK YAY</span>
    <h1>Keranjangmu.</h1>
</div>

@if($items->isEmpty() && count($missingKeys ?? []) === 0)
    <div class="empty panel">
        <h2>Keranjang masih kosong.</h2>
        <p>Yuk, cari menu lezat yang bikin harimu lebih bersemangat.</p>
        <a class="button" href="{{ route('home') }}#menu">Pilih menu</a>
    </div>
@else
<div class="two-column">
    <div class="stack">
        @foreach($missingKeys ?? [] as $key)
            <div class="panel">
                <p>Menu pada baris ini sudah tidak tersedia.</p>
                <form method="POST" action="{{ route('cart.destroy', $key) }}">
                    @csrf
                    @method('DELETE')
                    <button class="link-button danger" type="submit">Hapus dari keranjang</button>
                </form>
            </div>
        @endforeach

        @foreach($items as $item)
        <article class="cart-row panel">
            <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}" width="100" height="100">
            <div class="grow">
                <h3>{{ $item['product']->name }}</h3>
                <p class="muted">
                    Rp {{ number_format($item['product']->price, 0, ',', '.') }} / porsi
                    @if($item['addons_total'] > 0)
                        + Topping Rp {{ number_format($item['addons_total'], 0, ',', '.') }}
                    @endif
                </p>

                @if($item['addons']->isNotEmpty())
                    <div class="item-addons-tags">
                        <span class="tag-label">Topping:</span>
                        @foreach($item['addons'] as $addon)
                            <span class="addon-badge">{{ $addon->name }} (+Rp {{ number_format($addon->price, 0, ',', '.') }})</span>
                        @endforeach
                    </div>
                @endif

                @if(!$item['product']->is_active || $item['quantity'] > $item['product']->stock)
                    <p class="danger">Menu tidak aktif atau stok kurang. Tersisa {{ $item['product']->stock }}.</p>
                @endif

                <div class="row wrap">
                    <form class="row" method="POST" action="{{ route('cart.update', $item['key']) }}">
                        @csrf
                        @method('PATCH')
                        <label class="sr-only" for="qty-{{ $item['key'] }}">Jumlah {{ $item['product']->name }}</label>
                        <input class="quantity" id="qty-{{ $item['key'] }}" name="quantity" type="number" min="1" max="99" value="{{ $item['quantity'] }}" required>
                        <button class="button small outline" type="submit">Ubah</button>
                    </form>
                    <form method="POST" action="{{ route('cart.destroy', $item['key']) }}">
                        @csrf
                        @method('DELETE')
                        <button class="link-button danger" type="submit">Hapus</button>
                    </form>
                </div>
            </div>
            <div class="cart-line-subtotal">
                <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
            </div>
        </article>
        @endforeach
    </div>

    <aside class="panel summary">
        <h2>Ringkasan</h2>
        <div class="total-row">
            <span>Total menu</span>
            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>
        <div class="info-box mini">
            <p><strong>Pilihan Pengantaran:</strong><br>Ambil di Kedai (Gratis) atau Dikirim ke Alamat (+Rp {{ number_format(config('shop.delivery_fee', 10000), 0, ',', '.') }}) ditentukan di halaman checkout.</p>
        </div>
        <a class="button full" href="{{ route('checkout.show') }}">Lanjut checkout →</a>
        <a class="text-link" href="{{ route('home') }}#menu">Tambah menu lainnya</a>
    </aside>
</div>
@endif
@endsection
