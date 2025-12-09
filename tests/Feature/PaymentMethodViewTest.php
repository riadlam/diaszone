<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_view_contains_flexy_elements_and_endpoint()
    {
        $seller = Seller::factory()->create([
            'username' => 'view-seller',
            'website_enabled' => true,
            'flexy_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'View Pack',
            'code' => 'VP01',
            'diamonds' => 50,
            'bonus_diamonds' => 5,
            'price' => 5.00,
            'price_dzd' => 600.00,
            'base_price_dzd' => 500.00,
            'price_usd' => 2.5,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock VipResellerService to return a valid nickname so the page renders
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        // Query the view endpoint with required params
        $response = $this->get(route('seller.store.payment-method', ['username' => $seller->username]) . '?pack_id=' . $pack->id . '&game_type=mobilelegends&player_id=11111&zone_id=1010');
        $response->assertStatus(200);

        // Check that the page contains the element IDs our JS will use
        $response->assertSee('id="total-amount"', false);
        $response->assertSee('id="flexy-amount"', false);
        $response->assertSee('id="final_price"', false);

        // Ensure the flexy price route URL exists in the page so JS can call it
        $flexyUrl = route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack->id]);
        $response->assertSee(e($flexyUrl), false);
    }
}
