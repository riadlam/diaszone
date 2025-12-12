<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds uniqueness constraints to avoid duplicate provider records and improve matching.
     *
     * NOTE FOR PRODUCTION: this migration performs a best-effort deduplication of
     * `vipreseller_status.trxid` values before adding a unique index. If you plan to
     * run this on a live MySQL instance, consider running these checks first and
     * taking a snapshot of the table:
     *
     *   -- find duplicate trxid values
     *   SELECT trxid, COUNT(*) AS cnt FROM vipreseller_status WHERE trxid IS NOT NULL GROUP BY trxid HAVING cnt > 1;
     *
     *   -- export duplicates for review (MySQL example)
     *   SELECT * FROM vipreseller_status WHERE trxid IN (SELECT trxid FROM (SELECT trxid FROM vipreseller_status WHERE trxid IS NOT NULL GROUP BY trxid HAVING COUNT(*) > 1) tmp);
     *
     * The migration will attempt to keep one row per duplicate trxid (preferring rows
     * with an assigned order_id) and nullify trxid in other rows. Still, it is
     * recommended to make a backup (dump) before running this migration in production.
     */
    public function up(): void
    {
        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            // ref_id should be unique per Digiflazz request
            if (!Schema::hasColumn('digiflazz_statuses', 'ref_id')) {
                return;
            }
            try {
                // MySQL may already have the index present. For MySQL check information_schema
                $driver = null;
                try {
                    $driver = \DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
                } catch (\Throwable $_) {
                    $driver = null;
                }

                $shouldAddRef = true;
                $shouldAddTrx = true;
                if ($driver === 'mysql') {
                    try {
                        $dbName = \DB::getDatabaseName();
                        $refExists = (bool) \DB::table('information_schema.statistics')
                            ->where('table_schema', $dbName)
                            ->where('table_name', 'digiflazz_statuses')
                            ->where('index_name', 'digiflazz_statuses_ref_id_unique')
                            ->exists();
                        $trxExists = (bool) \DB::table('information_schema.statistics')
                            ->where('table_schema', $dbName)
                            ->where('table_name', 'digiflazz_statuses')
                            ->where('index_name', 'digiflazz_statuses_trxid_unique')
                            ->exists();

                        $shouldAddRef = !$refExists;
                        $shouldAddTrx = !$trxExists;
                    } catch (\Throwable $_) {
                        // If querying information_schema fails, fall back to try/catch below
                        $shouldAddRef = true;
                        $shouldAddTrx = true;
                    }
                }

                if ($shouldAddRef) {
                    try {
                        $table->unique('ref_id', 'digiflazz_statuses_ref_id_unique');
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }

                if ($shouldAddTrx) {
                    try {
                        $table->unique('trxid', 'digiflazz_statuses_trxid_unique');
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }
            } catch (\Throwable $e) {
                // ignore if index creation check fails or unsupported in this driver
            }
        });

        Schema::table('vipreseller_status', function (Blueprint $table) {
            if (!Schema::hasColumn('vipreseller_status', 'trxid')) {
                return;
            }
            try {
                // Deduplicate existing duplicate trxid values so the unique index can be added.
                // We will keep one row per trxid (prefer one that already has order_id), and
                // nullify trxid on the other duplicates.
                try {
                    $duplicates = \DB::table('vipreseller_status')
                        ->whereNotNull('trxid')
                        ->select('trxid', \DB::raw('COUNT(*) as cnt'))
                        ->groupBy('trxid')
                        ->having('cnt', '>', 1)
                        ->pluck('trxid');

                    foreach ($duplicates as $dupTrx) {
                        $rows = \DB::table('vipreseller_status')->where('trxid', $dupTrx)->orderBy('created_at', 'desc')->get();

                        $keeper = null;
                        foreach ($rows as $r) {
                            if (!empty($r->order_id)) {
                                $keeper = $r;
                                break;
                            }
                        }

                        if (!$keeper && count($rows)) {
                            $keeper = $rows->first();
                        }

                        if ($keeper) {
                            $idsToClear = array_map(fn($r) => $r->id, array_filter($rows->toArray(), fn($r) => $r->id !== $keeper->id));
                            if (count($idsToClear)) {
                                \DB::table('vipreseller_status')->whereIn('id', $idsToClear)->update(['trxid' => null]);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Best-effort deduplication; log the issue but continue to attempt index creation.
                    logger()->warning('vipreseller_status deduplication failed in migration', ['error' => $e->getMessage()]);
                }

                $table->unique('trxid', 'vipreseller_status_trxid_unique');
            } catch (\Throwable $e) {
                // ignore if index already exists or unsupported in this driver
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            try {
                $table->dropUnique('digiflazz_statuses_ref_id_unique');
            } catch (\Throwable $_) {
                // ignore
            }
            try {
                $table->dropUnique('digiflazz_statuses_trxid_unique');
            } catch (\Throwable $_) {
                // ignore
            }
        });

        Schema::table('vipreseller_status', function (Blueprint $table) {
            try {
                $table->dropUnique('vipreseller_status_trxid_unique');
            } catch (\Throwable $_) {
                // ignore
            }
        });
    }
};
