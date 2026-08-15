<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_events', function (Blueprint $table) {
            $table->string('background_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_events', function (Blueprint $table) {
            $table->dropColumn('background_path');
        });
    }
};
