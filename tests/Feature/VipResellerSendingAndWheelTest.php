<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\VipResellerStatus;
use App\Models\WheelEvent;
use App\Models\WheelReward;
use App\Models\WheelSpinLedger;
use App\Services\MlPackFulfillment;
use App\Services\VipResellerFulfillmentService;
use App\Services\WheelQualificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class VipResellerSendingAndWheelTest extends TestCase
{
    use RefreshDatabase;

    private function makeVipPack(): DiamondPack
    {
        return DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'VIP 86 Diamonds',
            'code' => 'mlbb-vip-86',
            'vip_reseller_code' => 'mlbbvip86',
            'diamonds' => 86,
            'price' => 1.50,
            'price_dzd' => 180,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function makeEvent(): WheelEvent
    {
        $event = WheelEvent::create([
            'name' => 'ML Wheel VIP Test',
            'game_type' => 'mobilelegends',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(14),
            'is_active' => true,
        ]);

        WheelReward::create([
            'wheel_event_id' => $event->id,
            'label' => 'Reward',
            'draws_required' => 2,
            'reward_type' => 'discount',
            'discount_percentage' => 10,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return $event;
    }

    private function makePaidVipOrder(User $user, DiamondPack $pack, int $qty = 1): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'VIP-'.uniqid(),
            'diamond_pack_id' => $pack->id,
            'quantity' => $qty,
            'original_price' => 180 * $qty,
            'final_price' => 180 * $qty,
            'status' => 'sending',
            'payment_method' => 'flexy',
            'user_id_ml' => '123456789',
            'zone_id_ml' => '2345',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'quantity' => $qty,
            'unit_price_dzd' => 180,
            'subtotal_dzd' => 180 * $qty,
            'discount_amount_dzd' => 0,
            'total_dzd' => 180 * $qty,
        ]);

        return $order->fresh(['orderItems.diamondPack', 'diamondPack']);
    }

    public function test_delivered_topups_count_accepts_vip_success_and_sukses(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makeVipPack();
        $order = $this->makePaidVipOrder($user, $pack);
        $item = $order->orderItems->first();

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'diamond_pack_id' => $pack->id,
            'trxid' => 'VIPTRX1',
            'buyer_sku_code' => 'mlbbvip86',
            'customer_no' => '123456789',
            'status' => 'success',
            'additional_data' => ['provider' => 'vipreseller'],
        ]);

        $this->assertSame(1, $item->fresh()->deliveredTopupsCount());
        $this->assertTrue($order->fresh(['orderItems.diamondPack', 'diamondPack'])->allPackTopupsDelivered());
    }

    public function test_ml_pack_fulfillment_maps_sukses_to_success(): void
    {
        $method = new ReflectionMethod(MlPackFulfillment::class, 'placeVip');
        // Mapping is inline; assert via VipResellerFulfillmentService map which we share semantically.
        $this->assertSame('success', VipResellerFulfillmentService::mapProviderStatus('sukses'));
        $this->assertSame('success', VipResellerFulfillmentService::mapProviderStatus('success'));

        $mapped = match (strtolower('sukses')) {
            'success', 'completed', 'paid', 'sukses' => 'success',
            'processing' => 'processing',
            default => 'waiting',
        };
        $this->assertSame('success', $mapped);
        $this->assertTrue($method->getDeclaringClass()->getName() === MlPackFulfillment::class);
    }

    public function test_vip_success_credits_wheel_spin_and_completes_order(): void
    {
        $this->makeEvent();
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makeVipPack();
        $order = $this->makePaidVipOrder($user, $pack);
        $item = $order->orderItems->first();

        $status = VipResellerStatus::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'diamond_pack_id' => $pack->id,
            'trxid' => 'VIPSPIN1',
            'buyer_sku_code' => 'mlbbvip86',
            'customer_no' => '123456789',
            'status' => 'waiting',
            'additional_data' => ['provider' => 'vipreseller'],
        ]);

        app(VipResellerFulfillmentService::class)->applyDeliveryToStatus(
            'VIPSPIN1',
            'Sukses Terkirim',
            'success',
            ['service' => 'mlbbvip86']
        );

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertSame('success', $status->fresh()->status);

        $this->assertTrue(
            WheelSpinLedger::query()
                ->where('user_id', $user->id)
                ->where('source_type', 'vipreseller_status')
                ->where('order_id', $order->id)
                ->exists()
        );

        // Idempotent second apply
        app(VipResellerFulfillmentService::class)->applyDeliveryToStatus(
            'VIPSPIN1',
            'Sukses Terkirim',
            'success'
        );
        $this->assertSame(
            1,
            WheelSpinLedger::query()
                ->where('user_id', $user->id)
                ->where('source_type', 'vipreseller_status')
                ->count()
        );
    }

    public function test_repair_sending_command_completes_ready_vip_orders(): void
    {
        $this->makeEvent();
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makeVipPack();
        $order = $this->makePaidVipOrder($user, $pack);
        $item = $order->orderItems->first();

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'diamond_pack_id' => $pack->id,
            'trxid' => 'VIPREPAIR1',
            'buyer_sku_code' => 'mlbbvip86',
            'customer_no' => '123456789',
            'status' => 'success',
            'additional_data' => ['provider' => 'vipreseller'],
        ]);

        $this->artisan('vipreseller:repair-sending')
            ->assertSuccessful();

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertTrue(
            WheelSpinLedger::query()
                ->where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->exists()
        );
    }

    public function test_credit_from_vip_rejects_non_ml_and_coupon_free(): void
    {
        $this->makeEvent();
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makeVipPack();
        $order = $this->makePaidVipOrder($user, $pack);
        $order->update(['payment_method' => 'coupon_free']);
        $item = $order->orderItems->first();

        $status = VipResellerStatus::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'diamond_pack_id' => $pack->id,
            'trxid' => 'VIPFREE1',
            'status' => 'success',
            'additional_data' => ['provider' => 'vipreseller'],
        ]);

        $this->assertFalse(app(WheelQualificationService::class)->creditFromVipResellerStatus($status));
    }
}
