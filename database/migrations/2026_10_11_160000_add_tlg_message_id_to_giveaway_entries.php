<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('tlg_message_id')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->dropColumn('tlg_message_id');
        });
    }
};
