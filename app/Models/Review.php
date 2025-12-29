<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'name',
        'comment',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the game that owns this review.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the user that made this review (if authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes and dislikes for this review.
     */
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * Get only likes for this review.
     */
    public function likesOnly(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewLike::class)->where('type', 'like');
    }

    /**
     * Get only dislikes for this review.
     */
    public function dislikesOnly(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewLike::class)->where('type', 'dislike');
    }

    /**
     * Get the replies for this review.
     */
    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewReply::class)->latest();
    }

    /**
     * Get the count of likes.
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likesOnly()->count();
    }

    /**
     * Get the count of dislikes.
     */
    public function getDislikesCountAttribute(): int
    {
        return $this->dislikesOnly()->count();
    }

    /**
     * Get the count of replies.
     */
    public function getRepliesCountAttribute(): int
    {
        return $this->replies()->count();
    }
}

