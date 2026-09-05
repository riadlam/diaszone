<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VipResellerPack extends Model
{
    protected $table = 'vipreseller_packs';

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'product_url',
        'image_path',
        'price_basic',
        'price_premium',
        'price_special',
        'price_dzd',
        'base_price_dzd',
        'price_usd',
        'discount_percentage',
        'server',
        'stock',
        'provider_status',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'price_basic' => 'decimal:2',
        'price_premium' => 'decimal:2',
        'price_special' => 'decimal:2',
        'price_dzd' => 'decimal:2',
        'base_price_dzd' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VipResellerCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'vipreseller_pack_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'vipreseller_pack_id');
    }

    public function usesVipReseller(): bool
    {
        return true;
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path) {
            return PublicMedia::url((string) $this->image_path);
        }

        return $this->category?->imageUrl();
    }

    public function href(): string
    {
        $url = trim((string) $this->product_url);
        if ($url !== '') {
            return $url;
        }

        return $this->category?->href() ?? '#';
    }

    /**
     * Margin between sell price (DZD) and base cost (DZD), when base is set.
     */
    public function profitDzd(): ?float
    {
        if ($this->base_price_dzd === null) {
            return null;
        }

        return (float) $this->price_dzd - (float) $this->base_price_dzd;
    }
}
