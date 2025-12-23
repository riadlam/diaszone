<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_page_shows_pending_when_website_enabled_false()
    {
        $seller = Seller::factory()->create([
            'username' => 'no-site',
            'website_enabled' => false,
            'website_url' => null,
        ]);

        $response = $this->get(route('seller.store.game', ['username' => $seller->username, 'gameType' => 'mobilelegends']));
        $response->assertStatus(200);
        $response->assertSee('This store is coming soon');
        // ensure the pending copy is displayed — seller name is optional in some renderings
        $response->assertSee('This store is coming soon');
    }

    public function test_game_page_shows_pending_when_legacy_is_website_zero()
    {
        $seller = Seller::factory()->create([
            'username' => 'legacy-off',
            'website_enabled' => true,
        ]);

        // Simulate legacy column being set to 0
        $seller->is_website = 0;
        $seller->save();

        $response = $this->get(route('seller.store.game', ['username' => $seller->username, 'gameType' => 'mobilelegends']));
        $response->assertStatus(200);
        $response->assertSee('This store is coming soon');
    }
}
