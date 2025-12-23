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
            // Drop the payment_method column
            $table->dropColumn('payment_method');
            
            // Add flexy_id column with foreign key relationship to flexies table
            $table->foreignId('flexy_id')->nullable()->after('status')->constrained('flexies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the flexy_id foreign key and column
            $table->dropForeign(['flexy_id']);
            $table->dropColumn('flexy_id');
            
            // Re-add the payment_method column
            $table->string('payment_method')->nullable()->after('status');
        });
    }
};
