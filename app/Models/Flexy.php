<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flexy extends Model
{
    protected $fillable = [
        'receipt_image',
        'diamond_pack_id',
        'status',
    ];

    /**
     * Get the diamond pack for this flexy.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Get the orders for this flexy.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
