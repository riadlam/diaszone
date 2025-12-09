<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodFlexySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_flexy_card_hidden_when_is_flexy_zero()
    {
        $seller = Seller::factory()->create([
            'username' => 'flexy-off',
            'website_enabled' => true,
            'flexy_enabled' => true,
        ]);

        // Legacy flag explicitly disable flexy
        $seller->is_flexy = 0;
        $seller->save();

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack',
            'code' => 'ML200',
            'diamonds' => 100,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock VipResellerService to accept nickname for page rendering
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $response = $this->get(route('seller.store.payment-method', ['username' => $seller->username]) . '?pack_id=' . $pack->id . '&game_type=mobilelegends&player_id=11111&zone_id=1011');
        $response->assertStatus(200);

        // Ensure we see the disabled flexy text
        $response->assertSee('Flexy', false);
        $response->assertSee('Disabled by seller', false);
    }

    public function test_flexy_shows_dynamic_number_and_instruction_when_enabled()
    {
        $seller = Seller::factory()->create([
            'username' => 'flexy-on',
            'website_enabled' => true,
            'flexy_enabled' => true,
            'flexy_number' => '1234567890',
            'flexy_instruction' => 'Pay using Flexy with reference XYZ',
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack',
            'code' => 'ML201',
            'diamonds' => 50,
            'price' => 1.0,
            'price_dzd' => 50,
            'base_price_dzd' => 40,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock VipResellerService to accept nickname for page rendering
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $response = $this->get(route('seller.store.payment-method', ['username' => $seller->username]) . '?pack_id=' . $pack->id . '&game_type=mobilelegends&player_id=11111&zone_id=1011');
        $response->assertStatus(200);

        // The modal should include the seller's configured number and instruction
        $response->assertSee(e($seller->flexy_number), false);
        $response->assertSee(e($seller->flexy_instruction), false);
    }
}
