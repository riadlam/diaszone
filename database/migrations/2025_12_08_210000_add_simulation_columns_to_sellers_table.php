<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Simulation columns for toggling flows and storing Flexy details
            if (!Schema::hasColumn('sellers', 'is_flexy')) {
                $table->boolean('is_flexy')->nullable()->after('flexy_enabled')->default(null);
            }
            if (!Schema::hasColumn('sellers', 'is_website')) {
                $table->boolean('is_website')->nullable()->after('is_flexy')->default(null);
            }
            if (!Schema::hasColumn('sellers', 'flexy_number')) {
                $table->string('flexy_number')->nullable()->after('is_website');
            }
            if (!Schema::hasColumn('sellers', 'flexy_instruction')) {
                $table->text('flexy_instruction')->nullable()->after('flexy_number');
            }
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['is_flexy', 'is_website', 'flexy_number', 'flexy_instruction']);
        });
    }
};
