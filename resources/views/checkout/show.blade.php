@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<div class="page-heading">
    <span class="eyebrow">PERIKSA PESANANMU</span>
    <h1>Sedikit lagi, siap diproses!</h1>
</div>

<div class="two-column">
    <form method="POST" action="{{ route('checkout.store') }}" class="panel stack" id="checkout-form" data-once>
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $token }}">

        <h2>1. Data Pemesan</h2>
        <div class="field">
            <label for="customer_name">Nama Pemesan</label>
            <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required maxlength="100" autocomplete="name">
        </div>
        <div class="field">
            <label for="phone">Nomor WhatsApp / HP Aktif</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required minlength="8" maxlength="25" placeholder="08xxxxxxxxxx" autocomplete="tel">
            <small class="muted">Nomor ini akan digunakan untuk konfirmasi pesanan.</small>
        </div>

        <h2>2. Opsi Pengantaran</h2>
        <div class="choice-cards">
            <label class="choice-card" id="card-pickup">
                <input type="radio" name="fulfillment_type" value="pickup" @checked(old('fulfillment_type', 'pickup') === 'pickup') required>
                <div class="choice-content">
                    <span class="choice-icon">🏪</span>
                    <div>
                        <strong>Ambil di Kedai</strong>
                        <p class="muted">Ambil langsung pesananmu saat sudah siap.</p>
                        <span class="badge completed">Gratis Ongkir</span>
                    </div>
                </div>
            </label>

            <label class="choice-card" id="card-delivery">
                <input type="radio" name="fulfillment_type" value="delivery" @checked(old('fulfillment_type') === 'delivery') required>
                <div class="choice-content">
                    <span class="choice-icon">🛵</span>
                    <div>
                        <strong>Dikirim ke Alamat</strong>
                        <p class="muted">Pesanan diantarkan ke lokasimu.</p>
                        <span class="badge ready">+Rp {{ number_format($deliveryFee, 0, ',', '.') }}</span>
                    </div>
                </div>
            </label>
        </div>

        <div class="field" id="delivery-address-container" style="{{ old('fulfillment_type', 'pickup') === 'delivery' ? '' : 'display: none;' }}">
            <label for="delivery_address">Alamat Lengkap Pengiriman <span class="danger">*</span></label>
            <textarea id="delivery_address" name="delivery_address" rows="3" maxlength="500" placeholder="Nama Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Patokan / Detail">{{ old('delivery_address') }}</textarea>
        </div>

        <div class="field">
            <label for="notes">Catatan Pesanan (opsional)</label>
            <textarea id="notes" name="notes" rows="2" maxlength="1000" placeholder="Contoh: Saus dipisah, jangan pakai acar, dll.">{{ old('notes') }}</textarea>
        </div>

        <h2>3. Metode Pembayaran</h2>
        <div class="choice-cards">
            <label class="choice-card active-method" id="card-qris">
                <input type="radio" name="payment_method" value="qris" @checked(old('payment_method', 'qris') === 'qris') required>
                <div class="choice-content">
                    <span class="choice-icon">📱</span>
                    <div>
                        <strong>QRIS & Konfirmasi WhatsApp</strong>
                        <p class="muted">Scan dengan BCA, Mandiri, BRI, GoPay, OVO, DANA, ShopeePay, dll. Langsung konfirmasi via WA.</p>
                        <span class="badge processing">Direkomendasikan</span>
                    </div>
                </div>
            </label>

            <label class="choice-card" id="card-cash">
                <input type="radio" name="payment_method" value="cash_on_pickup" @checked(old('payment_method') === 'cash_on_pickup') required>
                <div class="choice-content">
                    <span class="choice-icon">💵</span>
                    <div>
                        <strong>Bayar Tunai di Kedai</strong>
                        <p class="muted">Bayar uang tunai saat mengambil burger di kedai.</p>
                        <small class="muted" id="cash-note">(Hanya untuk Ambil di Kedai)</small>
                    </div>
                </div>
            </label>
        </div>

        <button class="button full" type="submit" id="submit-order-btn">
            Buat Pesanan · <span id="btn-total-display">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
        </button>
        <a class="text-link" href="{{ route('cart.index') }}">← Ubah isi keranjang</a>
    </form>

    <aside class="panel summary" id="checkout-summary" data-subtotal="{{ $subtotal }}" data-delivery-fee="{{ $deliveryFee }}">
        <h2>Yang kamu pesan</h2>
        <div class="order-items-list">
            @foreach($items as $item)
            <div class="order-line-item">
                <div class="order-line">
                    <span><strong>{{ $item['quantity'] }}×</strong> {{ $item['product']->name }}</span>
                    <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                </div>
                @if($item['addons']->isNotEmpty())
                <div class="summary-addons">
                    @foreach($item['addons'] as $addon)
                        <span class="addon-mini">+ {{ $addon->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="summary-breakdown">
            <div class="breakdown-row">
                <span>Subtotal Menu</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="breakdown-row">
                <span>Biaya Pengiriman</span>
                <span id="summary-delivery-fee">Rp 0</span>
            </div>
            <div class="total-row">
                <span>Total Tagihan</span>
                <strong id="summary-grand-total">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
            </div>
        </div>

        <p class="muted">Stok dan harga diverifikasi otomatis saat pemesanan dibuat.</p>
    </aside>
</div>
@endsection
