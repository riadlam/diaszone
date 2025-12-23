<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerCancelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_cancel_pending_order_and_row_is_not_deleted()
    {
        $seller = Seller::factory()->create([
            'website_enabled' => true,
            'wallet_balance' => 2000,
        ]);

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Test pack',
            'code' => 'TP1',
            'diamonds' => 50,
            'price' => 5,
            'price_dzd' => 600,
            'base_price_dzd' => 500,
            'price_usd' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
            'original_price' => 600,
            'final_price' => 600,
            'seller_cost' => 500,
            'seller_profit' => 100,
            'payment_method' => 'baridimob',
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->deleteJson(route('seller.orders.delete', ['orderNumber' => $order->order_number]));
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // ensure entry remains
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
