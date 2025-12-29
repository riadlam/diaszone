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
        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->string('ip_address')->nullable(); // For tracking guest users
            $table->enum('type', ['like', 'dislike'])->default('like');
            $table->timestamps();

            // Prevent duplicate likes/dislikes from same user/session/IP
            // Note: We'll handle uniqueness in application logic since we need either user_id OR session_id
            // A user can only like/dislike once per review
            $table->index('review_id');
            $table->index('user_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_likes');
    }
};
