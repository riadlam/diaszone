<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;
use App\Models\ChargilyStatus;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ChargilyWebhookDigiflazzFlowTest extends TestCase
{
    use RefreshDatabase;
    public function test_chargily_paid_triggers_digiflazz_and_keeps_order_sending()
    {
        // Configure Digiflazz credentials
        config(['services.digiflazz.username' => 'testuser', 'services.digiflazz.sign' => 'testsign']);
        putenv('DIGIFLAZZ_USERNAME=testuser');
        putenv('DIGIFLAZZ_SIGN=testsign');

        // Sanity check: ensure config is visible in runtime
        $this->assertEquals('testuser', config('services.digiflazz.username'));

        // Fake Telegram
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 222]], 200)]);

        // Create pack and order and chargily_status
        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Chargily Test Pack', 'code' => 'mlbb-20', 'diamonds' => 20, 'price' => 2.00, 'price_dzd' => 520, 'is_active' => true]);
        $order = Order::create([
            'order_number' => 'ORD-CHG-1',
            'status' => 'pending_bmccp',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '205762973',
            'zone_id_ml' => '4048',
        ]);

        $chargily = ChargilyStatus::create([
            'order_id' => $order->id,
            'checkout_id' => 'ck_test_123',
            'event_type' => 'checkout.created',
            'status' => 'pending',
            'amount' => 1000,
        ]);

        $order->chargily_status_id = $chargily->id;
        $order->save();

        // Mock DigiflazzService to ensure placeOrder is called
        $this->mock(\App\Services\DigiflazzService::class, function ($mock) use ($pack, $order) {
            $mock->shouldReceive('placeOrder')->once()->withAnyArgs()->andReturn(['result' => true, 'data' => ['status' => 'waiting'], 'message' => 'queued']);
        });

        // Mock VipResellerService nickname validation to succeed so recharge proceeds
        $this->mock(\App\Services\VipResellerService::class, function ($mock) use ($order) {
            $mock->shouldReceive('checkNickname')->once()->with($order->user_id_ml, $order->zone_id_ml)->andReturn(['result' => true, 'data' => 'VerifiedNick']);
        });

        // Prepare Chargily webhook payload
        $payload = [
            'id' => 'evt_1',
            'entity' => 'event',
            'type' => 'checkout.paid',
            'data' => [
                'id' => 'ck_test_123',
                'status' => 'paid',
                'amount' => 1000,
            ],
        ];

        // Compute signature with Chargily secret
        $secret = 'chargily-secret';
        config(['services.chargily_pay_v2.secret' => $secret]);

        $raw = json_encode($payload);
        $signature = hash_hmac('sha256', $raw, $secret);

        $response = $this->withHeaders(['signature' => $signature])->postJson(route('baridimob.webhook'), $payload);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('sending', $order->status, 'Order should remain in sending state and wait for Digiflazz webhook');
    }
}
