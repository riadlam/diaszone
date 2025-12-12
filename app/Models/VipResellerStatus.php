<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VipResellerStatus extends Model
{
    // legacy: model name retained for compatibility; underlying table points
    // to `digiflazz_statuses` (mapped below) so existing code keeps working

    protected $fillable = [
        'order_id',
        'trxid',
        'data',
        'zone',
        'status',
        'balance',
        'note',
        'price',
        'additional_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'additional_data' => 'array', // Automatically cast JSON to array
    ];

    // For backward compatibility we will use `digiflazz_statuses` as the
    // underlying storage so existing code paths that reference
    // `VipResellerStatus` continue to work while we migrate off the
    // legacy `vipreseller_status` table. Accessors below map commonly used
    // fields onto the `digiflazz_statuses` structure.
    protected $table = 'digiflazz_statuses';

    /**
     * Get the order that owns this provider status
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Accessors to provide compatibility with legacy field names
    public function getDataAttribute()
    {
        return $this->customer_no ?? ($this->attributes['data'] ?? null);
    }

    public function setDataAttribute($value)
    {
        // map legacy data -> customer_no on digiflazz_statuses
        $this->attributes['customer_no'] = $value;
    }

    public function getZoneAttribute()
    {
        return $this->additional_data['zone'] ?? ($this->attributes['zone'] ?? null);
    }

    public function setZoneAttribute($value)
    {
        $ad = $this->additional_data ?? [];
        $ad['zone'] = $value;
        $this->additional_data = $ad;
    }

    public function getBalanceAttribute()
    {
        return $this->additional_data['balance'] ?? ($this->attributes['balance'] ?? null);
    }

    public function setBalanceAttribute($value)
    {
        $ad = $this->additional_data ?? [];
        $ad['balance'] = $value;
        $this->additional_data = $ad;
    }

    public function getNoteAttribute()
    {
        return $this->message ?? ($this->attributes['note'] ?? null);
    }

    public function setNoteAttribute($value)
    {
        $this->attributes['message'] = $value;
    }

    /**
     * Legacy `service` compatibility mapped into `additional_data` JSON
     */
    public function getServiceAttribute()
    {
        return $this->additional_data['service'] ?? ($this->attributes['service'] ?? null);
    }

    public function setServiceAttribute($value)
    {
        $ad = $this->additional_data ?? [];
        $ad['service'] = $value;
        $this->additional_data = $ad;
    }

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
