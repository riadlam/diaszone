<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;
use App\Models\ChargilyStatus;
use App\Models\DigiflazzStatus;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ChargilyMultiQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_chargily_paid_triggers_multiple_provider_calls_for_quantity()
    {
        // Ensure Digiflazz is configured so processChargilyRecharge uses DigiflazzService
        config(['services.digiflazz.username' => 'fakeuser', 'services.digiflazz.sign' => 'fakesign']);

        // Mock DigiflazzService so we can assert it is called N times and also persist DigiflazzStatus rows reliably
        $this->mock(\App\Services\DigiflazzService::class, function ($mock) {
            $mock->shouldReceive('placeOrder')->twice()->andReturnUsing(function ($pack, $order) {
                static $i = 0;
                $i++;
                $ref = 'order-' . $order->id . '-mock-' . $i;
                $trx = 'trx-' . $i;

                \App\Models\DigiflazzStatus::updateOrCreate(
                    ['ref_id' => $ref],
                    [
                        'order_id' => $order->id,
                        'ref_id' => $ref,
                        'trxid' => $trx,
                        'buyer_sku_code' => $pack->code,
                        'customer_no' => ($order->user_id_ml ?? $order->player_id_ff ?? $order->user_id ?? ''),
                        'rc' => '03',
                        'status' => 'Pending',
                        'message' => 'Mocked',
                        'price' => 11601,
                        'event' => 'create',
                        'additional_data' => [],
                    ]
                );

                return ['result' => true, 'data' => ['trxid' => $trx, 'buyer_sku_code' => $pack->code, 'customer_no' => ($order->user_id_ml ?? '')], 'ref_id' => $ref, 'message' => 'ok'];
            });
        });

        // Fake VIP Reseller nickname check and Telegram
        Http::fake([
            'https://vip-reseller.co.id/*' => Http::response(['result' => true, 'data' => 'ValidNick', 'message' => 'Success'], 200),
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 555]], 200),
        ]);

        // Configure Chargily secret for signature verification
        $secret = 'test_chargily_secret';
        config(['services.chargily_pay_v2.secret' => $secret]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Weekly Pass 2x', 'code' => 'mlbb-pass-2', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true, 'special_quantity' => 2]);

        $order = Order::create(['order_number' => 'ORD-PASS-2', 'status' => 'pending_bmccp', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048', 'quantity' => 2]);

        // Create ChargilyStatus linked to the order so webhook finds order quickly
        ChargilyStatus::create(['order_id' => $order->id, 'checkout_id' => 'ch_test_2', 'event_type' => 'checkout.created', 'status' => 'pending']);

        // Build webhook payload
        $payload = ['id' => 'evt_1', 'entity' => 'event', 'type' => 'checkout.paid', 'data' => ['id' => 'ch_test_2', 'status' => 'paid', 'amount' => 75]];
        $raw = json_encode($payload);
        $sig = hash_hmac('sha256', $raw, $secret);

        $this->withHeaders(['Content-Type' => 'application/json', 'signature' => $sig])
             ->postJson('/webhook/baridimob', $payload)
             ->assertStatus(200)->assertJson(['success' => true]);

        $order->refresh();

        // Order should be 'sending' (we rely on Digiflazz webhooks to mark completed)
        $this->assertEquals('sending', $order->status);

        // There should be two DigiflazzStatus records created for this order (2 submissions)
        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 'trx-1']);
        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 'trx-2']);
        $this->assertEquals(2, DigiflazzStatus::where('order_id', $order->id)->count());
    }
}
