<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'applies_to',
        'allowed_packages',
        'allowed_games',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'min_order_amount',
        'starts_at',
        'expires_at',
        'is_active',
        'created_by',
        'description',
    ];

    protected $casts = [
        'allowed_packages' => 'array',
        'allowed_games' => 'array',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Usages relationship
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is valid for use
     */
    public function isValid(): bool
    {
        // Check if active
        if (!$this->is_active) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check if not started yet
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // Check max uses
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedByUser(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check user's usage count
        $userUsageCount = $this->usages()->where('user_id', $userId)->count();
        
        return $userUsageCount < $this->max_uses_per_user;
    }

    /**
     * Check if coupon applies to a specific game and package
     */
    public function appliesToPackage(string $gameCode, int $packageId): bool
    {
        // If applies to all, return true
        if ($this->applies_to === 'all') {
            return true;
        }

        // Check if game is allowed
        if ($this->allowed_games !== null && !in_array($gameCode, $this->allowed_games)) {
            return false;
        }

        // Check if package is allowed
        if ($this->allowed_packages !== null && !in_array($packageId, $this->allowed_packages)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for a given amount
     */
    public function calculateDiscount(float $amount): array
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($amount * $this->discount_value) / 100;
            // Cap at 100%
            $discount = min($discount, $amount);
        } else {
            // Fixed amount discount
            $discount = min($this->discount_value, $amount);
        }

        $finalAmount = max(0, $amount - $discount);

        return [
            'original_amount' => $amount,
            'discount_amount' => round($discount, 2),
            'final_amount' => round($finalAmount, 2),
            'is_free' => $finalAmount == 0,
        ];
    }

    /**
     * Check if this is a 100% discount coupon
     */
    public function isFullDiscount(): bool
    {
        return $this->discount_type === 'percentage' && $this->discount_value >= 100;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Generate secure token for 100% discount processing
     */
    public function generateSecureToken(int $userId, int $orderId): string
    {
        $data = $this->id . '|' . $userId . '|' . $orderId . '|' . config('app.key');
        return hash('sha256', $data);
    }

    /**
     * Verify secure token for 100% discount processing
     */
    public function verifySecureToken(string $token, int $userId, int $orderId): bool
    {
        $expectedToken = $this->generateSecureToken($userId, $orderId);
        return hash_equals($expectedToken, $token);
    }

    /**
     * Find coupon by code (case-insensitive)
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper(trim($code)))->first();
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            });
    }

    /**
     * Auto uppercase code on save
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }
}
