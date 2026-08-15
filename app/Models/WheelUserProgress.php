<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WheelUserProgress extends Model
{
    protected $table = 'wheel_user_progress';

    protected $fillable = [
        'user_id',
        'game_type',
        'current_reward_id',
        'draws_toward_current',
        'total_spins_earned',
        'total_spins_used',
        'total_rewards_unlocked',
        'version',
    ];

    protected $casts = [
        'draws_toward_current' => 'integer',
        'total_spins_earned' => 'integer',
        'total_spins_used' => 'integer',
        'total_rewards_unlocked' => 'integer',
        'version' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentReward(): BelongsTo
    {
        return $this->belongsTo(WheelReward::class, 'current_reward_id');
    }

    public function availableSpins(): int
    {
        return max(0, $this->total_spins_earned - $this->total_spins_used);
    }
}
