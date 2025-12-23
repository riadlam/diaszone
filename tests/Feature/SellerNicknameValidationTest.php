<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use App\Services\VipResellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerNicknameValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_payment_method_rejects_invalid_nickname()
    {
        $seller = Seller::factory()->create([
            'username' => 'ml-seller',
            'website_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack',
            'code' => 'ML100',
            'diamonds' => 100,
            'price' => 1.00,
            'price_dzd' => 100.00,
            'base_price_dzd' => 90.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock VipResellerService to return failure
        $mock = \Mockery::mock(VipResellerService::class);
        $mock->shouldReceive('checkNickname')->andReturn(['result' => false, 'message' => 'invalid'])->once();
        $this->app->instance(VipResellerService::class, $mock);

        $resp = $this->post(route('seller.store.checkout', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '12345678',
            'zone_id' => '1111',
        ]);

        // Should redirect back with validation errors
        $resp->assertSessionHasErrors(['player_id']);
    }

    public function test_show_payment_method_accepts_valid_nickname_and_passes_to_view()
    {
        $seller = Seller::factory()->create([
            'username' => 'ml-seller-2',
            'website_enabled' => true,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack 2',
            'code' => 'ML101',
            'diamonds' => 50,
            'price' => 1.00,
            'price_dzd' => 100.00,
            'base_price_dzd' => 90.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock VipResellerService to return success + nickname
        $mock = \Mockery::mock(VipResellerService::class);
        $mock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'BestNickname'])->once();
        $this->app->instance(VipResellerService::class, $mock);

        $resp = $this->post(route('seller.store.checkout', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '22222222',
            'zone_id' => '2222',
        ]);

        $resp->assertStatus(200);
        $resp->assertViewIs('seller.storefront.payment-method');
        $resp->assertViewHas('playerData', function ($playerData) {
            return isset($playerData['nickname']) && $playerData['nickname'] === 'BestNickname';
        });
    }

    public function test_process_payment_rejects_invalid_nickname()
    {
        $seller = Seller::factory()->create([
            'username' => 'ml-seller-3',
            'website_enabled' => true,
            'wallet_balance' => 10000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack 3',
            'code' => 'ML102',
            'diamonds' => 50,
            'price' => 1.00,
            'price_dzd' => 100.00,
            'base_price_dzd' => 90.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Ensure seller has price so sale flows normally otherwise
        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 120.00,
            'custom_price_usd' => 1.20,
            'is_active' => true,
        ]);

        // Mock VipResellerService to return failure for nickname
        $mock = \Mockery::mock(VipResellerService::class);
        $mock->shouldReceive('checkNickname')->andReturn(['result' => false, 'message' => 'invalid'])->once();
        $this->app->instance(VipResellerService::class, $mock);

        $resp = $this->post(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '98765',
            'zone_id' => '3333',
            'payment_method' => 'baridimob',
        ]);

        // Expect back with errors about nickname validation
        $resp->assertSessionHasErrors(['player_id']);
    }
}
