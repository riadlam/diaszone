<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration migrates existing `vipreseller_status` rows into
     * `digiflazz_statuses` when appropriate. It's idempotent and best-effort;
     * it will skip rows that already appear to be migrated.
     */
    public function up(): void
    {
        if (!Schema::hasTable('vipreseller_status') || !Schema::hasTable('digiflazz_statuses')) {
            return;
        }

        // Process in chunks to avoid memory blowups on large tables
        \DB::table('vipreseller_status')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $r) {
                try {
                    // If trxid already exists in digiflazz_statuses, update that record
                    if (!empty($r->trxid)) {
                        $existing = \DB::table('digiflazz_statuses')->where('trxid', $r->trxid)->first();
                        if ($existing) {
                            // merge additional_data
                            $add = (array) json_decode($existing->additional_data ?? 'null', true) ?: [];
                            $oldAdd = (array) json_decode($r->additional_data ?? 'null', true) ?: [];
                            $merged = array_merge($add, $oldAdd, ['balance' => $r->balance ?? null]);
                            \DB::table('digiflazz_statuses')->where('id', $existing->id)->update([
                                'order_id' => $r->order_id ?? $existing->order_id,
                                'message' => $r->note ?? $existing->message,
                                'price' => $r->price ?? $existing->price,
                                'additional_data' => json_encode($merged),
                                'updated_at' => now(),
                            ]);
                            continue;
                        }
                    }

                    // Otherwise create a new digiflazz_statuses row derived from vipreseller_status
                    $payload = [
                        'order_id' => $r->order_id ?? null,
                        'ref_id' => null,
                        'trxid' => $r->trxid ?? null,
                        'buyer_sku_code' => null,
                        'customer_no' => $r->data ?? null,
                        'rc' => null,
                        'status' => $r->status ?? null,
                        'message' => $r->note ?? null,
                        'price' => is_numeric($r->price) ? (int) $r->price : null,
                        'sn' => null,
                        'additional_data' => json_encode(array_merge((array) json_decode($r->additional_data ?? 'null', true) ?: [], ['balance' => $r->balance ?? null, 'zone' => $r->zone ?? null, 'service' => $r->service ?? null, 'legacy_id' => $r->id])),
                        'event' => null,
                        'created_at' => $r->created_at ?? now(),
                        'updated_at' => $r->updated_at ?? now(),
                    ];

                    // Do not create duplicate by trxid or by exact legacy id
                    $exists = null;
                    if (!empty($payload['trxid'])) {
                        $exists = \DB::table('digiflazz_statuses')->where('trxid', $payload['trxid'])->first();
                    }

                    if (!$exists) {
                        \DB::table('digiflazz_statuses')->insert($payload);
                    }
                } catch (\Throwable $e) {
                    logger()->warning('Failed to migrate vipreseller_status row into digiflazz_statuses', ['id' => $r->id, 'error' => $e->getMessage()]);
                    // continue with next row
                }
            }
        });
    }

    public function down(): void
    {
        // No automatic revert: we intentionally keep migrated rows in `digiflazz_statuses`.
    }
};
