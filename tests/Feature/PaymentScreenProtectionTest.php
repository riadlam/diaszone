<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\SofizPayCibTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PaymentScreenProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Protect Pack',
            'code' => 'protect-'.uniqid(),
            'diamonds' => 100,
            'price' => 1.00,
            'price_dzd' => 228,
            'is_active' => true,
        ]);

        return Order::create(array_merge([
            'order_number' => Order::generateOrderNumber(),
            'diamond_pack_id' => $pack->id,
            'quantity' => 1,
            'status' => 'pending_bmccp',
            'payment_method' => 'bmccp',
            'user_id_ml' => '1268237601',
            'zone_id_ml' => '15159',
            'original_price' => 456,
            'final_price' => 228,
        ], $overrides));
    }

    public function test_pending_bmccp_order_can_open_baridimob_form(): void
    {
        $order = $this->makeOrder();
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->get(route('baridimob-form', ['encrypted_order_id' => $encrypted]))
            ->assertOk();
    }

    public function test_completed_order_cannot_open_baridimob_form(): void
    {
        $order = $this->makeOrder(['status' => 'completed']);
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->get(route('baridimob-form', ['encrypted_order_id' => $encrypted]))
            ->assertRedirect(route('payment.success', ['encrypted_order_id' => $encrypted]));
    }

    public function test_cancelled_order_cannot_open_baridimob_form(): void
    {
        $order = $this->makeOrder(['status' => 'cancelled']);
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->get(route('baridimob-form', ['encrypted_order_id' => $encrypted]))
            ->assertRedirect(route('select-payment'));
    }

    public function test_process_rejects_completed_order(): void
    {
        $order = $this->makeOrder(['status' => 'completed']);
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->postJson(route('api.baridimob.process'), ['encrypted_order_id' => $encrypted])
            ->assertStatus(409)
            ->assertJson(['success' => false]);
    }

    public function test_process_rejects_when_sofizpay_already_paid(): void
    {
        $order = $this->makeOrder(['status' => 'pending_bmccp']);
        $tx = SofizPayCibTransaction::create([
            'order_id' => $order->id,
            'transaction_id' => 'paid-tx-1',
            'amount_expected' => 228,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $order->update(['sofizpay_cib_transaction_id' => $tx->id]);

        $encrypted = Crypt::encryptString((string) $order->id);

        $this->postJson(route('api.baridimob.process'), ['encrypted_order_id' => $encrypted])
            ->assertStatus(409)
            ->assertJson(['success' => false]);
    }

    public function test_crypto_form_rejects_completed_order(): void
    {
        $order = $this->makeOrder([
            'status' => 'completed',
            'payment_method' => 'cryptocurrency',
        ]);
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->get(route('crypto-form', ['encrypted_order_id' => $encrypted]))
            ->assertRedirect(route('payment.success', ['encrypted_order_id' => $encrypted]));
    }
}
