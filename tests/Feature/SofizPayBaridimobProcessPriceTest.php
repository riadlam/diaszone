<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SofizPayBaridimobProcessPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_baridimob_process_uses_server_calculated_amount_for_special_quantity_pack(): void
    {
        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Weekly Pass 3x',
            'code' => 'mlbb-pass-3',
            'diamonds' => 55,
            'price' => 1.00,
            'price_dzd' => 100,
            'is_active' => true,
            'special_quantity' => 3,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'diamond_pack_id' => $pack->id,
            'quantity' => 3,
            'status' => 'pending',
            'user_id_ml' => '12345',
            'zone_id_ml' => '4048',
            'original_price' => 10,
            'final_price' => 10,
        ]);

        $expected = 100 * 3;

        config([
            'services.sofizpay.enabled' => true,
            'services.sofizpay.sandbox' => false,
            'services.sofizpay.base_url' => 'https://sofizpay.com',
            'services.sofizpay.merchant_account' => 'GA_PRICE_TEST',
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) use ($expected) {
            if (! str_contains($request->url(), 'make-cib-transaction')) {
                return Http::response('unexpected url', 500);
            }
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $q);
            if (($q['amount'] ?? null) !== number_format($expected, 2, '.', '')) {
                return Http::response(['success' => false, 'message' => 'amount mismatch'], 422);
            }

            return Http::response([
                'success' => true,
                'transaction_id' => 'fcd61bc4-2b79-45a0-a046-8ac2526a1ebd',
                'cib_transaction_id' => 2848342703,
                'payment_url' => 'https://cib.satim.dz/payment/merchants/SATIM/payment_ar.html?mdOrder=test',
                'amount' => (string) $expected,
                'status' => 'pending_user_transfer_start',
                'cib_response' => [
                    'errorCode' => '0',
                    'orderId' => 'HckzGfkgJcZe3UBFEKNI',
                    'formUrl' => 'https://cib.satim.dz/payment/merchants/SATIM/payment_ar.html?mdOrder=HckzGfkgJcZe3UBFEKNI',
                ],
            ], 200);
        });

        $encrypted = Crypt::encryptString((string) $order->id);
        $response = $this->postJson(route('api.baridimob.process'), ['encrypted_order_id' => $encrypted]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('sofizpay_cib_transactions', [
            'order_id' => $order->id,
            'amount_expected' => $expected,
        ]);
    }
}
