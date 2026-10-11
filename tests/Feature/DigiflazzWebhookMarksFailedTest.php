<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DigiflazzWebhookMarksFailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_marks_order_failed_on_terminal_gagal(): void
    {
        $secret = 'testsecret-fail';
        putenv('DIGIFLAZZ_WEBHOOK_SECRET='.$secret);

        config([
            'telegram.bot_token' => 'fake-token',
            'telegram.chat_id' => '1234',
            'telegram.api_url' => 'https://api.telegram.org/bot/',
        ]);
        Http::fake([
            'https://api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 222]], 200),
            'https://api.telegram.org/*/editMessageText' => Http::response(['ok' => true], 200),
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'freefire',
            'name' => 'FF 110',
            'code' => 'freefire-110dias',
            'diamonds' => 110,
            'price' => 1.00,
            'price_dzd' => 280,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-FF-FAIL-1',
            'status' => 'sending',
            'diamond_pack_id' => $pack->id,
            'player_id_ff' => '2079348734',
            'tlg_message_id' => 222,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'quantity' => 1,
            'unit_price_dzd' => 280,
            'subtotal_dzd' => 280,
            'total_dzd' => 280,
        ]);

        $itemId = $order->orderItems()->first()->id;
        $refId = "order-{$order->id}-item-{$itemId}-abc123";

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'order_item_id' => $itemId,
            'diamond_pack_id' => $pack->id,
            'ref_id' => $refId,
            'buyer_sku_code' => 'freefire-110dias',
            'customer_no' => '2079348734',
            'status' => 'Pending',
            'rc' => '03',
            'event' => 'create',
        ]);

        $payload = [
            'data' => [
                'ref_id' => $refId,
                'customer_no' => '2079348734',
                'buyer_sku_code' => 'freefire-110dias',
                'status' => 'Gagal',
                'rc' => '02',
                'message' => 'Transaksi Gagal',
                'trx_id' => 'trx-fail-1',
            ],
        ];
        $raw = json_encode($payload);
        $sig = 'sha1='.hash_hmac('sha1', $raw, $secret);

        $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-Hub-Signature' => $sig,
            'X-Digiflazz-Event' => 'update',
            'User-Agent' => 'Digiflazz-Hookshot',
        ])->postJson(route('digiflazz.webhook'), $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertSame('failed', $order->status);
        $this->assertStringContainsString('Transaksi Gagal', (string) $order->notes);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/editMessageText')) {
                return false;
            }

            return str_contains((string) ($request['text'] ?? ''), 'Order Failed');
        });
    }

    public function test_terminal_failure_helper(): void
    {
        $this->assertTrue(DigiflazzStatus::isTerminalFailure('Gagal', '02'));
        $this->assertTrue(DigiflazzStatus::isTerminalFailure('error', '71'));
        $this->assertTrue(DigiflazzStatus::isTerminalFailure('Gagal', '54'));
        $this->assertFalse(DigiflazzStatus::isTerminalFailure('Pending', '03'));
        $this->assertFalse(DigiflazzStatus::isTerminalFailure('Sukses', '00'));
    }
}
