<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'registration_id',
        'payment_method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_url',
        'payment_data',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    // Payment method constants
    const METHOD_VNPAY = 'vnpay';
    const METHOD_PAYPAL = 'paypal';
    const METHOD_STRIPE = 'stripe';
    const METHOD_CREDIT_CARD = 'credit_card';

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ─── Accessors & Mutators ─────────────────────────────────────────────────

    public function getIsPaidAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' ' . $this->currency;
    }
}
