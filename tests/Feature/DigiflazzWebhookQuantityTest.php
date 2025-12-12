<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_with_quantity_requires_multiple_successes()
    {
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=testsecretref');
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 555]], 200)]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Weekly Pass 3x', 'code' => 'mlbb-pass-3', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);
        $order = Order::create(['order_number' => 'ORD-PASS-1', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048', 'quantity' => 3]);

        // Post three webhook events (one by one). Each should create a DigiflazzStatus and only after 3 successes the order becomes completed
        for ($i = 1; $i <= 3; $i++) {
            $payload = ['data' => ['ref_id' => 'order-' . $order->id . '-pass-' . $i, 'trxid' => 'trx-' . $i, 'customer_no' => '2057629734048', 'status' => 'Sukses', 'rc' => '00', 'buyer_last_saldo' => 1000]];
            $raw = json_encode($payload);
            $sig = 'sha1=' . hash_hmac('sha1', $raw, getenv('DIGIFLAZZ_WEBHOOK_SECRET'));

            $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'update', 'User-Agent' => 'Digiflazz-Hookshot'])
                 ->postJson(route('digiflazz.webhook'), $payload)
                 ->assertStatus(200)->assertJson(['ok' => true]);

            $order->refresh();

            if ($i < 3) {
                $this->assertEquals('sending', $order->status);
                $this->assertStringContainsString("{$i}/3", \App\Services\TelegramService::formatOrderMessage($order));
            } else {
                $this->assertEquals('completed', $order->status);
                $this->assertStringContainsString("3/3", \App\Services\TelegramService::formatOrderMessage($order));
            }
        }
    }
}
