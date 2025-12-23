<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'pending_confirmation' to the status enum (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation', 'sending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending_confirmation' from the enum (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'sending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending'");
        }
    }
};
