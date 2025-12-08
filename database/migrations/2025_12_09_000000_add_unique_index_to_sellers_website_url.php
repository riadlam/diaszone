<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Add unique index on website_url (nulls allowed) if it doesn't exist
            if (!Schema::hasColumn('sellers', 'website_url')) {
                return;
            }

            // Try to detect an existing index by querying information_schema (MySQL)
            try {
                $dbName = \Illuminate\Support\Facades\DB::getDatabaseName();
                $exists = \Illuminate\Support\Facades\DB::selectOne(
                    'SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                    [$dbName, 'sellers', 'website_url_unique']
                );

                if (!($exists && $exists->cnt > 0)) {
                    $table->unique('website_url', 'website_url_unique');
                }
            } catch (\Exception $e) {
                // If detection fails for any reason, attempt to create the index (DB will error if it already exists)
                $table->unique('website_url', 'website_url_unique');
            }
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            // drop index if exists
            try {
                $dbName = \Illuminate\Support\Facades\DB::getDatabaseName();
                $exists = \Illuminate\Support\Facades\DB::selectOne(
                    'SELECT COUNT(1) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                    [$dbName, 'sellers', 'website_url_unique']
                );

                if ($exists && $exists->cnt > 0) {
                    $table->dropUnique('website_url_unique');
                }
            } catch (\Exception $e) {
                // best-effort — ignore failures
            }
        });
    }
};
