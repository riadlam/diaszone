<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SellerDirectTopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_direct_topup_deducts_wallet_and_creates_order()
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

        $this->actingAs($seller, 'seller');

        $mockVip = \Mockery::mock(\App\Services\VipResellerService::class);
        $mockVip->shouldReceive('placeFreefireOrder')->andReturn(['result' => true, 'data' => ['trxid' => 't123', 'price' => 200], 'message' => 'ok']);
        $this->app->instance(\App\Services\VipResellerService::class, $mockVip);

        // Fake Telegram HTTP API responses so sendMessage/editMessageText behave as expected
        config([
            'telegram.bot_token' => 'fake-token',
            'telegram.chat_id' => '12345',
            'telegram.api_url' => 'https://api.telegram.org/bot/',
        ]);
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => ['message_id' => 999]], 200),
        ]);

        $resp = $this->postJson(route('seller.direct-topup.process'), [
            'game_type' => 'freefire',
            'pack_id' => $pack->id,
            'player_id' => 'player123',
        ]);

        $resp->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', ['seller_id' => $seller->id, 'diamond_pack_id' => $pack->id, 'status' => 'completed']);

        $order = Order::where('seller_id', $seller->id)->first();

        // wallet deducted (base_price_dzd)
        $seller->refresh();
        $this->assertEquals(800.00, (float) $seller->wallet_balance);

        $this->assertDatabaseHas('vipreseller_status', ['order_id' => $order->id, 'trxid' => 't123', 'status' => 'success']);

        // Ensure we saved the Telegram message id when sending the initial admin notification
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'tlg_message_id' => 999]);
    }

    public function test_direct_topup_returns_400_when_insufficient_funds()
    {
        $seller = Seller::factory()->create(['wallet_balance' => 50]);

        $pack = DiamondPack::create([
            'game_type' => 'freefire',
            'name' => 'Cheap FF',
            'code' => 'FF10',
            'diamonds' => 10,
            'bonus_diamonds' => 0,
            'price' => 1.00,
            'price_dzd' => 100.00,
            'base_price_dzd' => 100.00,
            'is_active' => true,
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.direct-topup.process'), [
            'game_type' => 'freefire',
            'pack_id' => $pack->id,
            'player_id' => 'player-x',
        ]);

        $resp->assertStatus(400)->assertJson(['success' => false]);

        $this->assertDatabaseMissing('orders', ['seller_id' => $seller->id]);
    }

    public function test_duplicate_submission_is_blocked_by_lock()
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

        $this->actingAs($seller, 'seller');

        // Simulate the lock being held by another process using the file cache driver
        config(['cache.default' => 'file']);

        $lockKey = 'seller_direct_topup_lock:' . $seller->id . ':' . $pack->id . ':' . md5('player123');
        $lock = Cache::lock($lockKey, 30);
        $this->assertTrue($lock->get());

        $resp = $this->postJson(route('seller.direct-topup.process'), [
            'game_type' => 'freefire',
            'pack_id' => $pack->id,
            'player_id' => 'player123',
        ]);

        $resp->assertStatus(423)->assertJson(['success' => false]);

        $this->assertDatabaseMissing('orders', ['seller_id' => $seller->id]);

        // Release the lock
        $lock->release();
    }
}
