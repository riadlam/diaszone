<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SellerStorefrontSofizPayBaridimobTest extends TestCase
{
    use RefreshDatabase;

    private function mockDigiflazzProductAvailable(bool $multi = true): void
    {
        $mock = $this->createMock(\App\Services\DigiflazzService::class);
        $mock->method('checkProductAvailability')->willReturn([
            'available' => true,
            'buyer_product_status' => true,
            'seller_product_status' => true,
            'multi' => $multi,
        ]);
        $this->app->instance(\App\Services\DigiflazzService::class, $mock);
    }

    private function fakeSofizPayCreate(float $expectedAmount, string $paymentUrl = 'https://cib.test/pay'): void
    {
        config([
            'services.sofizpay.enabled' => true,
            'services.sofizpay.sandbox' => false,
            'services.sofizpay.base_url' => 'https://sofizpay.com',
            'services.sofizpay.merchant_account' => 'GA_SELLER_STORE',
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) use ($expectedAmount, $paymentUrl) {
            if (! str_contains($request->url(), 'make-cib-transaction')) {
                return Http::response('unexpected', 500);
            }
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $q);
            if (($q['amount'] ?? null) !== number_format($expectedAmount, 2, '.', '')) {
                return Http::response(['success' => false, 'message' => 'amount mismatch'], 422);
            }

            return Http::response([
                'success' => true,
                'transaction_id' => '11111111-1111-1111-1111-111111111111',
                'cib_transaction_id' => 2848999999,
                'payment_url' => $paymentUrl,
                'amount' => (string) $expectedAmount,
                'status' => 'pending_user_transfer_start',
                'cib_response' => [
                    'errorCode' => '0',
                    'orderId' => 'ORDERREF1',
                    'formUrl' => $paymentUrl,
                ],
            ], 200);
        });
    }

    public function test_seller_storefront_baridimob_creates_sofizpay_transaction_at_selling_price(): void
    {
        $this->mockDigiflazzProductAvailable();

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

        $paymentUrl = 'https://cib.test/pay-seller-1';
        $this->fakeSofizPayCreate(1200.00, $paymentUrl);

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

        $response->assertRedirect($paymentUrl);

        $this->assertDatabaseHas('sofizpay_cib_transactions', [
            'amount_expected' => 1200.00,
            'status' => 'pending',
        ]);

        $order = \App\Models\Order::first();
        $this->assertNotNull($order->sofizpay_cib_transaction_id);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'sofizpay_cib_transaction_id' => $order->sofizpay_cib_transaction_id,
            'status' => 'pending_bmccp',
        ]);
    }

    public function test_baridimob_ajax_returns_checkout_url(): void
    {
        $this->mockDigiflazzProductAvailable();

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

        $paymentUrl = 'https://cib.test/pay-ajax';
        $this->fakeSofizPayCreate(500.00, $paymentUrl);

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
        $this->assertEquals($paymentUrl, $resp->json('checkout_url'));
    }

    public function test_baridimob_ajax_insufficient_balance_returns_400(): void
    {
        $this->mockDigiflazzProductAvailable();

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

    public function test_seller_custom_price_is_sent_to_sofizpay(): void
    {
        $this->mockDigiflazzProductAvailable();

        $seller = Seller::factory()->create([
            'username' => 'custompriceseller',
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

        SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 1500.00,
            'custom_price_usd' => 6.00,
            'is_active' => true,
        ]);

        $paymentUrl = 'https://cib.test/pay-custom';
        $this->fakeSofizPayCreate(1500.00, $paymentUrl);

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

        $response->assertRedirect($paymentUrl);

        $this->assertDatabaseHas('sofizpay_cib_transactions', [
            'amount_expected' => 1500.00,
            'status' => 'pending',
        ]);
    }
}
