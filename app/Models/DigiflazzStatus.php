<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigiflazzStatus extends Model
{
    protected $table = 'digiflazz_statuses';

    protected $fillable = [
        'order_id', 'diamond_pack_id', 'order_item_id', 'ref_id', 'trxid', 'buyer_sku_code', 'customer_no', 'rc', 'status', 'message', 'price', 'sn', 'additional_data', 'event'
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    /**
     * Deliveries the provider confirmed as fulfilled.
     */
    public function scopeSuccessful($query)
    {
        return $query->where(function ($inner) {
            $inner->whereRaw("LOWER(digiflazz_statuses.status) = 'sukses'")
                ->orWhere('digiflazz_statuses.rc', '00');
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function diamondPack()
    {
        return $this->belongsTo(DiamondPack::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
