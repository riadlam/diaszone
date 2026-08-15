<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WheelReward extends Model
{
    protected $fillable = [
        'wheel_event_id',
        'label',
        'draws_required',
        'reward_type',
        'diamond_pack_id',
        'discount_percentage',
        'image_paths',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'draws_required' => 'integer',
        'discount_percentage' => 'decimal:2',
        'image_paths' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Public URLs for every uploaded image. The first one is the wheel icon,
     * the rest only show up in the slice preview gallery.
     *
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->image_paths ?? [])
            ->filter()
            ->map(fn (string $path): string => url('/storage/'.ltrim($path, '/')))
            ->values()
            ->all();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WheelEvent::class, 'wheel_event_id');
    }

    public function diamondPack(): BelongsTo
    {
        return $this->belongsTo(DiamondPack::class);
    }

    public function eligiblePacks(): BelongsToMany
    {
        return $this->belongsToMany(
            DiamondPack::class,
            'wheel_reward_eligible_packs',
            'wheel_reward_id',
            'diamond_pack_id'
        )->withTimestamps();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WheelClaim::class);
    }

    public function isPackReward(): bool
    {
        return $this->reward_type === 'diamond_pack';
    }

    public function isDiscountReward(): bool
    {
        return $this->reward_type === 'discount';
    }
}
