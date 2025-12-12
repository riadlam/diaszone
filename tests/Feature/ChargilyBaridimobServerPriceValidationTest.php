<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ChargilyBaridimobServerPriceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_baridimob_uses_server_calculated_final_price_for_multi_quantity_offers()
    {
        // Create a pack with special_quantity = 3
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

        // Create an order with tampered final_price (set to small value)
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'diamond_pack_id' => $pack->id,
            'quantity' => 3,
            'status' => 'pending',
            'user_id_ml' => '12345',
            'zone_id_ml' => '4048',
            // Tampered
            'original_price' => 10,
            'final_price' => 10,
        ]);

        // Expected server final price = price_dzd * quantity = 100 * 3 = 300 (no discount)
        $expected = 100 * 3;

        // Mock Chargily service to assert createCheckout called with amount==expected
        $mock = \Mockery::mock(\App\Services\ChargilyPayV2Service::class);
        $mock->shouldReceive('hasCredentials')->andReturn(true);
        $mock->shouldReceive('createCheckout')->once()->withArgs(function ($checkoutData) use ($expected) {
            // Ensure amount uses server-side calculated final price
            return isset($checkoutData['amount']) && (int) round($checkoutData['amount']) === (int) $expected;
        })->andReturn([
            'success' => true,
            "checkout_id" => 'ck_test_server_1',
            "checkout_url" => 'https://pay.chargily.net/checkout/ck_test_server_1',
            'data' => [
                'id' => 'ck_test_server_1',
                'status' => 'pending',
                'checkout_url' => 'https://pay.chargily.net/checkout/ck_test_server_1'
            ]
        ]);
        $this->app->instance(\App\Services\ChargilyPayV2Service::class, $mock);

        // Start payment process with encrypted order id
        $encrypted = Crypt::encryptString($order->id);
        $response = $this->postJson(route('baridimob.webhook') /* invalid, it's the webhook route - fix: use processBaridimobPayment route */, ['encrypted_order_id' => $encrypted]);

        // Oops: We must call the actual process endpoint. Fix: POST to /api/baridimob/process
        $response = $this->postJson(route('api.baridimob.process'), ['encrypted_order_id' => $encrypted]);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('chargily_status', [
            'amount' => $expected,
        ]);
    }
}
