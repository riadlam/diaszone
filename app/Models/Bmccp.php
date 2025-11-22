<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bmccp extends Model
{
    protected $table = 'bmccp';
    
    protected $fillable = [
        'receipt_image',
        'diamond_pack_id',
        'notes',
        'status',
        'invoice_number',
    ];

    /**
     * Get the diamond pack for this bmccp.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Get the orders for this bmccp.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
