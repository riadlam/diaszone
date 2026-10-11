<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
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

        return storage_public_url('images_giveaway/gift-hero.svg');
    }

    public static function assetUrl(string $filename): string
    {
        return storage_public_url('images_giveaway/'.$filename);
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
