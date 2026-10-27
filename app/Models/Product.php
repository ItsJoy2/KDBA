<?php

namespace App\Models;

use App\Models\OrderItem;
use App\Models\OrderSerial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'buy_price',
        'selling_price',
        'stock_quantity',
        'track_serials',
        'serial_start',
        'serial_end',
        'current_serial',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'buy_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'track_serials' => 'boolean',
            'serial_start' => 'integer',
            'serial_end' => 'integer',
            'current_serial' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderSerials(): HasMany
    {
        return $this->hasMany(OrderSerial::class);
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}