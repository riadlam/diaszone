<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipResellerStatus extends Model
{
    protected $table = 'vipreseller_status';

    protected $fillable = [
        'trxid',
        'data',
        'zone',
        'service',
        'status',
        'note',
        'price',
        'additional_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'additional_data' => 'array', // Automatically cast JSON to array
    ];

    /**
     * Get the status badge color class
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'green',
            'error' => 'red',
            'waiting' => 'yellow',
            default => 'gray',
        };
    }
}
