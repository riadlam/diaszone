<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Cryptopay extends Model
{
    protected $fillable = [
        'payment_id',
        'transaction_id',
        'diamond_pack_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'notes',
        'payment_data',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the diamond pack for this cryptopay.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Get the orders for this cryptopay.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
