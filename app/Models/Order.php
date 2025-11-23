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
        'bmccp_id',
        'chargily_status_id',
        'cryptopay_id',
        'nowpayments_payment_id',
        'user_id_ml',
        'zone_id_ml',
        'player_id_ff',
        'player_id_pubg',
        'player_id_hok',
        'user_id_bs',
        'server_bs',
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
     * Get the bmccp payment for this order.
     */
    public function bmccp(): BelongsTo
    {
        return $this->belongsTo(Bmccp::class);
    }

    /**
     * Get the cryptopay payment for this order.
     */
    public function cryptopay(): BelongsTo
    {
        return $this->belongsTo(Cryptopay::class);
    }

    /**
     * Get the Chargily status for this order.
     */
    public function chargilyStatus(): BelongsTo
    {
        return $this->belongsTo(ChargilyStatus::class, 'chargily_status_id');
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
