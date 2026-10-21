@extends('layouts.landing')

@section('content')
{{-- 1. HERO — tulisan tetap berupa HTML, gambar transparan berada di depannya. --}}
<section class="hero" aria-labelledby="hero-title">
    <h1 class="hero-title display" id="hero-title" lang="en">@foreach(config('landing.hero_lines') as $line)<span class="hero-line" style="--line-idx: {{ $loop->index }}">{{ $line }}</span>@endforeach</h1>
    <img class="hero-burger" src="{{ asset('images/landing/hero-burger.webp') }}" width="1254" height="1254" alt="Double cheeseburger dengan keju leleh, selada, tomat, dan bawang" fetchpriority="high" decoding="async">
    <div class="flavor-stamp" lang="en"><span>{{ config('landing.badge_top') }}</span><strong>{{ config('landing.badge_main') }}</strong><span>{{ config('landing.badge_bottom') }}</span></div>
</section>

{{-- 2. PROMO — seluruh kartu merupakan tautan ke katalog. --}}
<section class="promo-grid" aria-label="Pilihan Burger Mak Yay">
    <a class="promo-card flavor-card" href="{{ route('home') }}#menu">
        <img class="flavor-burger" src="{{ asset('images/landing/hero-burger.webp') }}" width="1254" height="1254" alt="" loading="lazy" decoding="async">
        <div class="flavor-copy"><h2 class="display" lang="en">{{ config('landing.flavor_title') }}</h2><p lang="en">{{ config('landing.flavor_description') }}</p></div>
    </a>
    <a class="promo-card fries-card" href="{{ route('home') }}#menu">
        <h2 class="display" lang="en">{{ config('landing.fries_title') }}</h2>
        <span class="crown-badge" aria-hidden="true">♛</span>
        <img src="{{ asset('images/landing/fries.webp') }}" width="1254" height="1254" alt="" loading="lazy" decoding="async">
        <small class="promo-note">{{ config('landing.promo_note') }}</small>
    </a>
    <a class="promo-card discover-card" href="{{ route('home') }}#menu">
        <h2 class="display" lang="en">{{ config('landing.discover_title') }}</h2>
        <img src="{{ asset('images/landing/hero-burger.webp') }}" width="1254" height="1254" alt="" loading="lazy" decoding="async">
    </a>
</section>

{{-- Menu dapat dipesan langsung dari mainpage, tanpa halaman katalog terpisah. --}}
@include('partials.home-menu')

{{-- 3. INGREDIENTS — skala dan posisi gambar diatur di .ingredient-burger. --}}
<section class="ingredients" aria-labelledby="ingredients-title">
    <h2 id="ingredients-title" class="visually-hidden">Rasa besar di setiap lapisan burger</h2>
    <p class="ingredient-type display" lang="en">@foreach(config('landing.story_lines') as $line)<span>{{ $line }}</span>@endforeach</p>
    <img class="ingredient-burger" src="{{ asset('images/landing/exploded-burger.webp') }}" width="725" height="2170" alt="Lapisan burger: roti wijen, keju, tomat, bawang, daging panggang, patty sapi, selada, dan roti bawah" loading="lazy" decoding="async">
</section>

{{-- 4. ULASAN — dapat dioperasikan dengan tombol dan keyboard; tanpa autoplay. --}}
@if(count(config('landing.reviews', [])) > 0)
<section class="review-card" aria-label="Cerita pencinta burger" aria-roledescription="carousel" data-review-carousel>
    <img class="review-photo" src="{{ asset('images/landing/customer.webp') }}" width="1086" height="1448" alt="Ilustrasi seorang pengunjung menikmati burger" loading="lazy" decoding="async">
    <div class="review-content">
        @if(config('landing.reviews_are_examples'))<span class="example-label">Contoh ulasan · ilustrasi</span>@endif
        <div class="review-slides" aria-live="polite" aria-atomic="true">
            @foreach(config('landing.reviews') as $review)
                <figure class="review-slide" data-review-slide @if(!$loop->first) hidden @endif role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ $loop->count }}">
                    <blockquote class="display" lang="en">{{ $review['quote'] }}</blockquote>
                    <figcaption>— {{ $review['name'] }}</figcaption>
                </figure>
            @endforeach
        </div>
        <div class="review-controls" data-review-controls hidden>
            <button type="button" class="arrow-button" data-review-prev aria-label="Ulasan sebelumnya">←</button>
            <span class="review-counter"><strong data-review-number>1</strong><span>/{{ count(config('landing.reviews')) }}</span></span>
            <button type="button" class="arrow-button" data-review-next aria-label="Ulasan berikutnya">→</button>
        </div>
    </div>
</section>
@endif

{{-- 5. FOOTER — CTA masuk ke katalog, akun dan keranjang memakai route asli. --}}
<footer class="landing-footer">
    <div class="footer-teaser">
        <div class="footer-copy"><h2 class="display" lang="en">{{ config('landing.footer_title') }}</h2><p>{{ config('landing.footer_description') }}</p></div>
        <img class="footer-fries" src="{{ asset('images/landing/fries.webp') }}" width="1254" height="1254" alt="" loading="lazy" decoding="async">
        <img class="footer-burger" src="{{ asset('images/landing/hero-burger.webp') }}" width="1254" height="1254" alt="" loading="lazy" decoding="async">
    </div>
    <div class="footer-panel">
        <nav class="footer-actions" aria-label="Aksi footer">
            <a class="pill" href="{{ route('home') }}#menu"><span class="bag-icon" aria-hidden="true"></span> Order Now</a>
            @auth
                <form action="{{ route('logout') }}" method="POST" class="footer-logout-form">
                    @csrf
                    <button type="submit" class="pill footer-logout-btn">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <a class="pill" href="{{ route('login') }}">
                    <span class="user-icon" aria-hidden="true"></span>
                    <span>Login</span>
                </a>
            @endauth
        </nav>
        <div class="footer-bottom">
            <nav aria-label="Tautan pelanggan">
                @auth
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" class="footer-bottom-logout">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </nav>
            <small>© {{ date('Y') }} Burger Mak Yay. All rights reserved.</small>
        </div>
    </div>
</footer>
@endsection
