<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VipResellerCategory extends Model
{
    protected $table = 'vipreseller_categories';

    protected $fillable = [
        'slug',
        'name',
        'filter_game',
        'image_path',
        'product_url',
        'required_fields',
        'description',
        'is_active',
        'is_topseller',
        'is_newproduct',
        'sort_order',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'is_active' => 'boolean',
        'is_topseller' => 'boolean',
        'is_newproduct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function packs(): HasMany
    {
        return $this->hasMany(VipResellerPack::class, 'category_id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? PublicMedia::url((string) $this->image_path)
            : null;
    }

    public function href(): string
    {
        $url = trim((string) $this->product_url);

        return $url !== '' ? $url : '#';
    }
}
