<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('games') || ! Schema::hasColumn('games', 'required_fields')) {
            return;
        }

        DB::table('games')->where('game_type', 'bloodstrike')->update([
            'required_fields' => json_encode([
                ['data_name' => 'user_id_bs', 'type' => 'text', 'required' => true, 'name' => 'User ID'],
                ['data_name' => 'server_bs', 'type' => 'select', 'required' => false, 'name' => 'Server', 'options' => [
                    ['value' => 'global', 'label' => 'Global'],
                ]],
            ]),
            'updated_at' => now(),
        ]);

        DB::table('games')->where('game_type', 'honorofkings')->update([
            'required_fields' => json_encode([
                ['data_name' => 'player_id', 'type' => 'text', 'required' => true, 'name' => 'Player ID'],
            ]),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('games') || ! Schema::hasColumn('games', 'required_fields')) {
            return;
        }

        DB::table('games')->where('game_type', 'bloodstrike')->update([
            'required_fields' => json_encode([
                ['data_name' => 'save_id', 'type' => 'text', 'required' => true, 'name' => 'User ID'],
            ]),
            'updated_at' => now(),
        ]);

        DB::table('games')->where('game_type', 'honorofkings')->update([
            'required_fields' => json_encode([
                ['data_name' => 'save_id', 'type' => 'text', 'required' => true, 'name' => 'User ID'],
            ]),
            'updated_at' => now(),
        ]);
    }
};
