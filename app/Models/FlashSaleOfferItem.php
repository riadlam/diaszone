<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleOfferItem extends Model
{
    protected $fillable = [
        'flash_sale_offer_id',
        'diamond_pack_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(FlashSaleOffer::class, 'flash_sale_offer_id');
    }

    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }
}
