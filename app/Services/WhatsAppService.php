<?php

namespace App\Services;

use App\Models\Order;

class WhatsAppService
{
    public function formatMessage(Order $order): string
    {
        $order->loadMissing(['items.addons']);

        $lines = [];
        $lines[] = "Halo Burger Mak Yay! 🍔";
        $lines[] = "Saya ingin konfirmasi pesanan saya:";
        $lines[] = "";
        $lines[] = "📋 *DETAIL PESANAN*";
        $lines[] = "• No. Pesanan: #" . $order->number;
        $lines[] = "• Nama: " . $order->customer_name;
        $lines[] = "• No. HP: " . $order->phone;
        $lines[] = "• Pengantaran: *" . ($order->fulfillment_type === 'delivery' ? '🛵 Dikirim ke Alamat' : '🏪 Ambil di Kedai') . "*";

        if ($order->fulfillment_type === 'delivery' && ! empty($order->delivery_address)) {
            $lines[] = "• Alamat Tujuan: " . $order->delivery_address;
        }

        if (! empty($order->notes)) {
            $lines[] = "• Catatan: " . $order->notes;
        }

        $lines[] = "";
        $lines[] = "🛒 *MENU YANG DIPESAN:*";

        foreach ($order->items as $item) {
            $addonText = '';
            if ($item->addons && $item->addons->isNotEmpty()) {
                $addonNames = $item->addons->map(function ($addon) {
                    return $addon->addon_name . ' (+Rp ' . number_format($addon->addon_price, 0, ',', '.') . ')';
                })->implode(', ');
                $addonText = "\n   Topping: " . $addonNames;
            }

            $lines[] = "• " . $item->quantity . "x " . $item->product_name . " = Rp " . number_format($item->subtotal, 0, ',', '.') . $addonText;
        }

        $lines[] = "";
        $lines[] = "Subtotal Menu: Rp " . number_format($order->total, 0, ',', '.');
        if ($order->fulfillment_type === 'delivery') {
            $lines[] = "Ongkos Kirim: Rp " . number_format($order->delivery_fee, 0, ',', '.');
        }
        $lines[] = "*TOTAL TAGIHAN: Rp " . number_format($order->grand_total, 0, ',', '.') . "*";
        $lines[] = "Metode Pembayaran: *" . ($order->payment_method === 'qris' ? 'QRIS' : 'Bayar Tunai di Kedai') . "*";

        if ($order->payment_method === 'qris') {
            $lines[] = "";
            $lines[] = "Saya sudah melakukan transfer via QRIS. Mohon segera diproses ya! (Bukti transfer saya lampirkan). Terima kasih!";
        } else {
            $lines[] = "";
            $lines[] = "Saya akan bayar tunai saat mengambil pesanan di kedai. Terima kasih!";
        }

        $lines[] = "";
        $lines[] = "Tautan Pesanan: " . route('orders.show', $order);

        return implode("\n", $lines);
    }

    public function generateUrl(Order $order): string
    {
        $phone = config('shop.whatsapp_number', '6281234567890');
        // Clean phone number to digits
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $message = $this->formatMessage($order);

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message);
    }
}
