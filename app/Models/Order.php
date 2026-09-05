<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'diamond_pack_id',
            'vipreseller_pack_id',
            'flash_sale_offer_id',
            'flash_sale_name',
            'status',
            'flexy_id',
            'bmccp_id',
            'chargily_status_id',
            'sofizpay_cib_transaction_id',
            'cryptopay_id',
            'nowpayments_payment_id',
            'user_id_ml',
            'zone_id_ml',
            'player_id_ff',
            'player_id_pubg',
            'player_id_hok',
            'user_id_bs',
            'server_bs',
            'save_id', // Generic user ID for new games (same as user_id)
            'server', // Generic server field for new games
            'customer_email',
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

    public function flashSaleOffer(): BelongsTo
    {
        return $this->belongsTo(FlashSaleOffer::class);
    }

    public function isFlashSale(): bool
    {
        return $this->flash_sale_offer_id !== null;
    }

    public function successfulDigiflazzTopupsCount(): int
    {
        return $this->digiflazzStatuses()
            ->where(function ($q) {
                $q->whereRaw("LOWER(status) = 'sukses'")
                  ->orWhere('rc', '00');
            })->count();
    }

    /**
     * Number of top-ups actually delivered to this customer before this order.
     * Counts provider deliveries, not order rows, so multi-quantity orders and
     * orders still marked as sending are represented correctly.
     */
    public function priorSuccessfulTopupsCount(): int
    {
        if (! $this->user_id) {
            return 0;
        }

        $earlierOrders = static::query()
            ->where('user_id', $this->user_id)
            ->whereKeyNot($this->getKey())
            ->when($this->created_at, fn ($query) => $query->where('created_at', '<=', $this->created_at))
            ->select('id');

        $digiflazz = DigiflazzStatus::query()
            ->whereIn('order_id', (clone $earlierOrders))
            ->where(function ($query) {
                $query->whereRaw("LOWER(digiflazz_statuses.status) = 'sukses'")
                    ->orWhere('digiflazz_statuses.rc', '00');
            })
            ->count();

        $item4gamer = Item4GamerOrder::query()
            ->whereIn('order_id', (clone $earlierOrders))
            ->whereIn(DB::raw('LOWER(status)'), ['completed', 'success'])
            ->sum('quantity');

        return (int) $digiflazz + (int) $item4gamer;
    }

    public function displayAmount(): float
    {
        if ($this->final_price !== null) {
            return (float) $this->final_price;
        }

        if ($this->relationLoaded('orderItems') && $this->orderItems->isNotEmpty()) {
            return (float) $this->orderItems->sum('total_dzd');
        }

        $price = (float) ($this->diamondPack?->price_dzd ?? 0);

        return $price * max(1, (int) ($this->quantity ?: 1));
    }

    public function gameLabel(): string
    {
        $gameType = $this->orderItems->first()?->diamondPack?->game_type
            ?? $this->diamondPack?->game_type;

        return match ($gameType) {
            'mobilelegends' => 'Mobile Legends',
            'freefire' => 'Free Fire',
            'pubgmobile', 'pubg_mobile' => 'PUBG Mobile',
            'honorofkings' => 'Honor of Kings',
            'bloodstrike' => 'Blood Strike',
            default => $gameType ? ucwords(str_replace('_', ' ', $gameType)) : 'Unknown game',
        };
    }

    public function playerIdentifier(): string
    {
        return match (true) {
            filled($this->user_id_ml) => $this->user_id_ml.' ('.$this->zone_id_ml.')',
            filled($this->player_id_ff) => $this->player_id_ff,
            filled($this->player_id_pubg) => $this->player_id_pubg,
            filled($this->player_id_hok) => $this->player_id_hok,
            filled($this->user_id_bs) => $this->user_id_bs.($this->server_bs ? ' · '.$this->server_bs : ''),
            filled($this->save_id) => $this->save_id.($this->server ? ' · '.$this->server : ''),
            default => '—',
        };
    }

    public function topupProgressLabel(): string
    {
        $required = $this->orderItems->isNotEmpty()
            ? (int) $this->orderItems->sum('quantity')
            : max(1, (int) ($this->quantity ?: 1));

        $completed = $this->digiflazzStatuses
            ->filter(fn ($status): bool => strtolower((string) $status->status) === 'sukses'
                || (string) $status->rc === '00')
            ->count();

        $completed += (int) $this->item4gamerOrders
            ->filter(fn ($provider): bool => in_array(strtolower((string) $provider->status), ['completed', 'success'], true))
            ->sum('quantity');

        if ($completed === 0 && $this->status === 'completed') {
            $completed = $required;
        }

        return min($completed, $required).'/'.$required;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'sending', 'processing' => 'info',
            'pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay',
            'pending_confirmation', 'pending_flexy_verification' => 'warning',
            'cancelled', 'failed' => 'danger',
            'refunded' => 'gray',
            default => 'gray',
        };
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
     * Get the VIP Reseller pack for this order (when fulfilled via VipReseller).
     */
    public function vipResellerPack(): BelongsTo
    {
        return $this->belongsTo(VipResellerPack::class, 'vipreseller_pack_id');
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

    public function sofizpayCibTransaction(): BelongsTo
    {
        return $this->belongsTo(SofizPayCibTransaction::class, 'sofizpay_cib_transaction_id');
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
     * Get the Item4Gamer orders for this order.
     */
    public function item4gamerOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Item4GamerOrder::class);
    }

    /**
     * Get the order items for this order.
     */
    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get total number of top-ups required across all items.
     */
    public function getTotalTopupsRequiredAttribute(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Get count of successful top-ups across all items.
     */
    public function getTotalSuccessfulTopupsAttribute(): int
    {
        return $this->digiflazzStatuses()
            ->where(function ($q) {
                $q->whereRaw("LOWER(status) = 'sukses'")
                  ->orWhere('rc', '00');
            })->count();
    }

    /**
     * Check if all top-ups for this order are completed.
     */
    public function areAllTopupsCompleted(): bool
    {
        $required = $this->total_topups_required;
        if ($required === 0) return false;
        
        $completed = $this->total_successful_topups;
        return $completed >= $required;
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
