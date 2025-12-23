<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondPack extends Model
{
    protected $fillable = [
        'game_id',
        'game_type',
        'name',
        'membership_name',
        'code',
        'diamonds',
        'special_quantity',
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
        'special_quantity' => 'integer',
        'game_id' => 'integer',
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

    /**
     * Get the game that owns this diamond pack.
     */
    public function game(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}