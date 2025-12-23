<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerWalletTransaction extends Model
{
    protected $fillable = [
        'seller_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'admin_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the seller for this transaction
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the admin who made this transaction (for credits)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the related order if exists
     */
    public function order(): ?BelongsTo
    {
        if ($this->reference_type === 'order') {
            return $this->belongsTo(Order::class, 'reference_id');
        }
        return null;
    }

    /**
     * Scope for credits only
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debits only
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }
}
