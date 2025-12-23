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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2); // e.g., 10 for 10% or 500 for 500 DZD
            $table->enum('applies_to', ['all', 'specific'])->default('all');
            $table->json('allowed_packages')->nullable(); // Array of package IDs
            $table->json('allowed_games')->nullable(); // Array of game codes: ['mlbb', 'freefire']
            $table->integer('max_uses')->nullable(); // Total uses allowed, null = unlimited
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('used_count')->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable(); // Minimum order to apply
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable(); // Admin/Telegram user who created
            $table->text('description')->nullable(); // Internal notes
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
