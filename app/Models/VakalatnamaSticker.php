<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VakalatnamaSticker extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'record_number',
        'unique_number',
        'status',
        'print_count',
        'printed_at',
        'printed_by',
        'last_printed_at',
        'last_printed_by',
    ];

    protected $hidden = [
        'unique_number',
    ];

    protected $casts = [
        'print_count' => 'integer',
        'printed_at' => 'datetime',
        'last_printed_at' => 'datetime',
    ];

    /**
     * Parent order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(
            VakalatnamaStickerOrder::class,
            'order_id'
        );
    }

    /**
     * Member who owns the sticker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who performed the first print.
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'printed_by'
        );
    }

    /**
     * User who performed the most recent print.
     */
    public function lastPrinter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'last_printed_by'
        );
    }

    /**
     * Printing audit history.
     */
    public function printLogs(): HasMany
    {
        return $this->hasMany(
            VakalatnamaStickerPrintLog::class,
            'sticker_id'
        );
    }

    /**
     * Scope: issued stickers.
     */
    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope: not yet printed.
     */
    public function scopeUnprinted(Builder $query): Builder
    {
        return $query->where('print_count', 0);
    }

    /**
     * Determine whether the sticker has been printed.
     */
    public function isPrinted(): bool
    {
        return $this->print_count > 0;
    }

    /**
     * Determine whether the sticker can be printed
     * for the first time.
     */
    public function canPrint(): bool
    {
        return $this->status === 'issued'
            && $this->unique_number !== null
            && $this->print_count === 0;
    }
}
