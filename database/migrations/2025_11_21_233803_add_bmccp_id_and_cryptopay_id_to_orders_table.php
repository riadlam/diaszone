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
            // Add bmccp_id column with foreign key relationship to bmccp table
            $table->foreignId('bmccp_id')->nullable()->after('flexy_id')->constrained('bmccp')->onDelete('set null');
            
            // Add cryptopay_id column with foreign key relationship to cryptopay table
            $table->foreignId('cryptopay_id')->nullable()->after('bmccp_id')->constrained('cryptopay')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign keys and columns
            $table->dropForeign(['bmccp_id']);
            $table->dropColumn('bmccp_id');
            
            $table->dropForeign(['cryptopay_id']);
            $table->dropColumn('cryptopay_id');
        });
    }
};
