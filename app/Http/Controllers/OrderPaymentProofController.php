<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderPaymentProofController extends Controller
{
    public function store(Request $request, Order $order)
    {
        // Ensure user is authorized
        if ($request->user()->id !== $order->user_id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'payment_proof.required' => 'Pilih foto atau screenshot bukti transfer.',
            'payment_proof.image' => 'File harus berupa gambar.',
            'payment_proof.mimes' => 'Format gambar yang didukung: JPEG, PNG, JPG, WEBP.',
            'payment_proof.max' => 'Ukuran file maksimal 5MB.',
        ]);

        if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('payment_proof')->store('proofs', 'public');
        $order->update(['payment_proof' => $path]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Admin akan segera memverifikasi pesananmu.');
    }
}
