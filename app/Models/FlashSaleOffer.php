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

    /**
     * Active offers shown on the storefront.
     * Start/end dates are ignored — only the Active toggle controls visibility.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isLive(): bool
    {
        return (bool) $this->is_active;
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
        if (! $this->image_path) {
            return null;
        }

        // Always go through /media — never /storage (blocked with 403 on production).
        return PublicMedia::url((string) $this->image_path);
    }

    public function gameLabel(): string
    {
        return GameProvider::label($this->game_type);
    }

    public function statusLabel(): string
    {
        return $this->is_active ? 'live' : 'inactive';
    }
}
