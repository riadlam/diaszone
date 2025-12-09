<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('store_logo_thumb')->nullable()->after('store_logo');
            $table->string('store_banner_resized')->nullable()->after('store_banner');
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('store_logo_thumb');
            $table->dropColumn('store_banner_resized');
        });
    }
};
