<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only change column default and leave existing data untouched
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('website_enabled')->default(false)->change();
            $table->boolean('flexy_enabled')->default(false)->change();
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('website_enabled')->default(true)->change();
            $table->boolean('flexy_enabled')->default(true)->change();
        });
    }
};
