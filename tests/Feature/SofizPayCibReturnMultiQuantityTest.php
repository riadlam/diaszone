<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SofizPayCibTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SofizPayCibReturnMultiQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sofizpay_return_triggers_multiple_digiflazz_calls_for_quantity(): void
    {
        config([
            'services.digiflazz.username' => 'fakeuser',
            'services.digiflazz.sign' => 'fakesign',
            'services.sofizpay.enabled' => true,
            'services.sofizpay.sandbox' => false,
            'services.sofizpay.base_url' => 'https://sofizpay.com',
            'services.sofizpay.merchant_account' => 'GA_MULTI_TEST',
        ]);

        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 555]], 200),
            'https://sofizpay.com/cib-transaction-check*' => Http::response($this->paidCheckPayload('110.00', 'GA_MULTI_TEST'), 200),
        ]);

        $this->mock(\App\Services\VipResellerService::class, function ($mock) {
            $mock->shouldReceive('checkNickname')->once()->andReturn(['result' => true, 'data' => 'ValidNick']);
        });

        $this->mock(\App\Services\DigiflazzService::class, function ($mock) {
            $mock->shouldReceive('placeOrderWithRefId')->twice()->andReturnUsing(function ($pack, $order, $refId) {
                static $i = 0;
                $i++;
                $trx = 'trx-' . $i;

                DigiflazzStatus::updateOrCreate(
                    ['ref_id' => $refId],
                    [
                        'order_id' => $order->id,
                        'ref_id' => $refId,
                        'trxid' => $trx,
                        'buyer_sku_code' => $pack->code,
                        'customer_no' => ($order->user_id_ml ?? ''),
                        'rc' => '03',
                        'status' => 'Pending',
                        'message' => 'Mocked',
                        'price' => 11601,
                        'event' => 'create',
                        'additional_data' => [],
                    ]
                );

                return [
                    'result' => true,
                    'data' => [
                        'trxid' => $trx,
                        'buyer_sku_code' => $pack->code,
                        'customer_no' => ($order->user_id_ml ?? ''),
                        'status' => 'waiting',
                    ],
                    'ref_id' => $refId,
                    'message' => 'ok',
                ];
            });
        });

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Weekly Pass 2x',
            'code' => 'mlbb-pass-2',
            'diamonds' => 55,
            'price' => 1.00,
            'price_dzd' => 55,
            'is_active' => true,
            'special_quantity' => 2,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-PASS-2',
            'status' => 'pending_bmccp',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '205762973',
            'zone_id_ml' => '4048',
            'quantity' => 2,
            'final_price' => 110,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'quantity' => 2,
            'unit_price_dzd' => 55,
            'unit_price_usd' => 1,
            'discount_percentage' => 0,
            'subtotal_dzd' => 110,
            'discount_amount_dzd' => 0,
            'total_dzd' => 110,
        ]);

        $spf = SofizPayCibTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'txn-multi-1',
            'cib_order_number' => '2848000002',
            'amount_expected' => 110.00,
            'status' => 'pending',
            'create_response' => [],
        ]);
        $order->update(['sofizpay_cib_transaction_id' => $spf->id]);

        $eid = Crypt::encryptString((string) $order->id);
        $this->get(route('payment.sofizpay.cib.return', ['eid' => $eid]))
            ->assertRedirect(route('payment.success', ['encrypted_order_id' => $eid]));

        $order->refresh();
        $this->assertEquals('sending', $order->status);

        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 'trx-1']);
        $this->assertDatabaseHas('digiflazz_statuses', ['order_id' => $order->id, 'trxid' => 'trx-2']);
        $this->assertEquals(2, DigiflazzStatus::where('order_id', $order->id)->count());
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
