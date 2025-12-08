<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('website_enabled')->default(true)->after('store_description');
            $table->string('website_url')->nullable()->after('website_enabled');
            $table->boolean('flexy_enabled')->default(true)->after('website_url');
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['website_enabled', 'website_url', 'flexy_enabled']);
        });
    }
};
