<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WheelSpinLedger extends Model
{
    protected $table = 'wheel_spin_ledger';

    protected $fillable = [
        'user_id',
        'game_type',
        'wheel_event_id',
        'entry_type',
        'amount',
        'source_type',
        'source_key',
        'digiflazz_status_id',
        'order_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'integer',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WheelEvent::class, 'wheel_event_id');
    }

    public function digiflazzStatus(): BelongsTo
    {
        return $this->belongsTo(DigiflazzStatus::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
