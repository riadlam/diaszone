<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('digiflazz_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('ref_id')->nullable()->index();
            $table->string('trxid')->nullable();
            $table->string('buyer_sku_code')->nullable();
            $table->string('customer_no')->nullable();
            $table->string('rc')->nullable();
            $table->string('status')->nullable();
            $table->text('message')->nullable();
            $table->bigInteger('price')->nullable();
            $table->string('sn')->nullable();
            $table->json('additional_data')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('digiflazz_statuses');
    }
};
