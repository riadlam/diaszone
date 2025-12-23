<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontFlexyPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_seller_flexy_price_when_configured_and_enabled()
    {
        $seller = Seller::factory()->create([
            'username' => 'testseller',
            'website_enabled' => true,
            'flexy_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Test Pack',
            'code' => 'TP100',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'flexy_price' => 1333.00,
            'is_active' => true,
        ]);

        $resp = $this->getJson(route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack->id]));
        $resp->assertStatus(200)->assertJson(['success' => true, 'flexy_price' => 1333.0]);
    }

    public function test_returns_403_if_flexy_disabled_for_seller()
    {
        $seller = Seller::factory()->create([
            'username' => 'no-flexy',
            'website_enabled' => true,
            'flexy_enabled' => false,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Quick Pack',
            'code' => 'QP01',
            'diamonds' => 10,
            'bonus_diamonds' => 0,
            'price' => 1.00,
            'price_dzd' => 120.00,
            'base_price_dzd' => 100.00,
            'price_usd' => 0.5,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $resp = $this->getJson(route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack->id]));
        $resp->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_fallbacks_to_custom_or_pack_price_when_no_flexy_price_set()
    {
        $seller = Seller::factory()->create([
            'username' => 'fallback-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Test Pack',
            'code' => 'TP100',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00,
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create a custom price without flexy_price -> should return custom_price_dzd
        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1400.00,
            'custom_price_usd' => 5.00,
            'is_active' => true,
        ]);

        $resp = $this->getJson(route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack->id]));
        $resp->assertStatus(200)->assertJson(['success' => true, 'flexy_price' => 1400.0]);

        // Remove custom price and ensure fallback to pack price
        SellerGamePrice::truncate();

        $resp2 = $this->getJson(route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack->id]));
        $resp2->assertStatus(200)->assertJson(['success' => true, 'flexy_price' => 1200.0]);
    }
}
