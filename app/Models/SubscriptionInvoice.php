<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'subscription_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'gateway_invoice_id',
        'gateway_payment_url',
        'paid_at',
        'due_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'due_at'  => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function formattedAmount(): string
    {
        return '$' . number_format($this->amount / 100, 2) . ' ' . $this->currency;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
