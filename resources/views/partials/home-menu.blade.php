<section class="home-menu" id="menu" aria-labelledby="menu-title">
    <div class="home-menu-heading"><div><h2 class="display" id="menu-title">Big bites.<br>Made for you.</h2></div><p>{{ $products->total() }} menu tersedia<br><span>Tambah topping. Bikin versimu sendiri.</span></p></div>
    <form class="home-menu-filter" action="{{ route('home') }}#menu" method="GET">
        <div><label for="q">Cari menu</label><input type="search" id="q" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Burger, kebab…"></div>
        <div>
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
        <button type="submit" class="menu-button">Cari menu</button><a class="menu-reset" href="{{ route('home') }}#menu">Reset</a>
    </form>
    @php
        $groupedProducts = $products->groupBy(function($item) {
            return $item->category->name ?? 'Lainnya';
        });
    @endphp
    @forelse($groupedProducts as $categoryName => $categoryProducts)
        <div class="category-section-header" style="margin-top: {{ $loop->first ? '2.5rem' : '4.5rem' }}; margin-bottom: 1.8rem; text-align: center;">
            <h3 style="font-size: clamp(2rem, 5vw, 2.8rem); font-weight: 700; color: #1a1a1a; margin: 0; letter-spacing: -0.5px;">{{ $categoryName }}</h3>
        </div>
        <div class="home-product-grid">
            @foreach($categoryProducts as $product)
            <article class="home-product-card">
                <a class="home-product-image" href="{{ route('products.show', $product) }}"><img src="{{ $product->image_url }}" width="400" height="300" loading="lazy" alt="{{ $product->image_path ? $product->name : 'Ilustrasi menu; foto produk belum ditambahkan' }}">@if(!$product->image_path)<small>Foto belum ditambahkan</small>@endif</a>
                <div class="home-product-content"><span class="menu-eyebrow">{{ $product->category->name }}</span><h3 class="display"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                    <div class="home-product-price"><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong><small>{{ $product->stock > 0 ? 'Stok '.$product->stock : 'Habis' }}</small></div>
                    <div class="home-product-actions"><a class="menu-customize" href="{{ route('products.show', $product) }}">Detail & topping ↗</a><form action="{{ route('cart.store', $product) }}" method="POST" data-once>@csrf<input type="hidden" name="quantity" value="1"><button type="submit" class="menu-button" @disabled($product->stock < 1)>{{ $product->stock > 0 ? '+ Keranjang' : 'Stok habis' }}</button></form></div>
                </div>
            </article>
            @endforeach
        </div>
    @empty
        <div class="home-menu-empty"><h3 class="display">Belum ada menu yang cocok.</h3><p>Coba kata kunci atau kategori lain.</p><a class="menu-button" href="{{ route('home') }}#menu">Lihat semua menu</a></div>
    @endforelse
    @include('partials.pagination', ['paginator' => $products])
</section>
