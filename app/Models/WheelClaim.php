<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WheelClaim extends Model
{
    protected $fillable = [
        'user_id',
        'wheel_event_id',
        'wheel_reward_id',
        'occurrence',
        'reward_type',
        'claim_code',
        'coupon_id',
        'status',
        'unlocked_at',
        'fulfilled_at',
        'used_at',
        'admin_notes',
        'idempotency_key',
    ];

    protected $casts = [
        'occurrence' => 'integer',
        'unlocked_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WheelEvent::class, 'wheel_event_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(WheelReward::class, 'wheel_reward_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public static function generateClaimCode(): string
    {
        do {
            $code = 'DZ-WHEEL-'.strtoupper(Str::random(10));
        } while (static::where('claim_code', $code)->exists());

        return $code;
    }
}
