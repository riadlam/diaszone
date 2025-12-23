<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontProcessOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_seller_order_deducts_wallet_on_success()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 2000.00,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Pack A',
            'code' => 'PA100',
            'diamonds' => 50,
            'bonus_diamonds' => 0,
            'price' => 5.00,
            'price_dzd' => 600.00,
            'base_price_dzd' => 500.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
            'wallet_deducted' => false,
            'seller_cost' => 500.00,
            'seller_profit' => 100.00,
            'original_price' => 600.00,
            'final_price' => 600.00,
        ]);

        $result = \App\Http\Controllers\Seller\SellerStorefrontController::processSellerOrder($order);

        $this->assertTrue($result, 'processSellerOrder should return true on success');

        $seller->refresh();
        $this->assertEquals(1500.00, (float) $seller->wallet_balance, 'Seller wallet should be deducted by seller_cost');

        $order->refresh();
        $this->assertEquals(true, (bool) $order->wallet_deducted, 'Order.wallet_deducted should be set to true');
    }

    public function test_process_seller_order_fails_when_insufficient_funds()
    {
        $seller = Seller::factory()->create([
            'wallet_balance' => 100.00,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Pack B',
            'code' => 'PB100',
            'diamonds' => 50,
            'bonus_diamonds' => 0,
            'price' => 5.00,
            'price_dzd' => 600.00,
            'base_price_dzd' => 500.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
            'wallet_deducted' => false,
            'seller_cost' => 500.00,
            'seller_profit' => 100.00,
            'original_price' => 600.00,
            'final_price' => 600.00,
        ]);

        $result = \App\Http\Controllers\Seller\SellerStorefrontController::processSellerOrder($order);

        $this->assertFalse($result, 'processSellerOrder should return false if seller has insufficient balance');

        $seller->refresh();
        $this->assertEquals(100.00, (float) $seller->wallet_balance, 'Seller wallet should remain unchanged');

        $order->refresh();
        $this->assertEquals(false, (bool) $order->wallet_deducted, 'Order.wallet_deducted should remain false');
    }
}
