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
            // Drop the amount column
            $table->dropColumn('amount');
            
            // Make user_id nullable
            // First, drop the foreign key constraint
            $table->dropForeign(['user_id']);
            // Then modify the column to be nullable
            $table->foreignId('user_id')->nullable()->change();
            // Re-add the foreign key constraint with nullable support
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Re-add the amount column
            $table->decimal('amount', 10, 2)->after('payment_method');
            
            // Make user_id not nullable again
            // First, drop the foreign key constraint
            $table->dropForeign(['user_id']);
            // Then modify the column to be not nullable
            $table->foreignId('user_id')->nullable(false)->change();
            // Re-add the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
