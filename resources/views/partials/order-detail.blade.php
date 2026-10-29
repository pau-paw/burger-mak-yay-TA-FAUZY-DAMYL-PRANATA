<section class="panel stack order-detail-panel">
    <div class="section-heading">
        <div>
            <h2>Rincian Pesanan</h2>
            <p class="order-number">#{{ $order->number }}</p>
        </div>
        <div class="row badges-row">
            <span class="badge {{ $order->status }}">{{ $order->status_label }}</span>
            <span class="badge {{ $order->payment_status === 'paid' ? 'completed' : 'pending' }}">
                {{ $order->payment_status === 'paid' ? '✓ Lunas' : 'Belum Dibayar' }}
            </span>
            <span class="badge outline">
                {{ $order->fulfillment_type === 'delivery' ? '🛵 Delivery' : '🏪 Pickup' }}
            </span>
        </div>
    </div>
    <p class="muted">Dibuat pada {{ $order->created_at->format('d M Y, H:i') }} WIB</p>

    <!-- Table of ordered items -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Menu & Topping</th>
                    <th>Harga Dasar</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->addons && $item->addons->isNotEmpty())
                        <div class="addons-snapshot-list">
                            @foreach($item->addons as $addon)
                                <span class="addon-chip">+ {{ $addon->addon_name }} (Rp {{ number_format($addon->addon_price, 0, ',', '.') }})</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pricing Summary -->
    <div class="order-summary-box">
        <div class="summary-line">
            <span>Subtotal Menu</span>
            <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
        </div>
        @if($order->fulfillment_type === 'delivery')
        <div class="summary-line">
            <span>Biaya Pengantaran (Delivery)</span>
            <strong>Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</strong>
        </div>
        @endif
        <div class="total-row">
            <span>Total Tagihan</span>
            <strong class="grand-total-highlight">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
        </div>
    </div>

    <!-- Metadata -->
    <dl class="metadata">
        <div>
            <dt>Nama Pemesan</dt>
            <dd>{{ $order->customer_name }}</dd>
        </div>
        <div>
            <dt>Nomor Telepon / WA</dt>
            <dd>{{ $order->phone }}</dd>
        </div>
        <div>
            <dt>Layanan</dt>
            <dd>
                <strong>{{ $order->fulfillment_label }}</strong>
                @if($order->fulfillment_type === 'delivery' && $order->delivery_address)
                    <br><span class="delivery-address-text">📍 {{ $order->delivery_address }}</span>
                @endif
            </dd>
        </div>
        <div>
            <dt>Metode Pembayaran</dt>
            <dd>{{ $order->payment_method_label }} · <strong>{{ $order->payment_status_label }}</strong></dd>
        </div>
        <div>
            <dt>Catatan Pesanan</dt>
            <dd>{{ $order->notes ?: 'Tidak ada catatan' }}</dd>
        </div>
    </dl>

    <!-- QRIS & WhatsApp Payment Box -->
    @if($order->payment_method === 'qris')
    <div class="payment-action-card {{ $order->payment_status === 'paid' ? 'paid-card' : '' }}">
        @if($order->payment_status === 'paid')
            <div class="paid-success-banner">
                <span class="paid-check">✓</span>
                <div>
                    <h3>Pembayaran Berhasil & Telah Dikonfirmasi</h3>
                    <p class="muted">
                        Terverifikasi pada {{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') . ' WIB' : 'Hari ini' }}. Pesananmu sedang diproses oleh dapur Mak Yay.
                    </p>
                </div>
            </div>
        @else
            <div class="qris-payment-content">
                <div class="qris-header">
                    <span class="eyebrow">PEMBAYARAN QRIS</span>
                    <h3>Scan Barcode & Konfirmasi ke WhatsApp</h3>
                    <p>Bisa dibayar dengan BCA Mobile, Livin' Mandiri, BRImo, BNI, GoPay, OVO, DANA, ShopeePay, LinkAja, atau mobile banking lainnya.</p>
                </div>

                <div class="qris-display-area">
                    <div class="qris-qr-wrapper">
                        <img src="{{ asset(config('shop.qris_image')) }}" alt="QRIS Barcode Burger Mak Yay" class="qris-image">
                        <a href="{{ asset(config('shop.qris_image')) }}" download="QRIS-Burger-Mak-Yay.svg" class="button small outline full download-qr-btn">
                            ⬇ Unduh Gambar QRIS
                        </a>
                    </div>
                    <div class="qris-instructions">
                        <div class="amount-card" style="background: transparent; border: 1px solid #ddbf9d; border-radius: 10px; padding: 12px 16px;">
                            <span class="amount-label" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7;">Total Bayar</span>
                            <div class="amount-value-row">
                                <span class="amount-value" id="order-nominal" data-nominal="{{ $order->grand_total }}">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </span>
                                <button type="button" class="copy-button copy-icon-btn" id="copy-amount-btn" title="Salin nominal" aria-label="Salin nominal pembayaran">
                                    <svg class="copy-idle-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg>
                                    <svg class="copy-success-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                                </button>
                                <span id="copy-amount-status" class="sr-only" role="status" aria-live="polite"></span>
                            </div>
                        </div>

                        <div class="action-buttons-group">
                            <a href="{{ $order->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="button full whatsapp-button">
                                @include('partials.whatsapp-icon')
                                <span>Konfirmasi ke WhatsApp Kedai</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    </div>
    @else
    <!-- Cash Payment Box -->
    <div class="cash-payment-info-box">
        <div class="cash-icon">💵</div>
        <div>
            <h3>Pembayaran Tunai di Kedai</h3>
            <p>Silakan tunjukkan nomor pesanan <strong>#{{ $order->number }}</strong> kepada kasir saat mengambil pesanan dan lakukan pembayaran secara tunai.</p>
            <a href="{{ $order->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="button small outline whatsapp-outline-btn">
                @include('partials.whatsapp-icon') Hubungi WhatsApp Kedai
            </a>
        </div>
    </div>
    @endif
</section>
