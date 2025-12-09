<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontChargilyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_storefront_baridimob_uses_base_price_dzd_and_creates_chargily_status()
    {
        // Create a seller and pack
        $seller = Seller::factory()->create([
            'username' => 'testseller',
            'website_enabled' => true,
            // ensure seller has enough wallet balance (non-flexy branch checks base cost)
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Test Pack',
            'code' => 'TP100',
            'diamonds' => 100,
            'bonus_diamonds' => 10,
            'price' => 10.00,
            'price_dzd' => 1200.00,
            'base_price_dzd' => 1000.00, // <- important: expected Chargily amount
            'price_usd' => 4.00,
            'discount_percentage' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Mock Chargily service to avoid external network call
        $mock = \Mockery::mock(\App\Services\ChargilyPayV2Service::class);
        // Simulate service returning legacy 'id' key
        $mock->shouldReceive('createCheckout')->once()->andReturn([
            'id' => 'ck_test_123',
            'checkout_url' => 'https://pay.chargily.net/checkout/ck_test_123'
        ]);

        $this->app->instance(\App\Services\ChargilyPayV2Service::class, $mock);

        // Mock VipResellerService to accept nickname
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        // Post to the storefront payment endpoint (baridimob) and expect redirect
        $response = $this->post(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '987654321',
            'zone_id' => '12345',
            'payment_method' => 'baridimob',
        ]);

        $response->assertRedirect('https://pay.chargily.net/checkout/ck_test_123');

        // Chargily status record should be stored with amount equal to selling price (price_dzd)
        $this->assertDatabaseHas('chargily_status', [
            'checkout_id' => 'ck_test_123',
            'amount' => 1200.00,
            'status' => 'pending'
        ]);

        // Ensure the order is linked to the created chargily_status
        $order = \App\Models\Order::first();
        $this->assertNotNull($order->chargily_status_id);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'chargily_status_id' => $order->chargily_status_id,
        ]);
    }

    public function test_baridimob_ajax_returns_checkout_url()
    {
        $seller = Seller::factory()->create([
            'username' => 'ajaxseller',
            'website_enabled' => true,
            'wallet_balance' => 5000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'AJAX Pack',
            'code' => 'AJX',
            'diamonds' => 50,
            'price' => 1.0,
            'price_dzd' => 500,
            'base_price_dzd' => 400,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $mock = \Mockery::mock(\App\Services\ChargilyPayV2Service::class);
        $mock->shouldReceive('createCheckout')->once()->andReturn([
            'checkout_id' => 'ck_ajax_123',
            'checkout_url' => 'https://pay.chargily.net/checkout/ck_ajax_123'
        ]);
        $this->app->instance(\App\Services\ChargilyPayV2Service::class, $mock);

        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $resp = $this->postJson(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '11111',
            'zone_id' => '2222',
            'payment_method' => 'baridimob',
        ]);

        $resp->assertStatus(200);
        $resp->assertJsonStructure(['success', 'checkout_url', 'checkout_id']);
        $this->assertEquals('https://pay.chargily.net/checkout/ck_ajax_123', $resp->json('checkout_url'));
    }

    public function test_baridimob_ajax_insufficient_balance_returns_400()
    {
        $seller = Seller::factory()->create([
            'username' => 'lowwallet',
            'website_enabled' => true,
            'wallet_balance' => 100,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Expensive Pack',
            'code' => 'EXP',
            'diamonds' => 50,
            'price' => 10.0,
            'price_dzd' => 1000,
            'base_price_dzd' => 800,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $resp = $this->postJson(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '11111',
            'zone_id' => '2222',
            'payment_method' => 'baridimob',
        ]);

        $resp->assertStatus(400);
        $resp->assertJson(['success' => false]);
    }

    public function test_seller_custom_price_is_charged_to_client()
    {
        $seller = Seller::factory()->create([
            'username' => 'testseller',
            'website_enabled' => true,
            'wallet_balance' => 5000,
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

        // Add custom price for seller
        \App\Models\SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'is_active' => true,
        ]);

        // Simulate service returning 'checkout_id' top-level key (newer format)
        $mock = \Mockery::mock(\App\Services\ChargilyPayV2Service::class);
        $mock->shouldReceive('createCheckout')->once()->andReturn([
            'checkout_id' => 'ck_test_custom_123',
            'checkout_url' => 'https://pay.chargily.net/checkout/ck_test_custom_123'
        ]);

        $this->app->instance(\App\Services\ChargilyPayV2Service::class, $mock);

        // Mock VipResellerService to accept nickname for the second case as well
        $vipMock = \Mockery::mock(\App\Services\VipResellerService::class);
        $vipMock->shouldReceive('checkNickname')->andReturn(['result' => true, 'data' => 'PlayerName'])->once();
        $this->app->instance(\App\Services\VipResellerService::class, $vipMock);

        $response = $this->post(route('seller.store.payment', ['username' => $seller->username]), [
            'pack_id' => $pack->id,
            'game_type' => 'mobilelegends',
            'player_id' => '987654321',
            'zone_id' => '12345',
            'payment_method' => 'baridimob',
        ]);

        $response->assertRedirect('https://pay.chargily.net/checkout/ck_test_custom_123');

        $this->assertDatabaseHas('chargily_status', [
            'checkout_id' => 'ck_test_custom_123',
            'amount' => 1500.00,
        ]);
    }
}
