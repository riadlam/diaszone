<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookRefIdFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_attaches_via_ref_id_when_customer_no_missing()
    {
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=testsecretref');
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 555]], 200)]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-55dias', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);
        $order = Order::create(['order_number' => 'ORD-REF-1', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);

        // ref_id contains order id pattern order-<id>-suffix
        $payload = ['data' => ['ref_id' => 'order-' . $order->id . '-abc123', 'trxid' => 'trx-ref-id', 'status' => 'Sukses', 'rc' => '00', 'buyer_last_saldo' => 1111]];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, getenv('DIGIFLAZZ_WEBHOOK_SECRET'));

        $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'update', 'User-Agent' => 'Digiflazz-Hookshot'])
             ->postJson(route('digiflazz.webhook'), $payload)
             ->assertStatus(200)->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertDatabaseHas('digiflazz_statuses', ['ref_id' => 'order-' . $order->id . '-abc123', 'trxid' => 'trx-ref-id', 'order_id' => $order->id]);
    }
}
