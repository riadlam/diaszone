<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLike extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
        'session_id',
        'ip_address',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the review that owns this like/dislike.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user that made this like/dislike (if authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
