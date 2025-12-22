<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'game_type',
        'name',
        'is_active',
        'is_topseller',
        'is_giftcard',
        'is_newproduct',
        'required_fields',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_topseller' => 'boolean',
        'is_giftcard' => 'boolean',
        'is_newproduct' => 'boolean',
        'required_fields' => 'array',
    ];

    /**
     * Get the diamond packs for this game.
     */
    public function diamondPacks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiamondPack::class);
    }

    /**
     * Get the reviews for this game.
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the content for this game.
     */
    public function content(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GameContent::class);
    }

    /**
     * Get the images for this game.
     */
    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GameImage::class)->orderBy('display_order');
    }
}

