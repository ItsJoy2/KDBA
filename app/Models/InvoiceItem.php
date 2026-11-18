<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'fee_key',
        'title',
        'description',
        'unit_amount',
        'quantity',
        'amount',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount' => 'decimal:2',
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'metadata' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
