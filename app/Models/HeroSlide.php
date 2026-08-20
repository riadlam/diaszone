<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? PublicMedia::url((string) $this->image_path)
            : null;
    }

    public function href(): string
    {
        $url = trim((string) $this->link_url);

        return $url !== '' ? $url : '#';
    }

    public function opensInNewTab(): bool
    {
        $url = $this->href();

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
