<?php

namespace Tests\Feature;

use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\AdminFinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardFinanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'status' => 'active']);
    }

    private function pack(array $overrides = []): DiamondPack
    {
        return DiamondPack::create(array_merge([
            'game_type' => 'mobilelegends',
            'name' => 'Mobile Legends 172 Diamonds',
            'code' => 'mlbb-172dias',
            'diamonds' => 156,
            'bonus_diamonds' => 16,
            'price' => 40000,
            'base_price_dzd' => 200,
            'price_dzd' => 300,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    public function test_admin_dashboard_shows_finance_and_recent_order_widgets(): void
    {
        $admin = $this->admin();
        $pack = $this->pack();

        Order::create([
            'order_number' => 'ORD-DASHBOARD-1',
            'user_id' => $admin->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'payment_method' => 'baridimob',
            'final_price' => 900,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\DashboardStatsWidget::class)
            ->assertSuccessful()
            ->assertSee('Revenue this month');

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\RecentOrdersWidget::class)
            ->assertSuccessful()
            ->assertSee('ORD-DASHBOARD-1');
    }

    public function test_finance_service_counts_revenue_cost_and_profit_from_deliveries(): void
    {
        $pack = $this->pack();
        $order = Order::create([
            'order_number' => 'ORD-FIN-1',
            'user_id' => User::factory()->create()->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'payment_method' => 'baridimob',
            'final_price' => 900,
            'quantity' => 1,
        ]);

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-FIN-1',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-FIN-2',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        $finance = app(AdminFinanceService::class);
        $order->load(['digiflazzStatuses.diamondPack', 'diamondPack']);

        $this->assertSame(900.0, $finance->orderRevenue($order));
        $this->assertSame(400.0, $finance->orderCost($order));
        $this->assertSame(500.0, $finance->orderProfit($order));
        $this->assertSame(2, $finance->orderDeliveriesCount($order));
    }

    public function test_finance_page_shows_filtered_summary_and_orders(): void
    {
        $admin = $this->admin();
        $pack = $this->pack();

        $order = Order::create([
            'order_number' => 'ORD-FIN-PAGE',
            'user_id' => $admin->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'payment_method' => 'baridimob',
            'final_price' => 600,
            'created_at' => now(),
        ]);

        DigiflazzStatus::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-FIN-PAGE',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\FinanceReport::class)
            ->assertSuccessful()
            ->assertSee('Gross revenue')
            ->assertSee('Daily breakdown')
            ->assertSee('ORD-FIN-PAGE')
            ->assertSee('600 DZD');
    }

    public function test_finance_page_requires_admin(): void
    {
        $this->actingAs(User::factory()->create(['status' => 'active']))
            ->get('/admin/finance')
            ->assertForbidden();
    }
}
