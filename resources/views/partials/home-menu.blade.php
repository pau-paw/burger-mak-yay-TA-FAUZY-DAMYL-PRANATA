<section class="home-menu" id="menu" aria-labelledby="menu-title">
    <div class="home-menu-heading"><div><h2 class="display" id="menu-title">Satu gigitan.<br>Untukmu.</h2></div><p>{{ $products->total() }} menu tersedia<br></p></div>
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
        <button type="submit" class="menu-button">Cari menu</button>
        @php
            $currentSort = request('sort');
            $nextSort = ($currentSort === 'name_asc') ? 'name_desc' : (($currentSort === 'name_desc') ? null : 'name_asc');
            $sortTitle = ($currentSort === 'name_asc') ? 'Urutan: A-Z (Klik untuk Z-A)' : (($currentSort === 'name_desc') ? 'Urutan: Z-A (Klik untuk reset)' : 'Urutkan nama menu berdasarkan abjad');
            $sortParams = array_merge(request()->except(['page', 'sort']), $nextSort ? ['sort' => $nextSort] : []);
            $sortUrl = route('home', $sortParams) . '#menu';
        @endphp
        <a href="{{ $sortUrl }}" class="menu-button" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; width: 46px; min-width: 46px; padding: 0; background: {{ $currentSort ? '#805747' : 'var(--yellow)' }}; color: {{ $currentSort ? '#fffaf0' : 'var(--brown)' }};" title="{{ $sortTitle }}" aria-label="{{ $sortTitle }}">
            @if($currentSort === 'name_desc')
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 5h10M11 9h7M11 13h4"/>
                    <path d="M3 7l3-3 3 3M6 4v14"/>
                </svg>
            @else
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 5h10M11 9h7M11 13h4"/>
                    <path d="M3 17l3 3 3-3M6 18V4"/>
                </svg>
            @endif
        </a>
        <a class="menu-reset" href="{{ route('home') }}#menu" title="Reset pencarian & filter" aria-label="Reset pencarian & filter" style="color: var(--brown); text-decoration: none; padding: 10px; display: inline-flex; align-items: center; justify-content: center; transition: transform 0.2s ease, opacity 0.2s ease;" onmouseover="this.style.opacity='0.75'; this.style.transform='rotate(-45deg)';" onmouseout="this.style.opacity='1'; this.style.transform='rotate(0deg)';">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <path d="M3 3v5h5"/>
            </svg>
        </a>
    </form>
    @php
        $groupedProducts = $products->groupBy(function($item) {
            return $item->category->name ?? 'Lainnya';
        });
    @endphp
    @forelse($groupedProducts as $categoryName => $categoryProducts)
        <div class="category-section-header" style="margin-top: {{ $loop->first ? '2.5rem' : '4.5rem' }}; margin-bottom: 1.5rem; text-align: center;">
            <h3 style="font-size: 40px; font-weight: 800; margin: 0; color: #1a1a1a; letter-spacing: -0.5px;">{{ $categoryName }} Pilihan</h3>
        </div>
        <div class="home-product-grid">
            @foreach($categoryProducts as $product)
            <article class="home-product-card">
                <a class="home-product-image" href="{{ route('products.show', $product) }}"><img src="{{ $product->image_url }}" width="400" height="300" loading="lazy" alt="{{ $product->image_path ? $product->name : 'Ilustrasi menu; foto produk belum ditambahkan' }}">@if(!$product->image_path)<small>Foto belum ditambahkan</small>@endif</a>
                <div class="home-product-content"><h3 class="display"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                    <div class="home-product-price"><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong></div>
                    <div class="home-product-actions"><form action="{{ route('cart.store', $product) }}" method="POST" data-once>@csrf<input type="hidden" name="quantity" value="1"><button type="submit" class="menu-button" @disabled($product->stock < 1)>{{ $product->stock > 0 ? '+ Keranjang' : 'Stok habis' }}</button></form></div>
                </div>
            </article>
            @endforeach
        </div>
    @empty
        <div class="home-menu-empty"><h3 class="display">Belum ada menu yang cocok.</h3><p>Coba kata kunci atau kategori lain.</p><a class="menu-button" href="{{ route('home') }}#menu">Lihat semua menu</a></div>
    @endforelse
    @include('partials.pagination', ['paginator' => $products])
</section>
