<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'diamond_pack_id',
        'status',
        'flexy_id',
        'nowpayments_payment_id',
        'user_id_ml',
        'zone_id_ml',
        'notes',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the diamond pack for this order.
     */
    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    /**
     * Get the flexy payment for this order.
     */
    public function flexy(): BelongsTo
    {
        return $this->belongsTo(Flexy::class);
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
