<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VakalatnamaStickerPrintLog extends Model
{
    protected $fillable = [
        'sticker_id',
        'printed_by',
        'print_type',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'printed_at' => 'datetime',
        ];
    }

    public function sticker(): BelongsTo
    {
        return $this->belongsTo(
            VakalatnamaSticker::class,
            'sticker_id'
        );
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
