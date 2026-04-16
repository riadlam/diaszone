<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SofizPayCibTransaction extends Model
{
    protected $table = 'sofizpay_cib_transactions';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'cib_order_number',
        'cib_order_id',
        'amount_expected',
        'status',
        'create_response',
        'last_check_response',
        'paid_at',
    ];

    protected $casts = [
        'amount_expected' => 'decimal:2',
        'create_response' => 'array',
        'last_check_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
