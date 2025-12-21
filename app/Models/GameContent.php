<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameContent extends Model
{
    protected $fillable = [
        'game_id',
        'currency_name',
        'about_text',
        'instructions_text',
        'id_format',
        'how_to_topup',
    ];

    /**
     * Get the game that owns this content.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
