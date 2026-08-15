<?php

namespace Tests\Feature;

use App\Filament\Resources\Games\Pages\GamePacks;
use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminGameCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'status' => 'active']);
    }

    private function pack(array $overrides = []): DiamondPack
    {
        return DiamondPack::create(array_merge([
            'game_type' => 'mobilelegends',
            'name' => 'Mobile Legends 172 Diamonds',
            'code' => 'mlbb-172dias',
            'diamonds' => 156,
            'bonus_diamonds' => 16,
            'price' => 40000,
            'base_price_dzd' => 200,
            'price_dzd' => 300,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function delivery(DiamondPack $pack, bool $successful = true): DigiflazzStatus
    {
        static $sequence = 0;
        $sequence++;

        return DigiflazzStatus::create([
            'diamond_pack_id' => $pack->id,
            'buyer_sku_code' => $pack->code,
            'ref_id' => 'REF-'.$sequence,
            'status' => $successful ? 'Sukses' : 'Gagal',
            'rc' => $successful ? '00' : '01',
        ]);
    }

    public function test_games_list_shows_digiflazz_games_only(): void
    {
        $this->pack();
        $this->pack(['game_type' => 'steam_giftcard', 'name' => 'Steam Wallet 10 USD', 'code' => 'steam-10']);

        $this->actingAs($this->admin())
            ->get('/admin/games')
            ->assertSuccessful()
            ->assertSee('Mobile Legends')
            ->assertDontSee('Steam Wallet');
    }

    public function test_game_packs_page_shows_pricing_profit_and_real_topup_counts(): void
    {
        $pack = $this->pack();
        $unsold = $this->pack([
            'name' => 'Mobile Legends 706 Diamonds',
            'code' => 'mlbb-706dias',
            'diamonds' => 706,
            'bonus_diamonds' => 0,
            'base_price_dzd' => 1000,
            'price_dzd' => 1000,
            'sort_order' => 2,
        ]);

        $this->delivery($pack);
        $this->delivery($pack);
        $this->delivery($pack);
        $this->delivery($pack, successful: false);

        // Belongs to another game, so it must not leak into these counts.
        $this->delivery($this->pack([
            'game_type' => 'freefire',
            'name' => 'Free Fire 100 Diamonds',
            'code' => 'ff-100dias',
        ]));

        Livewire::actingAs($this->admin())
            ->test(GamePacks::class, ['gameType' => 'mobilelegends'])
            ->assertCanSeeTableRecords([$pack, $unsold])
            ->assertTableColumnStateSet('code', 'mlbb-172dias', $pack)
            ->assertTableColumnStateSet('diamonds', '156 + 16 bonus', $pack)
            ->assertTableColumnStateSet('price', '40 000 IDR', $pack)
            ->assertTableColumnStateSet('base_price_dzd', '200 DZD', $pack)
            ->assertTableColumnStateSet('price_dzd', '300 DZD', $pack)
            ->assertTableColumnStateSet('profit_percentage', '50.0 %', $pack)
            ->assertTableColumnStateSet('profit_percentage', '0.0 %', $unsold)
            // Passing the key makes Filament resolve the record through the table
            // query, which is where the delivery counts are calculated.
            ->assertTableColumnStateSet('topups_count', 3, $pack->getKey())
            ->assertTableColumnStateSet('failed_topups_count', 1, $pack->getKey())
            ->assertTableColumnStateSet('topups_count', 0, $unsold->getKey());
    }

    public function test_game_packs_page_rejects_non_digiflazz_games(): void
    {
        $this->pack(['game_type' => 'steam_giftcard', 'code' => 'steam-10']);

        $this->actingAs($this->admin())
            ->get('/admin/games/steam_giftcard/packs')
            ->assertNotFound();
    }

    public function test_games_tab_requires_an_admin(): void
    {
        $customer = User::factory()->create(['status' => 'active']);

        $this->actingAs($customer)
            ->get('/admin/games')
            ->assertForbidden();
    }
}
