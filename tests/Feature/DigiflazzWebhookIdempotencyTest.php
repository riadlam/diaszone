<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;
use App\Models\VipResellerStatus;
use App\Models\DigiflazzStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_is_idempotent_and_creates_mirror_once()
    {
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=testsecret');
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 333]], 200)]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-55dias', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);
        $order = Order::create([
            'order_number' => 'ORD-IDEMP-1',
            'status' => 'sending',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '205762973',
            'zone_id_ml' => '4048',
        ]);

        $payload = ['data' => ['ref_id' => 'ref-idemp-1', 'trxid' => 'trx-idemp-1', 'customer_no' => (string)$order->user_id_ml . $order->zone_id_ml, 'status' => 'Pending', 'rc' => '03', 'buyer_last_saldo' => 123456]];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, getenv('DIGIFLAZZ_WEBHOOK_SECRET'));

        // First delivery
        $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'create', 'User-Agent' => 'Digiflazz-Hookshot'])
             ->postJson(route('digiflazz.webhook'), $payload)
             ->assertStatus(200)->assertJson(['ok' => true]);

        $this->assertDatabaseHas('digiflazz_statuses', ['ref_id' => 'ref-idemp-1', 'trxid' => 'trx-idemp-1']);
        $this->assertDatabaseHas('vipreseller_status', ['trxid' => 'trx-idemp-1', 'order_id' => $order->id]);

        // Second (duplicate) delivery should not create new records
        $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'create', 'User-Agent' => 'Digiflazz-Hookshot'])
             ->postJson(route('digiflazz.webhook'), $payload)
             ->assertStatus(200)->assertJson(['ok' => true]);

        $this->assertEquals(1, DigiflazzStatus::where('ref_id', 'ref-idemp-1')->count());
        $this->assertEquals(1, VipResellerStatus::where('trxid', 'trx-idemp-1')->count());
    }

    public function test_webhook_attaches_to_sending_order_when_multiple_orders_exist()
    {
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=testsecret2');
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 444]], 200)]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-55dias', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);
        // Older completed order
        $old = Order::create(['order_number' => 'ORD-OLD', 'status' => 'completed', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);
        // Recent sending order
        $recent = Order::create(['order_number' => 'ORD-RECENT', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);

        $payload = ['data' => ['ref_id' => 'ref-test-attach', 'trxid' => 'trx-attach', 'customer_no' => '2057629734048', 'status' => 'Sukses', 'rc' => '00', 'buyer_last_saldo' => 999]];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, getenv('DIGIFLAZZ_WEBHOOK_SECRET'));

        $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'update', 'User-Agent' => 'Digiflazz-Hookshot'])
             ->postJson(route('digiflazz.webhook'), $payload)
             ->assertStatus(200)->assertJson(['ok' => true]);

        // The vipreseller_status/trxid should be linked to the recent sending order
        $this->assertDatabaseHas('vipreseller_status', ['trxid' => 'trx-attach', 'order_id' => $recent->id]);
        $this->assertDatabaseHas('digiflazz_statuses', ['ref_id' => 'ref-test-attach', 'order_id' => $recent->id]);
    }
}
