<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiveawayReferral extends Model
{
    protected $fillable = [
        'giveaway_id',
        'referrer_entry_id',
        'referrer_user_id',
        'referred_user_id',
        'referral_code_used',
        'attributed_at',
    ];

    protected $casts = [
        'attributed_at' => 'datetime',
    ];

    public function giveaway(): BelongsTo
    {
        return $this->belongsTo(Giveaway::class);
    }

    public function referrerEntry(): BelongsTo
    {
        return $this->belongsTo(GiveawayEntry::class, 'referrer_entry_id');
    }

    public function referrerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
