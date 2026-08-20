<?php

namespace Tests\Feature;

use App\Filament\Resources\FlashSales\Pages\CreateFlashSaleOffer;
use App\Models\DiamondPack;
use App\Models\FlashSaleOffer;
use App\Models\FlashSaleOfferItem;
use App\Models\Order;
use App\Models\User;
use App\Services\FlashSaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FlashSaleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makePack(array $overrides = []): DiamondPack
    {
        return DiamondPack::create(array_merge([
            'game_type' => 'mobilelegends',
            'name' => 'Weekly Diamond Pass',
            'code' => 'mlbb-weekly-'.uniqid(),
            'diamonds' => 0,
            'price' => 2.00,
            'price_dzd' => 400,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function makeOffer(DiamondPack $pack, array $overrides = [], int $qty = 3): FlashSaleOffer
    {
        $offer = FlashSaleOffer::create(array_merge([
            'name' => 'Weekly Diamond Pass 3x',
            'game_type' => 'mobilelegends',
            'image_path' => 'flash-sale-images/test-pack.webp',
            'original_price_dzd' => 1200,
            'sale_price_dzd' => 999,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2),
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));

        FlashSaleOfferItem::create([
            'flash_sale_offer_id' => $offer->id,
            'diamond_pack_id' => $pack->id,
            'quantity' => $qty,
            'sort_order' => 0,
        ]);

        return $offer->fresh('items');
    }

    public function test_admin_can_create_flash_sale_with_multiple_products(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $packA = $this->makePack(['name' => 'Weekly', 'code' => 'ml-w1']);
        $packB = $this->makePack(['name' => '55 Dias', 'code' => 'ml-55', 'price_dzd' => 55]);
        $image = UploadedFile::fake()->create('flash-pack.jpg', 120, 'image/jpeg');

        Livewire::actingAs($admin)
            ->test(CreateFlashSaleOffer::class)
            ->fillForm([
                'name' => 'Bundle Deal',
                'game_type' => 'mobilelegends',
                'original_price_dzd' => 1500,
                'sale_price_dzd' => 1100,
                'starts_at' => now()->subMinute()->format('Y-m-d H:i'),
                'ends_at' => now()->addDay()->format('Y-m-d H:i'),
                'is_active' => true,
                'sort_order' => 0,
                'image_path' => [$image],
            ])
            ->set('data.items', [
                ['diamond_pack_id' => $packA->id, 'quantity' => 2, 'sort_order' => 0],
                ['diamond_pack_id' => $packB->id, 'quantity' => 1, 'sort_order' => 1],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $offer = FlashSaleOffer::where('name', 'Bundle Deal')->first();
        $this->assertNotNull($offer);
        $this->assertNotEmpty($offer->image_path);
        $this->assertStringContainsString('flash-sale-images', $offer->image_path);
        $this->assertCount(2, $offer->items);
    }

    public function test_active_offers_appear_on_home_regardless_of_dates(): void
    {
        $pack = $this->makePack();
        $live = $this->makeOffer($pack, [
            'name' => 'Live Bundle',
            'image_path' => 'flash-sale-images/live-card.webp',
        ]);
        // Dates in the past must not hide an active offer anymore.
        $this->makeOffer($pack, [
            'name' => 'Dated Bundle',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(),
            'is_active' => true,
        ]);
        $this->makeOffer($pack, [
            'name' => 'Inactive Bundle',
            'is_active' => false,
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Live Bundle', false);
        $response->assertSee('Dated Bundle', false);
        $response->assertSee('data-flash-sale', false);
        $response->assertSee('data-flash-countdown', false);
        $response->assertSee('/media/flash-sale-images/live-card.webp', false);
        $response->assertDontSee('Inactive Bundle', false);
        $response->assertSee((string) $live->id, false);
        $response->assertDontSee('Stock left', false);
    }

    public function test_checkout_creates_order_items_and_flash_totals(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();
        $offer = $this->makeOffer($pack, [], 3);

        $this->actingAs($user)
            ->postJson(route('api.flash-sales.checkout', $offer), [
                'user_id' => '123456',
                'zone_id' => '7890',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['encrypted_order_id', 'redirect_url']);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->isFlashSale());
        $this->assertSame('Weekly Diamond Pass 3x', $order->flash_sale_name);
        $this->assertEquals(1200.0, (float) $order->original_price);
        $this->assertEquals(999.0, (float) $order->final_price);
        $this->assertSame('123456', $order->user_id_ml);
        $this->assertSame('7890', $order->zone_id_ml);
        $this->assertCount(1, $order->orderItems);
        $this->assertSame(3, (int) $order->orderItems->first()->quantity);
        $this->assertSame($pack->id, $order->orderItems->first()->diamond_pack_id);
    }

    public function test_select_payment_hides_flexy_for_flash_orders(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();
        $offer = $this->makeOffer($pack);
        $order = app(FlashSaleService::class)->createCheckoutOrder($offer, $user, [
            'user_id' => '1',
            'zone_id' => '2',
        ]);

        $encrypted = Crypt::encryptString((string) $order->id);

        $this->actingAs($user)
            ->get(route('select-payment', ['order_id' => $encrypted, 'flash' => 1]))
            ->assertOk()
            ->assertSee('Algerie Post', false)
            ->assertSee('Cryptocurrency', false)
            ->assertDontSee('value="flexy"', false)
            ->assertSee('Weekly Diamond Pass 3x', false);
    }

    public function test_prepare_payment_sets_baridimob_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();
        $offer = $this->makeOffer($pack);
        $order = app(FlashSaleService::class)->createCheckoutOrder($offer, $user, [
            'user_id' => '1',
            'zone_id' => '2',
        ]);
        $encrypted = Crypt::encryptString((string) $order->id);

        $this->actingAs($user)
            ->postJson(route('api.flash-sales.prepare-payment'), [
                'encrypted_order_id' => $encrypted,
                'payment_method' => 'bmccp',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertSame('bmccp', $order->payment_method);
        $this->assertSame('pending_bmccp', $order->status);
    }

    public function test_my_orders_payload_uses_flash_name_and_sale_price(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();
        $offer = $this->makeOffer($pack);
        app(FlashSaleService::class)->createCheckoutOrder($offer, $user, [
            'user_id' => '11',
            'zone_id' => '22',
        ]);

        $this->actingAs($user)
            ->getJson(route('api.orders.mine'))
            ->assertOk()
            ->assertJsonPath('orders.0.order.is_flash_sale', true)
            ->assertJsonPath('orders.0.order.flash_sale_name', 'Weekly Diamond Pass 3x')
            ->assertJsonPath('orders.0.order.amount_dzd', 999);
    }

    public function test_guest_cannot_checkout_flash_sale(): void
    {
        $pack = $this->makePack();
        $offer = $this->makeOffer($pack);

        $this->postJson(route('api.flash-sales.checkout', $offer), [
            'user_id' => '1',
            'zone_id' => '2',
        ])->assertUnauthorized();
    }
}
