<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ListMyOrdersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mine_returns_only_authenticated_user_orders(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Test Pack',
            'code' => 'test-pack',
            'diamonds' => 86,
            'price' => 1,
            'price_dzd' => 100,
            'is_active' => true,
        ]);

        $orderA = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $userA->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_bmccp',
            'user_id_ml' => '1',
            'zone_id_ml' => '2',
            'original_price' => 100,
            'final_price' => 100,
        ]);

        Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $userB->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_bmccp',
            'user_id_ml' => '9',
            'zone_id_ml' => '9',
            'original_price' => 200,
            'final_price' => 200,
        ]);

        $response = $this->actingAs($userA)->getJson(route('api.orders.mine'));

        $response->assertOk()->assertJson(['success' => true]);
        $ids = collect($response->json('orders'))->pluck('order.id')->all();
        $this->assertContains($orderA->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_get_by_encrypted_id_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Pack',
            'code' => 'p1',
            'diamonds' => 10,
            'price' => 1,
            'price_dzd' => 50,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $owner->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'user_id_ml' => '1',
            'zone_id_ml' => '1',
            'original_price' => 50,
            'final_price' => 50,
        ]);

        $encrypted = Crypt::encryptString((string) $order->id);

        $this->actingAs($other)->postJson(route('api.orders.get-by-encrypted-id'), [
            'encrypted_order_id' => $encrypted,
        ])->assertStatus(403);
    }
}
