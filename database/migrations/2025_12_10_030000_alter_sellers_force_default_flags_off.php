<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes the column default value to false (0) for both website_enabled and flexy_enabled
     * so new sellers will have these features disabled by default at the DB level.
     */
    public function up()
    {
        // Use change() to alter default values; requires doctrine/dbal to be installed on the environment.
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('website_enabled')->default(false)->change();
            $table->boolean('flexy_enabled')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Reverts the default value back to true (previous default before change if needed).
     */
    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('website_enabled')->default(true)->change();
            $table->boolean('flexy_enabled')->default(true)->change();
        });
    }
};
