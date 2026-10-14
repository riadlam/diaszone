<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giveaways', function (Blueprint $table) {
            $table->foreignId('winner_entry_id')
                ->nullable()
                ->after('hero_image')
                ->constrained('giveaway_entries')
                ->nullOnDelete();
            $table->timestamp('winner_drawn_at')->nullable()->after('winner_entry_id');
        });
    }

    public function down(): void
    {
        Schema::table('giveaways', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_entry_id');
            $table->dropColumn('winner_drawn_at');
        });
    }
};
