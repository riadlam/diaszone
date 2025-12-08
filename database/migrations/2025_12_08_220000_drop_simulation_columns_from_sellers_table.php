<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            if (Schema::hasColumn('sellers', 'is_flexy')) {
                $table->dropColumn('is_flexy');
            }
            if (Schema::hasColumn('sellers', 'is_website')) {
                $table->dropColumn('is_website');
            }
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('is_flexy')->nullable()->after('flexy_enabled')->default(null);
            $table->boolean('is_website')->nullable()->after('is_flexy')->default(null);
        });
    }
};
