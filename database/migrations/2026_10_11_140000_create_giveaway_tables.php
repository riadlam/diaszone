<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giveaways', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('facebook_post_url');
            $table->string('facebook_page_url');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(false);
            $table->text('instructions')->nullable();
            $table->string('hero_image')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'giveaways_active_window_index');
        });

        Schema::create('giveaway_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_id')->constrained('giveaways')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 32)->unique();
            $table->string('screenshot_path')->nullable();
            $table->boolean('liked_followed_ack')->default(false);
            $table->timestamp('referral_satisfied_at')->nullable();
            $table->string('status', 32)->default('draft');
            $table->text('decline_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['giveaway_id', 'user_id'], 'giveaway_entries_giveaway_user_unique');
            $table->index(['giveaway_id', 'status'], 'giveaway_entries_giveaway_status_index');
        });

        Schema::create('giveaway_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_id')->constrained('giveaways')->cascadeOnDelete();
            $table->foreignId('referrer_entry_id')->constrained('giveaway_entries')->cascadeOnDelete();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code_used', 32);
            $table->timestamp('attributed_at');
            $table->timestamps();

            $table->unique(['giveaway_id', 'referred_user_id'], 'giveaway_referrals_giveaway_referred_unique');
            $table->index('referrer_entry_id', 'giveaway_referrals_referrer_entry_index');
            $table->index('referral_code_used', 'giveaway_referrals_code_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giveaway_referrals');
        Schema::dropIfExists('giveaway_entries');
        Schema::dropIfExists('giveaways');
    }
};
