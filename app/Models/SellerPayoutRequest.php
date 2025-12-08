<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayoutRequest extends Model
{
    protected $fillable = [
        'seller_id',
        'amount',
        'currency',
        'status',
        'seller_note',
        'admin_note',
        'transaction_id',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function transaction(): ?BelongsTo
    {
        return $this->belongsTo(SellerWalletTransaction::class, 'transaction_id');
    }
}
