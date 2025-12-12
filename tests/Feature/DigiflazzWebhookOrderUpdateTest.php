<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookOrderUpdateTest extends TestCase
{
    use RefreshDatabase;
    public function test_webhook_marks_order_completed_and_updates_telegram()
    {
        // Configure webhook secret
        $secret = 'testsecret-webhook';
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=' . $secret);

        // Fake Telegram HTTP API
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake([
            'https://api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 111]], 200),
            'https://api.telegram.org/*/editMessageText' => Http::response(['ok' => true], 200),
        ]);

        // Create a pack and an order
        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-10', 'diamonds' => 10, 'price' => 1.00, 'price_dzd' => 260, 'is_active' => true]);
        $order = Order::create([
            'order_number' => 'ORD-TEST-123',
            'status' => 'sending',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '205762973',
            'zone_id_ml' => '4048',
        ]);

        // Send initial Telegram message to simulate existing notification
        $messageId = \App\Services\TelegramService::sendMessage(\App\Services\TelegramService::formatOrderMessage($order));
        $this->assertNotNull($messageId);

        $order->tlg_message_id = $messageId;
        $order->save();

        // Prepare webhook payload targeting this order by numeric customer_no and include buyer_last_saldo
        $payload = ['data' => ['ref_id' => 'ref-test-1', 'customer_no' => (string)$order->id, 'status' => 'Sukses', 'rc' => '00', 'trxid' => 'trx-123', 'buyer_last_saldo' => 555000]];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, $secret);

        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-Hub-Signature' => $sig,
            'X-Digiflazz-Event' => 'create',
            'User-Agent' => 'Digiflazz-Hookshot',
        ])->postJson(route('digiflazz.webhook'), $payload);

        $response->assertStatus(200)->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertEquals('completed', $order->status);

        // Telegram editMessageText should have been called
        Http::assertSent(function ($request) use ($messageId) {
            if (!str_contains($request->url(), '/editMessageText')) return false;
            $text = $request['text'] ?? '';
            return strpos((string)$text, 'Provider Balance') !== false && strpos((string)$text, number_format(555000, 0)) !== false;
        });

        // Also ensure we saved a vipreseller_status mirror containing the balance
        $this->assertDatabaseHas('vipreseller_status', ['order_id' => $order->id, 'trxid' => 'trx-123', 'service' => 'digiflazz', 'balance' => '555000']);
    }
}
