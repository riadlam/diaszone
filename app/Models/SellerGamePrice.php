<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerGamePrice extends Model
{
    protected $fillable = [
        'seller_id',
        'diamond_pack_id',
        'custom_price_dzd',
        'custom_price_usd',
        'flexy_price',
        'is_active',
    ];

    protected $casts = [
        'custom_price_dzd' => 'decimal:2',
        'custom_price_usd' => 'decimal:2',
        'flexy_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the seller that owns this price
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the diamond pack for this price
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Check if custom price is valid (>= base price)
     */
    public function isValidPrice(): bool
    {
        $pack = $this->diamondPack;
        return $this->custom_price_dzd >= $pack->price_dzd 
            && $this->custom_price_usd >= $pack->price_usd;
    }

    /**
     * Get seller profit for DZD
     */
    public function getProfitDzdAttribute(): float
    {
        return $this->custom_price_dzd - $this->diamondPack->price_dzd;
    }

    /**
     * Get seller profit for USD
     */
    public function getProfitUsdAttribute(): float
    {
        return $this->custom_price_usd - $this->diamondPack->price_usd;
    }
}
