<?php

namespace App\Models;

use App\Observers\DigiflazzStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([DigiflazzStatusObserver::class])]
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

    /**
     * In-flight or successful Digiflazz attempts that should block a duplicate place.
     * Failed/Gagal rows and empty "waiting" placeholders must not count.
     */
    public function scopeSubmittedOrPending($query)
    {
        return $query->where(function ($inner) {
            $inner->whereRaw("LOWER(COALESCE(digiflazz_statuses.status, '')) IN ('sukses', 'success', 'pending')")
                ->orWhereIn('digiflazz_statuses.rc', ['00', '03', '99']);
        });
    }

    /**
     * Still waiting on Digiflazz (not yet final success/failure).
     */
    public function scopePendingInFlight($query)
    {
        return $query->where(function ($inner) {
            $inner->whereRaw("LOWER(COALESCE(digiflazz_statuses.status, '')) = 'pending'")
                ->orWhereIn('digiflazz_statuses.rc', ['03', '99']);
        });
    }

    /**
     * Digiflazz returned a final failure (Gagal / hard RC). Pending/success are excluded.
     */
    public static function isTerminalFailure(?string $status, ?string $rc): bool
    {
        $status = strtolower(trim((string) $status));
        $rc = trim((string) $rc);

        if (in_array($status, ['sukses', 'success', 'pending'], true) || in_array($rc, ['00', '03', '99'], true)) {
            return false;
        }

        if (in_array($status, ['gagal', 'error', 'failed'], true)) {
            return true;
        }

        // Common Digiflazz hard-fail RCs (wrong number, unstable product, generic gagal, etc.)
        return in_array($rc, [
            '02', '06', '07', '13', '14',
            '20', '21', '22', '23', '24', '25', '26',
            '50', '51', '52', '53', '54', '55', '56', '57', '58', '59',
            '60', '61', '62', '63', '64', '65', '66', '67', '68', '69',
            '70', '71', '72', '73', '74',
        ], true);
    }

    public function isTerminalFailureRecord(): bool
    {
        return static::isTerminalFailure($this->status, $this->rc !== null ? (string) $this->rc : null);
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
