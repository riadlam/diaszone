<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VipResellerStatus extends Model
{
    protected $table = 'vipreseller_status';

    protected $fillable = [
        'order_id',
        'trxid',
        'data',
        'zone',
        'service',
        'status',
        'balance',
        'note',
        'price',
        'additional_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'additional_data' => 'array', // Automatically cast JSON to array
    ];

    /**
     * Get the order that owns this VIP Reseller status
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the status badge color class
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'green',
            'error' => 'red',
            'waiting' => 'yellow',
            default => 'gray',
        };
    }
}
