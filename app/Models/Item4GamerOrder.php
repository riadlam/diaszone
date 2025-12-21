<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item4GamerOrder extends Model
{
    protected $table = 'item4gamer_orders';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'diamond_pack_id',
        'item4gamer_order_id',
        'status',
        'quantity',
        'total',
        'currency',
        'player_id',
        'additional_data',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total' => 'decimal:2',
        'additional_data' => 'array',
    ];

    /**
     * Get the order that owns this Item4Gamer order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item that owns this Item4Gamer order.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the diamond pack for this order.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }
}

