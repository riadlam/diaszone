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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); // Used for URL: /username/game
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('store_name')->nullable(); // Custom store name
            $table->text('store_description')->nullable();
            $table->string('store_logo')->nullable(); // Path to logo
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->decimal('total_earnings', 12, 2)->default(0.00); // Total profit earned
            $table->decimal('total_sales', 12, 2)->default(0.00); // Total sales amount
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->json('allowed_games')->nullable(); // Games seller can sell, null = all
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
