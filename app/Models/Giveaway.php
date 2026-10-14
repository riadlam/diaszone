<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Giveaway extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'facebook_post_url',
        'facebook_page_url',
        'starts_at',
        'ends_at',
        'is_active',
        'instructions',
        'hero_image',
        'winner_entry_id',
        'winner_drawn_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'winner_drawn_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Giveaway $giveaway): void {
            if (blank($giveaway->slug)) {
                $giveaway->slug = static::uniqueSlugFromTitle((string) $giveaway->title);
            }
        });
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GiveawayEntry::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(GiveawayReferral::class);
    }

    public function winnerEntry(): BelongsTo
    {
        return $this->belongsTo(GiveawayEntry::class, 'winner_entry_id');
    }

    public function hasWinner(): bool
    {
        return $this->winner_entry_id !== null;
    }

    public static function isWheelAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $email = strtolower(trim((string) config('giveaway.wheel_admin_email', 'riadlaamari@gmail.com')));

        return strtolower(trim((string) $user->email)) === $email;
    }

    public static function drawLiveEnabled(): bool
    {
        return (bool) config('giveaway.draw_live', true);
    }

    public static function winnerRevealEnabled(): bool
    {
        return (bool) config('giveaway.winner_reveal_enabled', true);
    }

    public static function featuredSlug(): string
    {
        return trim((string) config('giveaway.featured_slug', 'weekly-pass'));
    }

    public static function featured(): ?self
    {
        $slug = static::featuredSlug();
        if ($slug === '') {
            return null;
        }

        return static::query()->where('slug', $slug)->first();
    }

    public static function resolveForDisplay(?string $slug = null): ?self
    {
        if (filled($slug)) {
            return static::query()->where('slug', $slug)->first();
        }

        return static::featured()
            ?? static::currentActive()
            ?? static::query()->orderByDesc('starts_at')->first();
    }

    public function scopeActiveWindow(Builder $query, $moment = null): Builder
    {
        $moment = $moment ?: now();

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $moment)
            ->where('ends_at', '>', $moment);
    }

    public static function currentActive($moment = null): ?self
    {
        return static::query()
            ->activeWindow($moment)
            ->orderByDesc('starts_at')
            ->first();
    }

    public function isCurrentlyActive($moment = null): bool
    {
        $moment = $moment ?: now();

        return $this->is_active
            && $this->starts_at <= $moment
            && $this->ends_at > $moment;
    }

    public function isOpenForAttribution($moment = null): bool
    {
        $moment = $moment ?: now();

        return $this->is_active && $this->ends_at > $moment;
    }

    public function heroImageUrl(): string
    {
        if ($this->hero_image) {
            return PublicMedia::url($this->hero_image);
        }

        return PublicMedia::url('images_giveaway/gift-hero.svg');
    }

    public static function assetUrl(string $filename): string
    {
        return PublicMedia::url('images_giveaway/'.$filename);
    }

    public static function uniqueSlugFromTitle(string $title): string
    {
        $base = Str::slug($title) ?: 'giveaway';
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
