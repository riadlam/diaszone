<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wheel_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game_type', 64)->default('mobilelegends');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['game_type', 'is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('wheel_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wheel_event_id')->constrained('wheel_events')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('draws_required');
            $table->enum('reward_type', ['diamond_pack', 'discount']);
            $table->foreignId('diamond_pack_id')->nullable()->constrained('diamond_packs')->nullOnDelete();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['wheel_event_id', 'sort_order']);
        });

        Schema::create('wheel_reward_eligible_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wheel_reward_id')->constrained('wheel_rewards')->cascadeOnDelete();
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['wheel_reward_id', 'diamond_pack_id'], 'wheel_reward_pack_unique');
        });

        Schema::create('wheel_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('game_type', 64)->default('mobilelegends');
            $table->foreignId('current_reward_id')->nullable()->constrained('wheel_rewards')->nullOnDelete();
            $table->unsignedInteger('draws_toward_current')->default(0);
            $table->unsignedInteger('total_spins_earned')->default(0);
            $table->unsignedInteger('total_spins_used')->default(0);
            $table->unsignedInteger('total_rewards_unlocked')->default(0);
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'game_type']);
        });

        Schema::create('wheel_spin_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('game_type', 64)->default('mobilelegends');
            $table->foreignId('wheel_event_id')->nullable()->constrained('wheel_events')->nullOnDelete();
            $table->enum('entry_type', ['credit', 'debit']);
            $table->integer('amount');
            $table->string('source_type', 64);
            $table->string('source_key', 191);
            $table->foreignId('digiflazz_status_id')->nullable()->constrained('digiflazz_statuses')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_key'], 'wheel_spin_source_unique');
            $table->index(['user_id', 'game_type']);
            $table->index(['wheel_event_id', 'user_id']);
        });

        Schema::create('wheel_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wheel_event_id')->constrained('wheel_events')->cascadeOnDelete();
            $table->foreignId('wheel_reward_id')->constrained('wheel_rewards')->cascadeOnDelete();
            $table->unsignedInteger('occurrence')->default(1);
            $table->enum('reward_type', ['diamond_pack', 'discount']);
            $table->string('claim_code', 64)->nullable()->unique();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->enum('status', ['unlocked', 'contacted', 'fulfilled', 'used', 'failed'])->default('unlocked');
            $table->dateTime('unlocked_at')->nullable();
            $table->dateTime('fulfilled_at')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('idempotency_key', 191)->unique();
            $table->timestamps();

            $table->unique(['user_id', 'wheel_reward_id', 'occurrence'], 'wheel_claim_occurrence_unique');
            $table->index(['wheel_event_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_claims');
        Schema::dropIfExists('wheel_spin_ledger');
        Schema::dropIfExists('wheel_user_progress');
        Schema::dropIfExists('wheel_reward_eligible_packs');
        Schema::dropIfExists('wheel_rewards');
        Schema::dropIfExists('wheel_events');
    }
};
