<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigiflazzStatus extends Model
{
    protected $table = 'digiflazz_statuses';

    protected $fillable = [
        'order_id', 'ref_id', 'trxid', 'buyer_sku_code', 'customer_no', 'rc', 'status', 'message', 'price', 'sn', 'additional_data', 'event'
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
