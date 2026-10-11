<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries MODIFY screenshot_path VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE giveaway_entries ALTER COLUMN screenshot_path DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite: recreate is heavy; tests use RefreshDatabase from create migration
            // which we also update below. No-op for existing sqlite if already nullable.
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries MODIFY screenshot_path VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE giveaway_entries ALTER COLUMN screenshot_path SET NOT NULL');
        }
    }
};
