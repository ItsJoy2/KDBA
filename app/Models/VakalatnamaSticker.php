<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VakalatnamaSticker extends Model
{
    /**
     * Prevent unique number from appearing in
     * normal JSON/API serialization.
     */
    protected $hidden = [
        'unique_number',
    ];

    protected $fillable = [
        'order_id',
        'user_id',
        'unique_number',
        'status',
        'printed_at',
        'print_count',
        'last_printed_by',
    ];

    protected function casts(): array
    {
        return [
            'printed_at' => 'datetime',
            'print_count' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            VakalatnamaStickerOrder::class,
            'order_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastPrintedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_printed_by');
    }

    public function printLogs(): HasMany
    {
        return $this->hasMany(
            VakalatnamaStickerPrintLog::class,
            'sticker_id'
        );
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopePrinted($query)
    {
        return $query->where('status', 'printed');
    }

    public function isPrintable(): bool
    {
        return $this->status === 'ready'
            && $this->printed_at === null
            && $this->print_count === 0
            && $this->order?->status === 'approved';
    }
}
