<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargilyStatus extends Model
{
    protected $table = 'chargily_status';

    protected $fillable = [
        'order_id',
        'checkout_id',
        'event_type',
        'status',
        'amount',
        'fees',
        'payment_method',
        'metadata',
        'webhook_data',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'metadata' => 'array',
        'webhook_data' => 'array',
    ];

    /**
     * Get the order that owns this Chargily status
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
            'paid' => 'green',
            'failed', 'canceled', 'expired' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }
}
