<?php

namespace Tests\Feature;

use App\Filament\Resources\WheelEvents\Pages\CreateWheelEvent;
use App\Filament\Resources\WheelEvents\Pages\EventParticipants;
use App\Models\Coupon;
use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Item4GamerOrder;
use App\Models\Order;
use App\Models\User;
use App\Models\WheelClaim;
use App\Models\WheelEvent;
use App\Models\WheelReward;
use App\Models\WheelSpinLedger;
use App\Models\WheelUserProgress;
use App\Services\WheelProgressService;
use App\Services\WheelQualificationService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WheelEventFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makePack(): DiamondPack
    {
        return DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => '55 Diamonds',
            'code' => 'mlbb-55dias',
            'diamonds' => 55,
            'price' => 1.00,
            'price_dzd' => 55,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function makeEvent(array $overrides = []): WheelEvent
    {
        return WheelEvent::create(array_merge([
            'name' => 'ML Wheel Aug 2026',
            'game_type' => 'mobilelegends',
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ], $overrides));
    }

    private function makeRewards(WheelEvent $event, DiamondPack $pack): array
    {
        $first = WheelReward::create([
            'wheel_event_id' => $event->id,
            'label' => '55 Diamonds',
            'draws_required' => 2,
            'reward_type' => 'diamond_pack',
            'diamond_pack_id' => $pack->id,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $second = WheelReward::create([
            'wheel_event_id' => $event->id,
            'label' => '10% Off',
            'draws_required' => 3,
            'reward_type' => 'discount',
            'discount_percentage' => 10,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $second->eligiblePacks()->sync([$pack->id]);

        return [$first, $second];
    }

    /**
     * @return array<string, mixed>
     */
    private function wheelConfig(string $pageHtml): array
    {
        return json_decode(
            (string) preg_replace(
                '/.*<script type="application\/json" id="wheelConfig">(.*?)<\/script>.*/s',
                '$1',
                $pageHtml
            ),
            true
        );
    }

    private function creditSpins(User $user, WheelEvent $event, int $count): void
    {
        $service = app(WheelQualificationService::class);
        for ($i = 0; $i < $count; $i++) {
            $service->creditSpin(
                userId: $user->id,
                event: $event,
                sourceType: 'test',
                sourceKey: 'test-credit-'.$user->id.'-'.$i,
            );
        }
    }

    public function test_guest_cannot_spin_or_list_rewards(): void
    {
        $this->postJson(route('event.spin', 'mobilelegends'))
            ->assertStatus(401);

        $this->getJson(route('event.rewards', 'mobilelegends'))
            ->assertStatus(401);
    }

    public function test_non_mobile_legends_event_page_is_unavailable(): void
    {
        $this->get(route('event.show', 'freefire'))
            ->assertOk()
            ->assertSee(__('event.not_available'), false);
    }

    public function test_guest_sees_login_gate_on_mobile_legends(): void
    {
        $this->makeEvent();

        $this->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('wheel-stage', false)
            ->assertSee('wheelLoginModal', false)
            ->assertSee(__('event.login_modal_title'), false)
            ->assertSee(__('event.spin'), false);
    }

    public function test_upcoming_event_is_publicly_teased_only_when_no_event_is_active(): void
    {
        $upcoming = $this->makeEvent([
            'name' => 'September Starfall',
            'description' => 'New skins and diamond prizes are coming.',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(12),
            'background_path' => 'event-backgrounds/september-starfall.png',
        ]);

        $this->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee(__('event.next_event'), false)
            ->assertSee($upcoming->name, false)
            ->assertSee($upcoming->description, false)
            ->assertSee('data-next-event-countdown', false)
            ->assertSee('/storage/event-backgrounds/september-starfall.png', false)
            ->assertDontSee(__('event.login_required'), false);

        $active = $this->makeEvent([
            'name' => 'Active Wheel Event',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(2),
        ]);
        $this->makeRewards($active, $this->makePack());

        $this->actingAs(User::factory()->create())
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee(__('event.spin'), false)
            ->assertDontSee($upcoming->description, false)
            ->assertDontSee('class="next-event-countdown', false);
    }

    public function test_public_navigation_links_to_the_lucky_wheel(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('event.show', 'mobilelegends'), false)
            ->assertSee(__('nav.lucky_wheel'), false);
    }

    public function test_event_page_renders_fixed_backdrop_for_mobile_legends_only(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('event-backdrop', false)
            ->assertSee('/storage/event-backgrounds/mlbb-jujutsu-kaisen-skins.png', false);

        $this->actingAs($user)
            ->get(route('event.show', 'freefire'))
            ->assertOk()
            ->assertDontSee('event-backdrop', false);
    }

    public function test_event_backdrop_uses_admin_uploaded_background(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent(['background_path' => 'event-backgrounds/custom-art.png']);
        $this->makeRewards($event, $pack);

        $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('/storage/event-backgrounds/custom-art.png', false)
            ->assertDontSee('mlbb-jujutsu-kaisen-skins.png', false);
    }

    public function test_reward_track_and_wheel_hide_draw_counts(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $response = $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('55 Diamonds', false)
            ->assertSee('-10%', false);

        $this->assertStringNotContainsString('2 '.__('event.draws'), $response->getContent());
        $this->assertStringNotContainsString('3 '.__('event.draws'), $response->getContent());
    }

    public function test_wheel_adds_generated_no_prize_slices(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $content = $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee(__('event.no_win_label'), false)
            ->getContent();

        $config = $this->wheelConfig($content);

        $blanks = array_filter($config['segments'], fn (array $segment): bool => $segment['type'] === 'none');

        $this->assertGreaterThanOrEqual(8, count($config['segments']));
        $this->assertCount(count($config['segments']) - 2, $blanks);
        $this->assertSame(__('event.no_win_label'), reset($blanks)['label']);
    }

    public function test_wheel_uses_pack_icon_fallback_and_admin_icon_override(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        [$reward] = $this->makeRewards($event, $pack);

        $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('/storage/images_homepage/diaslow.webp', false)
            ->assertSee('wheelIconPreview', false);

        $reward->update(['image_paths' => [
            'wheel-reward-icons/custom.webp',
            'wheel-reward-icons/account-2.webp',
            'wheel-reward-icons/account-3.webp',
        ]]);

        $content = $this->actingAs($user)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('/storage/wheel-reward-icons/custom.webp', false)
            ->getContent();

        $segment = collect($this->wheelConfig($content)['segments'])
            ->firstWhere('reward_id', $reward->id);

        $this->assertSame(url('/storage/wheel-reward-icons/custom.webp'), $segment['icon']);
        $this->assertSame('cover', $segment['icon_fit']);
        $this->assertCount(3, $segment['gallery']);
        $this->assertSame(url('/storage/wheel-reward-icons/account-3.webp'), $segment['gallery'][2]);
    }

    public function test_digiflazz_success_credits_one_spin_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $order = Order::create([
            'order_number' => 'ORD-WHEEL-1',
            'user_id' => $user->id,
            'status' => 'sending',
            'diamond_pack_id' => $pack->id,
            'user_id_ml' => '111',
            'zone_id_ml' => '2222',
            'created_at' => now()->subHour(),
        ]);

        $status = DigiflazzStatus::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'ref-wheel-1',
            'trxid' => 'trx-wheel-1',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        $service = app(WheelQualificationService::class);
        $this->assertTrue($service->creditFromDigiflazzStatus($status));
        $this->assertFalse($service->creditFromDigiflazzStatus($status->fresh()));

        $progress = WheelUserProgress::where('user_id', $user->id)->first();
        $this->assertNotNull($progress);
        $this->assertSame(1, $progress->total_spins_earned);
        $this->assertSame(1, $progress->availableSpins());
        $this->assertSame(1, WheelSpinLedger::where('user_id', $user->id)->where('entry_type', 'credit')->count());
    }

    public function test_flexy_orders_do_not_credit_spins(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $order = Order::create([
            'order_number' => 'ORD-WHEEL-FLEXY',
            'user_id' => $user->id,
            'status' => 'completed',
            'diamond_pack_id' => $pack->id,
            'payment_method' => 'flexy',
            'created_at' => now()->subHour(),
        ]);

        $status = DigiflazzStatus::create([
            'order_id' => $order->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'ref-flexy',
            'trxid' => 'trx-flexy',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        $this->assertFalse(app(WheelQualificationService::class)->creditFromDigiflazzStatus($status));
        $this->assertDatabaseMissing('wheel_spin_ledger', ['user_id' => $user->id]);
    }

    public function test_milestone_draw_unlocks_pack_claim_code_then_discount_coupon(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        [$first, $second] = $this->makeRewards($event, $pack);
        $this->creditSpins($user, $event, 5);

        $this->actingAs($user);

        // First reward requires 2 draws
        $this->postJson(route('event.spin', 'mobilelegends'))->assertOk()->assertJson(['reward_unlocked' => false]);
        $unlock = $this->postJson(route('event.spin', 'mobilelegends'))
            ->assertOk()
            ->assertJson(['success' => true, 'reward_unlocked' => true]);

        $claimCode = $unlock->json('claim.claim_code');
        $this->assertNotEmpty($claimCode);
        $this->assertTrue($unlock->json('claim.is_contact_reward'));
        $this->assertDatabaseHas('wheel_claims', [
            'user_id' => $user->id,
            'wheel_reward_id' => $first->id,
            'reward_type' => 'diamond_pack',
            'claim_code' => $claimCode,
            'status' => 'unlocked',
        ]);

        // Next reward requires 3 draws → discount coupon
        $this->postJson(route('event.spin', 'mobilelegends'))->assertOk();
        $this->postJson(route('event.spin', 'mobilelegends'))->assertOk();
        $discountUnlock = $this->postJson(route('event.spin', 'mobilelegends'))
            ->assertOk()
            ->assertJsonPath('reward_unlocked', true)
            ->assertJsonPath('claim.is_discount_reward', true);

        $couponCode = $discountUnlock->json('claim.coupon_code') ?: $discountUnlock->json('claim.claim_code');
        $this->assertNotEmpty($couponCode);
        $this->assertDatabaseHas('coupons', ['code' => strtoupper($couponCode)]);
        $coupon = Coupon::findByCode($couponCode);
        $this->assertTrue($coupon->appliesToPackage('mobilelegends', $pack->id));
        $this->assertFalse($coupon->appliesToPackage('freefire', $pack->id));
    }

    public function test_wheel_spin_and_reward_win_send_telegram_notifications(): void
    {
        config([
            'telegram.bot_token' => 'fake-token',
            'telegram.chat_id' => '1234',
            'telegram.api_url' => 'https://api.telegram.org/bot/',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 123],
            ]),
        ]);

        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);
        $this->creditSpins($user, $event, 2);

        $this->actingAs($user)
            ->postJson(route('event.spin', 'mobilelegends'))
            ->assertOk()
            ->assertJsonPath('reward_unlocked', false);

        $this->actingAs($user)
            ->postJson(route('event.spin', 'mobilelegends'))
            ->assertOk()
            ->assertJsonPath('reward_unlocked', true);

        Http::assertSentCount(3);
        Http::assertSent(fn ($request): bool => str_contains($request['text'], 'Lucky Wheel Spin')
            && str_contains($request['text'], 'No prize'));
        Http::assertSent(fn ($request): bool => str_contains($request['text'], 'Lucky Wheel Reward Won')
            && str_contains($request['text'], '55 Diamonds')
            && str_contains($request['text'], 'Claim Code'));
    }

    public function test_order_telegram_message_shows_prior_successful_topups(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();

        $multiQuantity = Order::create([
            'order_number' => 'ORD-PREVIOUS-ONE',
            'user_id' => $user->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'quantity' => 3,
        ]);

        foreach (range(1, 3) as $index) {
            DigiflazzStatus::create([
                'order_id' => $multiQuantity->id,
                'diamond_pack_id' => $pack->id,
                'ref_id' => 'REF-ONE-'.$index,
                'status' => 'Sukses',
                'rc' => '00',
            ]);
        }

        // Still marked as sending, but the provider already delivered it.
        $sending = Order::create([
            'order_number' => 'ORD-PREVIOUS-TWO',
            'user_id' => $user->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'sending',
        ]);

        DigiflazzStatus::create([
            'order_id' => $sending->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-TWO',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        $failed = Order::create([
            'order_number' => 'ORD-PREVIOUS-FAILED',
            'user_id' => $user->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
        ]);

        DigiflazzStatus::create([
            'order_id' => $failed->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-FAILED',
            'status' => 'Gagal',
            'rc' => '01',
        ]);

        $item4gamer = Order::create([
            'order_number' => 'ORD-PREVIOUS-I4G',
            'user_id' => $user->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
        ]);

        Item4GamerOrder::create([
            'order_id' => $item4gamer->id,
            'diamond_pack_id' => $pack->id,
            'item4gamer_order_id' => 'I4G-1',
            'status' => 'completed',
            'quantity' => 2,
        ]);

        $current = Order::create([
            'order_number' => 'ORD-CURRENT',
            'user_id' => $user->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
        ]);

        $message = TelegramService::formatOrderMessage($current->load(['user', 'diamondPack']));

        $this->assertStringContainsString('Previous Successful Top-ups:</b> 6', $message);
    }

    public function test_rewards_endpoint_returns_only_own_claims(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        [$first] = $this->makeRewards($event, $pack);

        WheelClaim::create([
            'user_id' => $user->id,
            'wheel_event_id' => $event->id,
            'wheel_reward_id' => $first->id,
            'occurrence' => 1,
            'reward_type' => 'diamond_pack',
            'claim_code' => 'DZ-WHEEL-OWNCODE01',
            'status' => 'unlocked',
            'unlocked_at' => now(),
            'idempotency_key' => 'u'.$user->id.':r'.$first->id.':o1',
        ]);

        WheelClaim::create([
            'user_id' => $other->id,
            'wheel_event_id' => $event->id,
            'wheel_reward_id' => $first->id,
            'occurrence' => 1,
            'reward_type' => 'diamond_pack',
            'claim_code' => 'DZ-WHEEL-OTHER001',
            'status' => 'unlocked',
            'unlocked_at' => now(),
            'idempotency_key' => 'u'.$other->id.':r'.$first->id.':o1',
        ]);

        $this->actingAs($user)
            ->getJson(route('event.rewards', 'mobilelegends'))
            ->assertOk()
            ->assertJsonCount(1, 'claims')
            ->assertJsonPath('claims.0.claim_code', 'DZ-WHEEL-OWNCODE01');
    }

    public function test_progress_persists_across_event_windows(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $oldEvent = $this->makeEvent([
            'name' => 'Old',
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(10),
            'is_active' => false,
        ]);
        $this->makeRewards($oldEvent, $pack);
        $this->creditSpins($user, $oldEvent, 3);

        $progress = WheelUserProgress::where('user_id', $user->id)->first();
        $this->assertSame(3, $progress->availableSpins());

        $newEvent = $this->makeEvent(['name' => 'New']);
        $this->makeRewards($newEvent, $pack);

        $snapshot = app(WheelProgressService::class)->snapshot($user, $newEvent);
        $this->assertSame(3, $snapshot['available_spins']);
    }

    public function test_admin_can_create_event_with_unlimited_rewards(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $pack = $this->makePack();

        Livewire::actingAs($admin)
            ->test(CreateWheelEvent::class)
            ->fillForm([
                'name' => 'Admin Wheel',
                'game_type' => 'mobilelegends',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMonth()->format('Y-m-d H:i:s'),
                'is_active' => true,
            ])
            ->set('data.rewards', [
                'first' => [
                    'label' => '55 Diamonds',
                    'draws_required' => 6,
                    'reward_type' => 'diamond_pack',
                    'diamond_pack_id' => $pack->id,
                    'is_active' => true,
                ],
                'second' => [
                    'label' => 'Weekly',
                    'draws_required' => 20,
                    'reward_type' => 'diamond_pack',
                    'diamond_pack_id' => $pack->id,
                    'is_active' => true,
                ],
                'third' => [
                    'label' => '15% Off',
                    'draws_required' => 10,
                    'reward_type' => 'discount',
                    'discount_percentage' => 15,
                    'eligiblePacks' => [$pack->id],
                    'is_active' => true,
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = WheelEvent::where('name', 'Admin Wheel')->first();
        $this->assertNotNull($event);
        $this->assertSame(3, $event->rewards()->count());
        $this->assertSame($admin->id, $event->created_by);

        $discount = $event->rewards()->where('reward_type', 'discount')->first();
        $this->assertNotNull($discount);
        $this->assertNull($discount->diamond_pack_id);
        $this->assertSame([$pack->id], $discount->eligiblePacks()->pluck('diamond_packs.id')->all());
    }

    public function test_admin_cannot_activate_overlapping_event(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $pack = $this->makePack();
        $this->makeEvent();

        Livewire::actingAs($admin)
            ->test(CreateWheelEvent::class)
            ->fillForm([
                'name' => 'Overlapping Wheel',
                'game_type' => 'mobilelegends',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'is_active' => true,
            ])
            ->set('data.rewards', [
                'first' => [
                    'label' => '55 Diamonds',
                    'draws_required' => 6,
                    'reward_type' => 'diamond_pack',
                    'diamond_pack_id' => $pack->id,
                    'is_active' => true,
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['is_active']);

        $this->assertNull(WheelEvent::where('name', 'Overlapping Wheel')->first());
    }

    public function test_admin_panel_rejects_non_admin_users(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false, 'status' => 'active']))
            ->get('/admin/wheel-events')
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['is_admin' => true, 'status' => 'active']))
            ->get('/admin/wheel-events')
            ->assertSuccessful();
    }

    public function test_admin_can_browse_users_and_order_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $customer = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();
        $order = Order::create([
            'order_number' => 'ORD-ADMIN-DETAILS',
            'user_id' => $customer->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'payment_method' => 'baridimob',
            'user_id_ml' => '123456',
            'zone_id_ml' => '7890',
            'final_price' => 550,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertSuccessful()
            ->assertSee($customer->email);

        $this->actingAs($admin)
            ->get('/admin/users/'.$customer->id)
            ->assertSuccessful()
            ->assertSee($customer->email);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertSuccessful()
            ->assertSee('ORD-ADMIN-DETAILS');

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertSuccessful()
            ->assertSee('ORD-ADMIN-DETAILS')
            ->assertSee('123456');
    }

    public function test_admin_user_stats_count_delivered_topups_not_order_rows(): void
    {
        $customer = User::factory()->create(['status' => 'active']);
        $pack = $this->makePack();

        $bulk = Order::create([
            'order_number' => 'ORD-BULK',
            'user_id' => $customer->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'quantity' => 4,
            'final_price' => 2000,
        ]);

        foreach (range(1, 4) as $index) {
            DigiflazzStatus::create([
                'order_id' => $bulk->id,
                'diamond_pack_id' => $pack->id,
                'ref_id' => 'REF-BULK-'.$index,
                'status' => 'Sukses',
                'rc' => '00',
            ]);
        }

        $sending = Order::create([
            'order_number' => 'ORD-SENDING',
            'user_id' => $customer->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'sending',
        ]);

        DigiflazzStatus::create([
            'order_id' => $sending->id,
            'diamond_pack_id' => $pack->id,
            'ref_id' => 'REF-SENDING',
            'status' => 'Sukses',
            'rc' => '00',
        ]);

        Order::create([
            'order_number' => 'ORD-CANCELLED',
            'user_id' => $customer->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'cancelled',
        ]);

        $this->assertSame(5, $customer->deliveredTopupsCount());
        $this->assertSame(3, $customer->orders()->count());
        $this->assertSame(2000.0, $customer->lifetimeSpendDzd());
        $this->assertSame('4/4', $bulk->load(['orderItems', 'digiflazzStatuses', 'item4gamerOrders'])->topupProgressLabel());
        $this->assertSame('1/1', $sending->load(['orderItems', 'digiflazzStatuses', 'item4gamerOrders'])->topupProgressLabel());
    }

    public function test_admin_participants_page_lists_event_players(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $player = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);
        $this->creditSpins($player, $event, 2);

        Livewire::actingAs($admin)
            ->test(EventParticipants::class, ['record' => $event->getKey()])
            ->assertCanSeeTableRecords(WheelUserProgress::where('user_id', $player->id)->get());

        $this->actingAs($admin)
            ->get("/admin/wheel-events/{$event->getKey()}/edit")
            ->assertSuccessful();

        $this->actingAs($admin)
            ->get("/admin/wheel-events/{$event->getKey()}/participants")
            ->assertSuccessful();
    }

    public function test_spin_fails_without_available_spins(): void
    {
        $user = User::factory()->create();
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        $this->actingAs($user)
            ->postJson(route('event.spin', 'mobilelegends'))
            ->assertStatus(429)
            ->assertJson(['success' => false]);
    }

    public function test_active_admin_can_spin_without_earned_spins_and_repeat_the_track(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $pack = $this->makePack();
        $event = $this->makeEvent();
        $this->makeRewards($event, $pack);

        for ($spin = 0; $spin < 6; $spin++) {
            $this->actingAs($admin)
                ->postJson(route('event.spin', 'mobilelegends'))
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('unlimited_spins', true);
        }

        $progress = WheelUserProgress::where('user_id', $admin->id)->firstOrFail();

        $this->assertSame(0, $progress->total_spins_earned);
        $this->assertSame(0, $progress->total_spins_used);
        $this->assertSame(2, $progress->total_rewards_unlocked);
        $this->assertSame(6, WheelSpinLedger::where('user_id', $admin->id)->count());
        $this->assertSame(2, WheelClaim::where('user_id', $admin->id)->count());

        $this->actingAs($admin)
            ->get(route('event.show', 'mobilelegends'))
            ->assertOk()
            ->assertSee('data-unlimited-spins="true"', false)
            ->assertSee('∞', false);
    }
}
