<?php

namespace App\Models;

use App\Support\GameProvider;
use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSaleOffer extends Model
{
    protected $fillable = [
        'name',
        'game_type',
        'image_path',
        'original_price_dzd',
        'sale_price_dzd',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'original_price_dzd' => 'decimal:2',
        'sale_price_dzd' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FlashSaleOfferItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeLive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now);
    }

    public function isLive(): bool
    {
        return $this->is_active
            && $this->starts_at <= now()
            && $this->ends_at > now();
    }

    public function discountPercent(): int
    {
        $original = (float) $this->original_price_dzd;
        $sale = (float) $this->sale_price_dzd;

        if ($original <= 0 || $sale >= $original) {
            return 0;
        }

        return (int) round((($original - $sale) / $original) * 100);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? PublicMedia::url($this->image_path)
            : null;
    }

    public function gameLabel(): string
    {
        return GameProvider::label($this->game_type);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        $now = now();

        if ($this->starts_at > $now) {
            return 'upcoming';
        }

        if ($this->ends_at <= $now) {
            return 'ended';
        }

        return 'live';
    }
}
