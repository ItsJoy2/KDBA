<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentGateway;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'order_id',
        'vakalatnama_sticker_order_id',
        'invoice_item_id',
        'order_item_id',
        'user_id',
        'category_id',
        'payment_gateway_id',
        'trx_id',
        'sender_mobile',
        'amount',
        'description',
        'status',
        'source',
        'recorded_by',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'receipt_path',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    public function vakalatnamaStickerOrder(): BelongsTo
    {
        return $this->belongsTo(
            VakalatnamaStickerOrder::class,
            'vakalatnama_sticker_order_id'
        );
    }
}
