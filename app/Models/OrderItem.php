<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'diamond_pack_id',
        'quantity',
        'unit_price_dzd',
        'unit_price_usd',
        'discount_percentage',
        'subtotal_dzd',
        'discount_amount_dzd',
        'total_dzd',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_dzd' => 'decimal:2',
        'unit_price_usd' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'subtotal_dzd' => 'decimal:2',
        'discount_amount_dzd' => 'decimal:2',
        'total_dzd' => 'decimal:2',
    ];

    /**
     * Get the order that owns this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the diamond pack for this item.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Get the Digiflazz statuses for this order item.
     */
    public function digiflazzStatuses(): HasMany
    {
        return $this->hasMany(DigiflazzStatus::class, 'order_item_id');
    }

    /**
     * Get count of successful top-ups for this item.
     */
    public function successfulTopupsCount(): int
    {
        return $this->digiflazzStatuses()
            ->where(function ($q) {
                $q->whereRaw("LOWER(status) = 'sukses'")
                  ->orWhere('rc', '00');
            })->count();
    }

    /**
     * Check if all top-ups for this item are completed.
     */
    public function isCompleted(): bool
    {
        return $this->successfulTopupsCount() >= $this->quantity;
    }
}
