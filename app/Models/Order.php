<?php

namespace App\Models;

use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu',
        'processing' => 'Disiapkan',
        'ready' => 'Siap diambil',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'number',
        'user_id',
        'checkout_token',
        'customer_name',
        'phone',
        'notes',
        'fulfillment_type',
        'delivery_address',
        'delivery_fee',
        'status',
        'payment_method',
        'payment_status',
        'payment_proof',
        'paid_at',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'delivery_fee' => 'integer',
            'user_id' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getGrandTotalAttribute(): int
    {
        return (int) $this->total + (int) ($this->delivery_fee ?? 0);
    }

    public function getIsDeliveryAttribute(): bool
    {
        return $this->fulfillment_type === 'delivery';
    }

    public function getFulfillmentLabelAttribute(): string
    {
        return $this->fulfillment_type === 'delivery' ? 'Dikirim ke Alamat' : 'Ambil di Kedai';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method === 'qris' ? 'QRIS' : 'Bayar Tunai di Kedai';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->payment_status === 'paid' ? 'Lunas' : 'Menunggu Pembayaran';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'ready' && $this->is_delivery) {
            return 'Siap dikirim';
        }

        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getWhatsappUrlAttribute(): string
    {
        return app(WhatsAppService::class)->generateUrl($this);
    }

    public function getWhatsappMessageAttribute(): string
    {
        return app(WhatsAppService::class)->formatMessage($this);
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? asset('storage/' . $this->payment_proof) : null;
    }

    public function nextStatuses(): array
    {
        return match ($this->status) {
            'pending' => ['processing', 'cancelled'],
            'processing' => ['ready', 'cancelled'],
            'ready' => ['completed', 'cancelled'],
            default => [],
        };
    }
}
