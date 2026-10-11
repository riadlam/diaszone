<?php

namespace Tests\Feature;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Models\GiveawayReferral;
use App\Models\User;
use App\Services\GiveawayReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GiveawayFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeGiveaway(array $overrides = []): Giveaway
    {
        return Giveaway::create(array_merge([
            'title' => 'Facebook Spring Giveaway',
            'slug' => 'spring-giveaway',
            'description' => 'Win prizes',
            'facebook_post_url' => 'https://facebook.com/posts/1',
            'facebook_page_url' => 'https://facebook.com/diaszone',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'is_active' => true,
        ], $overrides));
    }

    private function makeUser(string $name = 'Alice Player'): User
    {
        return User::factory()->create([
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function makeEntry(Giveaway $giveaway, User $user, array $overrides = []): GiveawayEntry
    {
        return GiveawayEntry::create(array_merge([
            'giveaway_id' => $giveaway->id,
            'user_id' => $user->id,
            'referral_code' => GiveawayEntry::generateUniqueReferralCode(),
            'screenshot_path' => 'giveaway-screenshots/test.jpg',
            'liked_followed_ack' => true,
            'status' => GiveawayEntry::STATUS_PENDING,
        ], $overrides));
    }

    public function test_public_giveaway_page_renders_active_campaign(): void
    {
        $this->makeGiveaway();

        $this->get(route('giveaway.index'))
            ->assertOk()
            ->assertSee('Facebook Spring Giveaway', false)
            ->assertSee(__('giveaway.how_title'), false);
    }

    public function test_guest_cannot_submit_entry(): void
    {
        $giveaway = $this->makeGiveaway();

        $this->post(route('giveaway.enter', $giveaway->slug), [
            'liked_followed_ack' => '1',
        ])->assertRedirect();
    }

    public function test_authenticated_user_gets_draft_referral_and_can_submit_for_approval(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Bob Tester');

        $this->actingAs($user)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee(__('giveaway.copy_link'), false);

        $entry = GiveawayEntry::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($entry);
        $this->assertSame(GiveawayEntry::STATUS_DRAFT, $entry->status);
        $this->assertNotEmpty($entry->referral_code);

        $this->actingAs($user)
            ->post(route('giveaway.screenshot.upload', $giveaway->slug), [
                'screenshot' => UploadedFile::fake()->image('tags.jpg', 800, 600),
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $entry->refresh();
        $this->assertTrue($entry->hasScreenshot());
        Storage::disk('public')->assertExists($entry->screenshot_path);

        $response = $this->actingAs($user)->post(route('giveaway.enter', $giveaway->slug), [
            'liked_followed_ack' => '1',
        ]);

        $response->assertRedirect(route('giveaway.show', $giveaway->slug));

        $entry->refresh();
        $this->assertSame(GiveawayEntry::STATUS_PENDING, $entry->status);
        $this->assertTrue($entry->liked_followed_ack);
    }

    public function test_submit_blocked_without_screenshot(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('No Shot');

        $this->actingAs($user)->get(route('giveaway.show', $giveaway->slug));

        $this->actingAs($user)->post(route('giveaway.enter', $giveaway->slug), [
            'liked_followed_ack' => '1',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(GiveawayEntry::STATUS_DRAFT, GiveawayEntry::query()->where('user_id', $user->id)->value('status'));
    }

    public function test_screenshot_can_be_deleted_and_replaced(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Replace Me');

        $this->actingAs($user)->get(route('giveaway.show', $giveaway->slug));

        $this->actingAs($user)->post(route('giveaway.screenshot.upload', $giveaway->slug), [
            'screenshot' => UploadedFile::fake()->image('one.jpg'),
        ])->assertOk();

        $entry = GiveawayEntry::query()->where('user_id', $user->id)->first();
        $firstPath = $entry->screenshot_path;

        $this->actingAs($user)
            ->delete(route('giveaway.screenshot.delete', $giveaway->slug))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $entry->refresh();
        $this->assertNull($entry->screenshot_path);
        Storage::disk('public')->assertMissing($firstPath);

        $this->actingAs($user)->post(route('giveaway.screenshot.upload', $giveaway->slug), [
            'screenshot' => UploadedFile::fake()->image('two.jpg'),
        ])->assertOk();

        $entry->refresh();
        $this->assertTrue($entry->hasScreenshot());
    }

    public function test_closed_giveaway_rejects_new_entries(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway([
            'ends_at' => now()->subHour(),
        ]);
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('giveaway.enter', $giveaway->slug), [
            'liked_followed_ack' => '1',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(0, GiveawayEntry::query()->where('status', GiveawayEntry::STATUS_PENDING)->count());
    }

    public function test_referral_capture_and_google_style_attribution(): void
    {
        $giveaway = $this->makeGiveaway();
        $referrer = $this->makeUser('Referrer One');
        $friend = $this->makeUser('Friend Two');
        $entry = $this->makeEntry($giveaway, $referrer);

        $service = app(GiveawayReferralService::class);

        $this->get(route('giveaway.show', ['slug' => $giveaway->slug, 'ref' => $entry->referral_code]))
            ->assertOk();

        $this->assertNotNull(session(GiveawayReferralService::SESSION_KEY));
        $this->assertSame($entry->referral_code, session(GiveawayReferralService::SESSION_KEY)['code']);

        $result = $service->attribute($friend, request());
        $this->assertTrue($result['ok']);

        $this->assertDatabaseHas('giveaway_referrals', [
            'giveaway_id' => $giveaway->id,
            'referrer_entry_id' => $entry->id,
            'referred_user_id' => $friend->id,
        ]);

        $entry->refresh();
        $this->assertNotNull($entry->referral_satisfied_at);
    }

    public function test_already_authenticated_friend_with_ref_is_attributed(): void
    {
        $giveaway = $this->makeGiveaway();
        $referrer = $this->makeUser('Referrer');
        $friend = $this->makeUser('Friend');
        $entry = $this->makeEntry($giveaway, $referrer);

        $this->actingAs($friend)
            ->get(route('giveaway.show', ['slug' => $giveaway->slug, 'ref' => $entry->referral_code]))
            ->assertOk();

        $this->assertSame(1, GiveawayReferral::count());
        $entry->refresh();
        $this->assertNotNull($entry->referral_satisfied_at);
    }

    public function test_self_referral_is_ignored(): void
    {
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser();
        $entry = $this->makeEntry($giveaway, $user);

        $this->actingAs($user)
            ->get(route('giveaway.show', ['slug' => $giveaway->slug, 'ref' => $entry->referral_code]))
            ->assertOk();

        $this->assertSame(0, GiveawayReferral::count());
        $entry->refresh();
        $this->assertNull($entry->referral_satisfied_at);
    }

    public function test_double_attribution_blocked(): void
    {
        $giveaway = $this->makeGiveaway();
        $referrer = $this->makeUser('R1');
        $friend = $this->makeUser('F1');
        $entry = $this->makeEntry($giveaway, $referrer);
        $service = app(GiveawayReferralService::class);

        $first = $service->attributeWithCode($friend, $entry->referral_code);
        $second = $service->attributeWithCode($friend, $entry->referral_code);

        $this->assertTrue($first['ok']);
        $this->assertFalse($second['ok']);
        $this->assertSame(1, GiveawayReferral::count());
    }

    public function test_expired_giveaway_does_not_attribute(): void
    {
        $giveaway = $this->makeGiveaway([
            'ends_at' => now()->subMinute(),
        ]);
        $referrer = $this->makeUser('R');
        $friend = $this->makeUser('F');
        $entry = $this->makeEntry($giveaway, $referrer);

        $result = app(GiveawayReferralService::class)->attributeWithCode($friend, $entry->referral_code);

        $this->assertFalse($result['ok']);
        $this->assertSame('closed', $result['reason']);
        $this->assertSame(0, GiveawayReferral::count());
    }

    public function test_decline_reason_visible_on_page(): void
    {
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Declined User');
        $this->makeEntry($giveaway, $user, [
            'status' => GiveawayEntry::STATUS_DECLINED,
            'decline_reason' => 'Screenshot does not show 5 tags',
        ]);

        $this->actingAs($user)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('Screenshot does not show 5 tags', false)
            ->assertSee(__('giveaway.status_declined'), false);
    }

    public function test_enrollee_count_matches_approved_only(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeEntry($giveaway, $this->makeUser('One'), [
            'status' => GiveawayEntry::STATUS_APPROVED,
        ]);
        $this->makeEntry($giveaway, $this->makeUser('Two'), [
            'status' => GiveawayEntry::STATUS_PENDING,
        ]);

        $this->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('One', false)
            ->assertDontSee('Two', false);
    }

    public function test_nav_contains_giveaway_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('giveaway.index'), false);
    }

    public function test_submit_sends_telegram_and_referral_updates_message(): void
    {
        Storage::fake('public');
        config([
            'telegram.bot_token' => 'test-token',
            'telegram.chat_id' => '12345',
            'telegram.api_url' => 'https://api.telegram.org/bot',
        ]);

        Http::fake([
            'https://api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 9001]], 200),
            'https://api.telegram.org/*/editMessageText' => Http::response(['ok' => true], 200),
        ]);

        $giveaway = $this->makeGiveaway();
        $referrer = $this->makeUser('Referrer TG');
        $friend = $this->makeUser('Friend TG');

        $this->actingAs($referrer)->get(route('giveaway.show', $giveaway->slug));
        $this->actingAs($referrer)->post(route('giveaway.screenshot.upload', $giveaway->slug), [
            'screenshot' => UploadedFile::fake()->image('tags.jpg'),
        ]);
        $this->actingAs($referrer)->post(route('giveaway.enter', $giveaway->slug), [
            'liked_followed_ack' => '1',
        ])->assertRedirect();

        $entry = GiveawayEntry::query()->where('user_id', $referrer->id)->first();
        $this->assertSame(9001, $entry->tlg_message_id);

        $this->actingAs($friend)
            ->get(route('giveaway.show', ['slug' => $giveaway->slug, 'ref' => $entry->referral_code]))
            ->assertOk();

        $this->assertSame(1, GiveawayReferral::count());
        $entry->refresh();
        $this->assertNotNull($entry->referral_satisfied_at);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/editMessageText')
                && (int) $request['message_id'] === 9001;
        });

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/sendMessage')) {
                return false;
            }
            $markup = json_decode($request['reply_markup'] ?? '{}', true);

            return collect($markup['inline_keyboard'] ?? [])
                ->flatten(1)
                ->contains(fn ($btn) => ($btn['callback_data'] ?? '') === 'gw_approve');
        });
    }

    public function test_approved_page_shows_stay_tuned_message(): void
    {
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Winner Hopeful');
        $this->makeEntry($giveaway, $user, [
            'status' => GiveawayEntry::STATUS_APPROVED,
        ]);

        $this->actingAs($user)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee(__('giveaway.approved_title'), false)
            ->assertSee(__('giveaway.approved_body'), false);
    }

    public function test_pending_page_hides_full_steps_and_shows_pending_banner(): void
    {
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Pending Player');
        $this->makeEntry($giveaway, $user, [
            'status' => GiveawayEntry::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee(__('giveaway.pending_title'), false)
            ->assertSee(__('giveaway.step3_title'), false)
            ->assertDontSee(__('giveaway.step1_title'), false)
            ->assertDontSee(__('giveaway.step2_title'), false);
    }

    public function test_telegram_approve_callback_approves_entry(): void
    {
        config([
            'telegram.bot_token' => 'test-token',
            'telegram.chat_id' => '12345',
            'telegram.api_url' => 'https://api.telegram.org/bot',
        ]);
        Http::fake([
            'https://api.telegram.org/*/answerCallbackQuery' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/*/editMessageText' => Http::response(['ok' => true], 200),
            'https://api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);

        $giveaway = $this->makeGiveaway();
        $entry = $this->makeEntry($giveaway, $this->makeUser('TG Approve'), [
            'status' => GiveawayEntry::STATUS_PENDING,
            'tlg_message_id' => 777,
        ]);

        $this->post('/webhook/telegram', [
            'callback_query' => [
                'id' => 'cb1',
                'data' => 'gw_approve',
                'message' => ['message_id' => 777],
            ],
        ])->assertOk();

        $entry->refresh();
        $this->assertSame(GiveawayEntry::STATUS_APPROVED, $entry->status);
    }
}
