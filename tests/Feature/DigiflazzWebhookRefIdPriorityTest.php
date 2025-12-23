<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Order;
use App\Models\DiamondPack;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookRefIdPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_prefers_ref_id_over_customer_no()
    {
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=testsecretref');
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);
        Http::fake(['https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 555]], 200)]);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-55dias', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);

        // Create two orders with the same customer identifier
        $target = Order::create(['order_number' => 'ORD-TARGET', 'status' => 'pending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048', 'created_at' => now()->subMinutes(10)]);
        $other = Order::create(['order_number' => 'ORD-OTHER', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048', 'created_at' => now()->subMinutes(1)]);

        // Webhook indicates success for the target order via ref_id, but customer_no would match the other order
        $payload = ['data' => ['ref_id' => 'order-' . $target->id . '-gcRG4NvE', 'customer_no' => '2057629734048', 'status' => 'Sukses', 'rc' => '00', 'buyer_last_saldo' => 326832]];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, getenv('DIGIFLAZZ_WEBHOOK_SECRET'));

        $this->withHeaders(['Content-Type' => 'application/json', 'X-Hub-Signature' => $sig, 'X-Digiflazz-Event' => 'update', 'User-Agent' => 'Digiflazz-Hookshot'])
             ->postJson(route('digiflazz.webhook'), $payload)
             ->assertStatus(200)->assertJson(['ok' => true]);

        $target->refresh();
        $other->refresh();

        // The webhook must attach and mark the order referred by ref_id as completed
        $this->assertEquals('completed', $target->status);
        // The other order (that would match by customer_no) should remain sending
        $this->assertEquals('sending', $other->status);

        $this->assertDatabaseHas('digiflazz_statuses', ['ref_id' => 'order-' . $target->id . '-gcRG4NvE', 'order_id' => $target->id]);
    }
}
