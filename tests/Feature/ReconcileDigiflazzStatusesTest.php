<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;

class ReconcileDigiflazzStatusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_reparents_and_applies_status()
    {
        config(['telegram.bot_token' => 'fake-token', 'telegram.chat_id' => '1234', 'telegram.api_url' => 'https://api.telegram.org/bot/']);

        $pack = DiamondPack::create(['game_type' => 'mobilelegends', 'name' => 'Test Pack', 'code' => 'mlbb-55dias', 'diamonds' => 55, 'price' => 1.00, 'price_dzd' => 55, 'is_active' => true]);

        $target = Order::create(['order_number' => 'ORD-TARGET', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);
        $other = Order::create(['order_number' => 'ORD-OTHER', 'status' => 'sending', 'diamond_pack_id' => $pack->id, 'user_id_ml' => '205762973', 'zone_id_ml' => '4048']);

        // Create a DigiflazzStatus that references target via ref_id but is attached to other
        $status = DigiflazzStatus::create([
            'order_id' => $other->id,
            'ref_id' => 'order-' . $target->id . '-gcRG4NvE',
            'trxid' => null,
            'buyer_sku_code' => 'mlbb-55dias',
            'customer_no' => '2057629734048',
            'rc' => '00',
            'status' => 'Sukses',
            'message' => 'Transaksi Sukses',
            'price' => 11601,
        ]);

        $this->artisan('digiflazz:reconcile-statuses --limit=10')->assertExitCode(0);

        $status->refresh();
        $target->refresh();
        $other->refresh();

        $this->assertEquals($target->id, $status->order_id);
        // After applying 'Sukses', target should be completed
        $this->assertEquals('completed', $target->status);
        // other should remain unchanged
        $this->assertEquals('sending', $other->status);
    }
}
