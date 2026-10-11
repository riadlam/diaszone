<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GiveawayEntry extends Model
{
    public const STATUS_DRAFT = 'draft';

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
        'tlg_message_id',
    ];

    protected $casts = [
        'liked_followed_ack' => 'boolean',
        'referral_satisfied_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'tlg_message_id' => 'integer',
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

    public function hasScreenshot(): bool
    {
        return filled($this->screenshot_path);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_DECLINED], true);
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_DECLINED], true);
    }

    public function canSubmitForApproval(): bool
    {
        return $this->isEditable()
            && $this->hasScreenshot()
            && $this->referralSatisfied();
    }

    public function screenshotPublicUrl(): ?string
    {
        if (! $this->hasScreenshot()) {
            return null;
        }

        return route('giveaway.screenshot', [
            'slug' => $this->giveaway->slug,
        ]);
    }

    public function deleteScreenshotFile(): void
    {
        if ($this->screenshot_path && Storage::disk('public')->exists($this->screenshot_path)) {
            Storage::disk('public')->delete($this->screenshot_path);
        }
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
