<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzCustomerNoTest extends TestCase
{
    use RefreshDatabase;
    public function test_mobile_legends_customer_no_is_concatenated()
    {
        // Setup config
        config(['services.digiflazz.username' => 'testuser', 'services.digiflazz.sign' => 'testsign']);

        // Create pack and order
        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'code' => 'mlbb-55dias', 'name' => 'Test Pack', 'diamonds' => 55, 'price' => 5.00, 'price_dzd' => 1300, 'is_active' => true]);
        $order = Order::create(['order_number' => Order::generateOrderNumber(), 'status' => 'pending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);

        Http::fake(function ($request) use ($pack, $order) {
            $body = $request->data();
            // Expect concatenated customer_no
            $this->assertEquals('2057629734048', $body['customer_no']);
            return Http::response(['data' => ['status' => 'SUCCESS', 'trxid' => 'tx-123']], 200);
        });

        $service = app(\App\Services\DigiflazzService::class);
        $result = $service->placeOrder($pack, $order);

        $this->assertTrue($result['result']);
    }
}
