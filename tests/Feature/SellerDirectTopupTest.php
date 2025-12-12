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

        // Configure Digiflazz and mock its service for direct top-up
        config(['services.digiflazz.username' => 'testuser', 'services.digiflazz.sign' => 'testsign']);
        $this->mock(\App\Services\DigiflazzService::class, function ($mock) {
            $mock->shouldReceive('placeOrder')->once()->withAnyArgs()->andReturn([
                'result' => true,
                'data' => ['data' => ['trxid' => 't123', 'price' => 200], 'ref_id' => 'ref-1'],
                'message' => 'ok'
            ]);
            $mock->shouldReceive('cekSaldo')->once()->andReturn(['result' => true, 'deposit' => 987654]);
        });

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

        $this->assertDatabaseHas('orders', ['seller_id' => $seller->id, 'diamond_pack_id' => $pack->id, 'status' => 'sending']);

        $order = Order::where('seller_id', $seller->id)->first();

        // wallet deducted (base_price_dzd) and profit credited
        $seller->refresh();
        // initial:1000 - baseCost(200) + profit(600) = 1400
        $this->assertEquals(1400.00, (float) $seller->wallet_balance);

        // ensure seller profit marked as paid on order
        $this->assertTrue((bool) $order->fresh()->seller_profit_paid);

        // Digiflazz initial record should exist (order linked)
        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id]);

        // We should also have a provider status record with the Digiflazz balance
        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 't123']);
        $row = \DB::table('digiflazz_statuses')->where('trxid', 't123')->first();
        $this->assertNotNull($row);
        $this->assertEquals('987654', (string)(json_decode($row->additional_data ?? '{}', true)['balance'] ?? ''));

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
