<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiveawayEntry extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'giveaway_id',
        'user_id',
        'referral_code',
        'screenshot_path',
        'liked_followed_ack',
        'referral_satisfied_at',
        'status',
        'decline_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'liked_followed_ack' => 'boolean',
        'referral_satisfied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function giveaway(): BelongsTo
    {
        return $this->belongsTo(Giveaway::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(GiveawayReferral::class, 'referrer_entry_id');
    }

    public function referralSatisfied(): bool
    {
        return $this->referral_satisfied_at !== null;
    }

    public function referralUrl(): string
    {
        return url('/giveaway/'.$this->giveaway->slug.'?ref='.$this->referral_code);
    }

    public static function generateUniqueReferralCode(): string
    {
        for ($i = 0; $i < 12; $i++) {
            $code = Str::lower(Str::random(10));
            if (! static::query()->where('referral_code', $code)->exists()) {
                return $code;
            }
        }

        return Str::lower(Str::random(16));
    }

    public function displayFirstName(): string
    {
        $name = trim((string) ($this->user?->name ?? ''));
        if ($name === '') {
            return 'Player';
        }

        return (string) (preg_split('/\s+/', $name)[0] ?? $name);
    }
}
