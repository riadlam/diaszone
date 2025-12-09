<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDirectTopupPriceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_topup_rejects_when_selling_price_below_base()
    {
        $seller = Seller::factory()->create(['wallet_balance' => 1000]);

        $pack = DiamondPack::create([
            'game_type' => 'freefire',
            'name' => 'FF Pack',
            'code' => 'FF100',
            'diamonds' => 100,
            'bonus_diamonds' => 0,
            'price' => 5.00,
            'price_dzd' => 800.00,
            'base_price_dzd' => 200.00,
            'is_active' => true,
        ]);

        // Tamper seller custom price so it's below base cost
        \App\Models\SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 100.00, // below base 200
            'custom_price_usd' => 1.25,
            'is_active' => true,
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.direct-topup.process'), [
            'game_type' => 'freefire',
            'pack_id' => $pack->id,
            'player_id' => 'tamper-player'
        ]);

        $resp->assertStatus(400)->assertJson(['success' => false]);

        $this->assertDatabaseMissing('orders', ['seller_id' => $seller->id]);
    }
}
