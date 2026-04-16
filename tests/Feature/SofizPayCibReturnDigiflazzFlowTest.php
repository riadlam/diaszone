<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SofizPayCibTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SofizPayCibReturnDigiflazzFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sofizpay_cib_return_triggers_digiflazz_and_keeps_order_sending(): void
    {
        config([
            'services.digiflazz.username' => 'testuser',
            'services.digiflazz.sign' => 'testsign',
            'services.sofizpay.enabled' => true,
            'services.sofizpay.sandbox' => false,
            'services.sofizpay.base_url' => 'https://sofizpay.com',
            'services.sofizpay.merchant_account' => 'GA_TEST_MERCHANT',
        ]);
        putenv('DIGIFLAZZ_USERNAME=testuser');
        putenv('DIGIFLAZZ_SIGN=testsign');

        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 222]], 200),
            'https://sofizpay.com/cib-transaction-check*' => Http::response($this->paidCheckPayload('520.00', 'GA_TEST_MERCHANT'), 200),
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'SofizPay Test Pack',
            'code' => 'mlbb-20',
            'diamonds' => 20,
            'price' => 2.00,
            'price_dzd' => 520,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-SPF-1',
            'status' => 'pending_bmccp',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '205762973',
            'zone_id_ml' => '4048',
            'final_price' => 520,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'quantity' => 1,
            'unit_price_dzd' => 520,
            'unit_price_usd' => 2,
            'discount_percentage' => 0,
            'subtotal_dzd' => 520,
            'discount_amount_dzd' => 0,
            'total_dzd' => 520,
        ]);

        $spf = SofizPayCibTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'txn-uuid-1',
            'cib_order_number' => '2848342703',
            'cib_order_id' => 'mdOrder1',
            'amount_expected' => 520.00,
            'status' => 'pending',
            'create_response' => [],
        ]);
        $order->update(['sofizpay_cib_transaction_id' => $spf->id]);

        $this->mock(\App\Services\DigiflazzService::class, function ($mock) {
            $mock->shouldReceive('placeOrderWithRefId')->once()->withAnyArgs()->andReturn([
                'result' => true,
                'data' => ['status' => 'waiting', 'trxid' => 'trx-digi-1'],
                'message' => 'queued',
            ]);
        });

        $this->mock(\App\Services\VipResellerService::class, function ($mock) {
            $mock->shouldReceive('checkNickname')->once()->andReturn(['result' => true, 'data' => 'VerifiedNick']);
        });

        $eid = Crypt::encryptString((string) $order->id);
        $response = $this->get(route('payment.sofizpay.cib.return', ['eid' => $eid]));

        $response->assertRedirect(route('payment.success', ['encrypted_order_id' => $eid]));

        $order->refresh();
        $this->assertEquals('sending', $order->status, 'Order should stay in sending and wait for Digiflazz webhook');
    }

    /**
     * @return array<string, mixed>
     */
    private function paidCheckPayload(string $amount, string $destinationAccount): array
    {
        return [
            'respCode' => '00',
            'errorCode' => '0',
            'orderStatus' => '2',
            'Amount' => $amount,
            'destination_account' => $destinationAccount,
        ];
    }
}
