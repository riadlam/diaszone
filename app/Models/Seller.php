<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'seller';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'store_name',
        'store_description',
        'store_logo',
        'wallet_balance',
        'total_earnings',
        'total_sales',
        'website_enabled',
        'website_url',
        'flexy_enabled',
        // legacy simulation columns removed from model — use flexy_enabled and website_enabled
        'flexy_number',
        'flexy_instruction',
        'status',
        'allowed_games',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Model casts — use a property so Eloquent's getCasts() works as expected
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'wallet_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'website_enabled' => 'boolean',
        'flexy_enabled' => 'boolean',
        // legacy is_flexy/is_website removed
        'flexy_number' => 'string',
        'flexy_instruction' => 'string',
        'allowed_games' => 'array',
    ];

    /**
     * Get seller's custom game prices
     */
    public function gamePrices(): HasMany
    {
        return $this->hasMany(SellerGamePrice::class);
    }

    /**
     * Get seller's orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get seller's wallet transactions
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(SellerWalletTransaction::class);
    }

    /**
     * Get seller payout requests
     */
    public function payoutRequests(): HasMany
    {
        return $this->hasMany(SellerPayoutRequest::class);
    }

    /**
     * Get seller wallet recharge asks (requests)
     */
    public function walletRechargeAsks(): HasMany
    {
        return $this->hasMany(WalletRechargeAsk::class);
    }

    /**
     * Check if seller is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if seller can sell a specific game
     */
    public function canSellGame(string $gameType): bool
    {
        // If allowed_games is null or empty, seller can sell all games
        if (empty($this->allowed_games)) {
            return true;
        }
        return in_array($gameType, $this->allowed_games);
    }

    /**
     * Get seller's custom price for a pack
     */
    public function getCustomPrice(int $diamondPackId): ?SellerGamePrice
    {
        return $this->gamePrices()->where('diamond_pack_id', $diamondPackId)->first();
    }

    /**
     * Deduct from wallet and create transaction
     * Returns created SellerWalletTransaction on success, or false on failure
     */
    public function deductWallet(float $amount, string $description, ?int $referenceId = null, ?string $referenceType = null)
    {
        if ($this->wallet_balance < $amount) {
            return false;
        }

        $balanceBefore = $this->wallet_balance;
        $this->wallet_balance -= $amount;
        $this->save();

        $tx = $this->walletTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->wallet_balance,
            'description' => $description,
            'reference_type' => $referenceType ?: ($referenceId ? 'order' : null),
            'reference_id' => $referenceId,
        ]);

        return $tx;
    }

    /**
     * Credit wallet and create transaction
     */
    public function creditWallet(float $amount, string $description, ?int $adminId = null, ?int $referenceId = null, string $referenceType = 'admin_topup'): void
    {
        $balanceBefore = $this->wallet_balance;
        $this->wallet_balance += $amount;
        $this->save();

        $this->walletTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->wallet_balance,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * Add to total earnings
     */
    public function addEarnings(float $profit, float $saleAmount): void
    {
        $this->total_earnings += $profit;
        $this->total_sales += $saleAmount;
        $this->save();
    }

    /**
     * Get the store URL
     */
    public function getStoreUrl(string $gameType = null): string
    {
        // Use the configured slug (website_url) if present, otherwise the username
        $slug = $this->website_url ?: $this->username;
        $url = url('/store/' . $slug);
        if ($gameType) {
            $url .= '/' . $gameType;
        }
        return $url;
    }
}
