<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontChargilyPriceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payment_rejects_selling_price_below_base()
    {
        $seller = Seller::factory()->create([
            'username' => 'charged-seller',
            'website_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Chargily Pack',
            'code' => 'CP100',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
        ]);

        // Seller has a malicious / invalid custom price lower than base
        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 900.00, // below base 1000
            'custom_price_usd' => 3.40,
            'is_active' => true,
        ]);

        // Perform a processPayment (Chargily path) - expecting rejection due to invalid price
        $resp = $this->postJson(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => 'player123',
            'payment_method' => 'baridimob'
        ]);

        $resp->assertStatus(400)->assertJson(['success' => false]);
    }
}
