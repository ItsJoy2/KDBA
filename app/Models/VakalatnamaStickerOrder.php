<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VakalatnamaStickerOrder extends Model
{
    protected $fillable = [
        'user_id',
        'created_by',
        'order_number',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_status',
        'status',
        'transaction_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Member who owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin/staff who created the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Admin/staff who approved the payment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    /**
     * Associated payment transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'transaction_id'
        );
    }

    /**
     * Individual stickers belonging to this order.
     */
    public function stickers(): HasMany
    {
        return $this->hasMany(
            VakalatnamaSticker::class,
            'order_id'
        );
    }

    /**
     * Scope: paid orders.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope: pending payment orders.
     */
    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->whereIn(
            'payment_status',
            ['pending', 'processing']
        );
    }

    /**
     * Determine whether the order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Determine whether all stickers have been issued.
     */
    public function hasIssuedStickers(): bool
    {
        return $this->stickers()
            ->where('status', 'issued')
            ->exists();
    }
}
