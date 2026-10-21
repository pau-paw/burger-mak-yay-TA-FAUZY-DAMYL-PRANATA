@extends('layouts.app')
@section('title', 'Proses Pesanan #' . substr($order->number, 0, 8))
@section('content')
<a class="back" href="{{ route('admin.orders.index') }}">← Daftar pesanan</a>
<div class="page-heading">
    <span class="eyebrow">ADMINISTRASI PESANAN</span>
    <h1>Kelola Pesanan.</h1>
</div>

<div class="two-column">
    <div>
        @include('partials.order-detail')
    </div>

    <aside class="panel summary stack">
        <!-- 1. Status Flow -->
        <h2>Ubah Status Pesanan</h2>
        @if(count($order->nextStatuses()))
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="stack" data-confirm="Pastikan perubahan status sudah benar. Pembatalan akan otomatis mengembalikan stok menu.">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="status">Status Berikutnya</label>
                    <select id="status" name="status" required>
                        @foreach($order->nextStatuses() as $status)
                            <option value="{{ $status }}">
                                @if($status === 'ready' && $order->is_delivery)
                                    Siap dikirim (Kurir)
                                @else
                                    {{ \App\Models\Order::STATUSES[$status] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="button full" type="submit">Simpan Status</button>
            </form>
            <p class="note">Pembatalan mengembalikan stok satu kali. Pesanan selesai tidak dapat dibatalkan lagi.</p>
        @else
            <p class="muted">Pesanan ini sudah pada status akhir ({{ $order->status_label }}) dan tidak dapat diubah lagi.</p>
        @endif

        <!-- 2. Payment Management -->
        <hr class="divider">
        <h2>Kelola Pembayaran</h2>
        <div class="admin-payment-box">
            <p>
                Metode: <strong>{{ $order->payment_method_label }}</strong><br>
                Status: <span class="badge {{ $order->payment_status === 'paid' ? 'completed' : 'pending' }}">{{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' }}</span>
                @if($order->paid_at)
                    <br><small class="muted">Waktu bayar: {{ $order->paid_at->format('d/m/Y H:i') }}</small>
                @endif
            </p>

            @if($order->payment_proof)
                <div class="admin-proof-preview">
                    <strong>Bukti Transfer Pelanggan:</strong>
                    <a href="{{ $order->payment_proof_url }}" target="_blank" title="Klik untuk memperbesar">
                        <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer" class="proof-thumbnail">
                    </a>
                    <a href="{{ $order->payment_proof_url }}" target="_blank" class="text-link">Buka Gambar Penuh ↗</a>
                </div>
            @else
                <p class="muted"><small>Pelanggan belum mengunggah bukti transfer lewat web (mungkin dikirim via WhatsApp).</small></p>
            @endif

            <form method="POST" action="{{ route('admin.orders.payment_status', $order) }}" class="stack">
                @csrf
                @if($order->payment_status !== 'paid')
                    <input type="hidden" name="payment_status" value="paid">
                    <button class="button full success-btn" type="submit">
                        ✓ Verifikasi & Tandai Lunas
                    </button>
                @else
                    <input type="hidden" name="payment_status" value="unpaid">
                    <button class="button full outline danger" type="submit" data-confirm="Ubah kembali status menjadi Belum Dibayar?">
                        ✕ Batal Tandai Lunas
                    </button>
                @endif
            </form>
        </div>

        <!-- 3. Customer Info & Quick WhatsApp Contact -->
        <hr class="divider">
        <div class="info-box">
            <strong>Pelanggan:</strong>
            <p>
                {{ $order->customer_name }} ({{ $order->phone }})<br>
                {{ $order->user->email }}
            </p>

            @php
                $cleanCustPhone = preg_replace('/[^0-9]/', '', $order->phone);
                if (str_starts_with($cleanCustPhone, '0')) {
                    $cleanCustPhone = '62' . substr($cleanCustPhone, 1);
                }
                $adminWaText = urlencode("Halo kak " . $order->customer_name . ", kami dari Burger Mak Yay mengenai pesanan #" . $order->number . "...");
            @endphp
            <a href="https://wa.me/{{ $cleanCustPhone }}?text={{ $adminWaText }}" target="_blank" rel="noopener noreferrer" class="button small outline full whatsapp-outline-btn">
                💬 Chat WA Pelanggan
            </a>
        </div>
    </aside>
</div>
@endsection
