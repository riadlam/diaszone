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
        'region',
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

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function digiflazzStatuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DigiflazzStatus::class);
    }

    /**
     * Margin between what we sell at and what the pack costs us.
     */
    public function profitDzd(): ?float
    {
        if (! $this->price_dzd || $this->base_price_dzd === null) {
            return null;
        }

        return (float) $this->price_dzd - (float) $this->base_price_dzd;
    }

    public function profitPercentage(): ?float
    {
        $base = (float) $this->base_price_dzd;

        if ($base <= 0 || ! $this->price_dzd) {
            return null;
        }

        return (((float) $this->price_dzd - $base) / $base) * 100;
    }

    /**
     * A real DZD cost is a small fraction of the Digiflazz IDR price, so a cost
     * anywhere near the IDR figure was never converted and makes the margin wrong.
     */
    public const UNCONVERTED_COST_RATIO = 0.25;

    public function hasUnconvertedCost(): bool
    {
        return $this->base_price_dzd !== null
            && (float) $this->price > 0
            && (float) $this->base_price_dzd >= (float) $this->price * self::UNCONVERTED_COST_RATIO;
    }

    public function usesDigiflazz(): bool
    {
        return \App\Support\GameProvider::usesDigiflazz($this->game_type);
    }
}