<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WheelEvent extends Model
{
    protected $fillable = [
        'name',
        'game_type',
        'starts_at',
        'ends_at',
        'is_active',
        'description',
        'background_path',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(WheelReward::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeRewards(): HasMany
    {
        return $this->rewards()->where('is_active', true);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WheelClaim::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function backgroundUrl(): ?string
    {
        return $this->background_path
            ? url('/storage/'.ltrim($this->background_path, '/'))
            : null;
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        return $this->starts_at <= $now && $this->ends_at > $now;
    }

    public function statusLabel(): string
    {
        $now = now();

        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at > $now) {
            return 'upcoming';
        }

        if ($this->ends_at <= $now) {
            return 'ended';
        }

        return 'active';
    }

    public function scopeForGame($query, string $gameType)
    {
        return $query->where('game_type', $gameType);
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now);
    }
}
