<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondPack extends Model
{
    protected $fillable = [
        'game_type',
        'name',
        'code',
        'diamonds',
        'bonus_diamonds',
        'price',
        'price_dzd',
        'base_price_dzd',
        'price_usd',
        'discount_percentage',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_dzd' => 'decimal:2',
        'base_price_dzd' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getTotalDiamondsAttribute(): int
    {
        return $this->diamonds + $this->bonus_diamonds;
    }

    /**
     * Get the orders for this diamond pack.
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the flexies for this diamond pack.
     */
    public function flexies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Flexy::class);
    }
}
