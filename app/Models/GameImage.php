<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameImage extends Model
{
    protected $fillable = [
        'game_id',
        'image_path',
        'image_type',
        'display_order',
        'alt_text',
        'title',
    ];

    /**
     * Get the game that owns this image.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
