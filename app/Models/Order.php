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
        'tlg_message_id',
        // Coupon fields
        'coupon_id',
        'discount_amount',
        'original_price',
        'final_price',
        // Seller fields
        'seller_id',
        'wallet_deducted',
        'seller_cost',
        'seller_profit',
        'seller_profit_paid',
        'seller_profit_paid_at',
        'is_direct_topup',
        'quantity',
        // Payment method and Flexy fields
        'payment_method',
        'flexy_receipt',
        'flexy_description',
    ];

    protected $casts = [
        'seller_profit_paid' => 'boolean',
        'seller_profit_paid_at' => 'datetime',
        'quantity' => 'integer',
    ];

    public function successfulDigiflazzTopupsCount(): int
    {
        return $this->digiflazzStatuses()
            ->where(function ($q) {
                $q->whereRaw("LOWER(status) = 'sukses'")
                  ->orWhere('rc', '00');
            })->count();
    }

    /**
     * Credit seller wallet with the order profit (idempotent)
     * Returns true if credited, false if already paid or not applicable
     */
    public function creditSellerProfit(): bool
    {
        // no seller or no profit
        if (!$this->seller_id || !$this->seller_profit || (float) $this->seller_profit <= 0) {
            return false;
        }

        // already paid
        if ($this->seller_profit_paid) {
            return false;
        }

        $seller = $this->seller;
        if (!$seller) return false;

        try {
            \DB::beginTransaction();

            // Credit seller wallet
            $seller->creditWallet((float) $this->seller_profit, "Profit for order #{$this->order_number}", null, $this->id, 'order_profit');

            // mark order as paid
            $this->seller_profit_paid = true;
            $this->seller_profit_paid_at = now();
            $this->save();

            \DB::commit();
            \Log::info('Order profit credited to seller wallet', ['order_id' => $this->id, 'seller_id' => $seller->id, 'amount' => $this->seller_profit]);
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to credit order profit to seller wallet', ['order_id' => $this->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get the coupon applied to this order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the seller for this order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

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
     * Get the provider statuses for this order.
     */
    public function vipResellerStatuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VipResellerStatus::class);
    }

    public function digiflazzStatuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DigiflazzStatus::class);
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
