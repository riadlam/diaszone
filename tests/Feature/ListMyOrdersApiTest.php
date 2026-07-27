<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\OrderItem;
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

    public function test_mine_includes_order_items_quantities_and_totals(): void
    {
        $user = User::factory()->create();

        $packA = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => '86 Diamonds',
            'code' => 'mlbb-86',
            'diamonds' => 86,
            'price' => 1.5,
            'price_usd' => 1.5,
            'price_dzd' => 200,
            'is_active' => true,
        ]);
        $packB = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => '172 Diamonds',
            'code' => 'mlbb-172',
            'diamonds' => 172,
            'price' => 3,
            'price_usd' => 3,
            'price_dzd' => 400,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $user->id,
            'diamond_pack_id' => $packA->id,
            'status' => 'completed',
            'user_id_ml' => '1537832294',
            'zone_id_ml' => '4441',
            'original_price' => 1000,
            'final_price' => 1000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $packA->id,
            'quantity' => 3,
            'unit_price_dzd' => 200,
            'unit_price_usd' => 1.5,
            'discount_percentage' => 0,
            'subtotal_dzd' => 600,
            'discount_amount_dzd' => 0,
            'total_dzd' => 600,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $packB->id,
            'quantity' => 1,
            'unit_price_dzd' => 400,
            'unit_price_usd' => 3,
            'discount_percentage' => 0,
            'subtotal_dzd' => 400,
            'discount_amount_dzd' => 0,
            'total_dzd' => 400,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.orders.mine'));
        $response->assertOk();

        $payload = collect($response->json('orders'))->firstWhere('order.id', $order->id);
        $this->assertNotNull($payload);
        $this->assertSame(1000.0, (float) $payload['order']['amount_dzd']);
        $this->assertCount(2, $payload['order']['order_items']);
        $this->assertSame(3, (int) $payload['order']['order_items'][0]['quantity']);
        $this->assertSame(1, (int) $payload['order']['order_items'][1]['quantity']);
        $this->assertSame('86 Diamonds', $payload['order']['order_items'][0]['diamond_pack']['name']);
        $this->assertSame('172 Diamonds', $payload['order']['order_items'][1]['diamond_pack']['name']);
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
