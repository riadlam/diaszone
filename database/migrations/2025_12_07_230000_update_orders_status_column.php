<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Change status from enum to string to allow new statuses like 'pending_flexy_verification'
            $table->string('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't easily revert to enum with data validation, so we leave it as string or revert to enum if we were sure of data
        Schema::table('orders', function (Blueprint $table) {
            // Reverting to enum might lose data if we have invalid statuses, so generally we might skip or try:
            // $table->enum('status', ['pending', 'sending', 'completed', 'refunded'])->change();
        });
    }
};
