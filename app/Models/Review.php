<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'game_id',
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
}

