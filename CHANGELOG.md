# Perubahan dari arsip awal

## 2026-09-14 — Implementasi awal Burger Mak Yay

- Mempertahankan Laravel dan composer.lock yang dikirim; tidak mengganti framework.
- Menetapkan MySQL sebagai konfigurasi utama, dengan opsi SQLite untuk demo/test.
- Menambahkan empat tabel toko, role pelanggan/admin, foreign key, dan indeks.
- Mengganti halaman welcome Laravel dengan katalog, detail, keranjang, checkout, dan pesanan.
- Menambahkan login/logout, pendaftaran pelanggan, middleware admin, dan perintah pembuatan admin.
- Menambahkan pengelolaan kategori, foto/menu/stok, pengarsipan/pemulihan, serta status pesanan.
- Menyimpan snapshot nama/harga menu pada setiap item pesanan.
- Menghitung total di server, validasi stok di transaksi, mengunci baris, dan memeriksa token checkout.
- Menambahkan pembatalan dengan pengembalian stok serta konfirmasi pembayaran saat penyelesaian.
- Menambahkan UI berbahasa Indonesia, validasi utama Indonesia, CSS responsif dan JavaScript progresif.
- Menambahkan test bisnis dan keamanan, panduan instalasi, checklist production.
- Menonaktifkan setup migrasi paksa dan pembaruan APP_KEY otomatis dari skrip composer setup.
- Menghilangkan runtime/rahasia dari paket distribusi; arsip sumber tetap dipertahankan terpisah selama penyusunan.

Tidak ditambahkan: payment gateway, pengiriman, inventori bahan baku, refund, reset password, verifikasi email, notifikasi eksternal, atau realtime. Batasan dan status verifikasi ada di README.md dan TESTING.md.
