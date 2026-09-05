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
        'vipreseller_pack_id',
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
     * Get the VIP Reseller pack for this item (streaming / VIP-fulfilled products).
     */
    public function vipResellerPack(): BelongsTo
    {
        return $this->belongsTo(VipResellerPack::class, 'vipreseller_pack_id');
    }

    public function isVipResellerItem(): bool
    {
        return $this->vipreseller_pack_id !== null;
    }

    /**
     * Get the Digiflazz statuses for this order item.
     */
    public function digiflazzStatuses(): HasMany
    {
        return $this->hasMany(DigiflazzStatus::class, 'order_item_id');
    }

    /**
     * Get the Item4Gamer orders for this order item.
     */
    public function item4gamerOrders(): HasMany
    {
        return $this->hasMany(Item4GamerOrder::class, 'order_item_id');
    }

    /**
     * Get count of successful top-ups for this item (Digiflazz).
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
     * Check if Item4Gamer order for this item is completed.
     */
    public function isItem4GamerCompleted(): bool
    {
        $item4gamerOrder = $this->item4gamerOrders()->first();
        if (!$item4gamerOrder) {
            return false;
        }
        return in_array(strtolower($item4gamerOrder->status ?? ''), ['completed', 'success']);
    }

    /**
     * Check if all top-ups for this item are completed (Digiflazz).
     */
    public function isCompleted(): bool
    {
        return $this->successfulTopupsCount() >= $this->quantity;
    }
}
