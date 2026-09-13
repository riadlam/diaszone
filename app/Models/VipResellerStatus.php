<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class VipResellerStatus extends Model
{
    // legacy: model name retained for compatibility; underlying table points
    // to `digiflazz_statuses` (mapped below) so existing code keeps working

    protected $fillable = [
        'order_id',
        'order_item_id',
        'diamond_pack_id',
        'buyer_sku_code',
        'trxid',
        'ref_id',
        'data',
        'zone',
        'status',
        'balance',
        'note',
        'price',
        'sn',
        'additional_data',
        'event',
        'customer_no',
        'message',
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

    /**
     * Insert or update the status for a provider transaction id.
     * A webhook and the place call can both try to store the same trxid.
     */
    public static function upsertByTrxid(array $data): self
    {
        $trxid = $data['trxid'] ?? null;
        if ($trxid === null || $trxid === '') {
            return static::create($data);
        }

        try {
            return static::writeByTrxid((string) $trxid, $data);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }

            return static::writeByTrxid((string) $trxid, $data);
        }
    }

    protected static function writeByTrxid(string $trxid, array $data): self
    {
        $existing = static::query()->where('trxid', $trxid)->first();
        if (! $existing) {
            return static::create($data);
        }

        $payload = [];
        foreach ($data as $key => $value) {
            if ($value === null || $key === 'trxid') {
                continue;
            }
            $payload[$key] = $value;
        }

        $existing->fill($payload);
        $existing->save();

        return $existing;
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
