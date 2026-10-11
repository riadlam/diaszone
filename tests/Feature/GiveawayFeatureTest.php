<?php

namespace Tests\Feature;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Models\GiveawayReferral;
use App\Models\User;
use App\Services\GiveawayReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_authenticated_user_can_submit_entry_and_get_referral_code(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway();
        $user = $this->makeUser('Bob Tester');

        $response = $this->actingAs($user)->post(route('giveaway.enter', $giveaway->slug), [
            'screenshot' => UploadedFile::fake()->image('tags.jpg', 800, 600),
            'liked_followed_ack' => '1',
        ]);

        $response->assertRedirect(route('giveaway.show', $giveaway->slug));

        $entry = GiveawayEntry::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($entry);
        $this->assertSame(GiveawayEntry::STATUS_PENDING, $entry->status);
        $this->assertNotEmpty($entry->referral_code);
        Storage::disk('public')->assertExists($entry->screenshot_path);
    }

    public function test_closed_giveaway_rejects_new_entries(): void
    {
        Storage::fake('public');
        $giveaway = $this->makeGiveaway([
            'ends_at' => now()->subHour(),
        ]);
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('giveaway.enter', $giveaway->slug), [
            'screenshot' => UploadedFile::fake()->image('tags.jpg'),
            'liked_followed_ack' => '1',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(0, GiveawayEntry::count());
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

    public function test_enrollee_count_matches_entries(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeEntry($giveaway, $this->makeUser('One'));
        $this->makeEntry($giveaway, $this->makeUser('Two'));

        $this->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('2', false)
            ->assertSee('One', false)
            ->assertSee('Two', false);
    }

    public function test_nav_contains_giveaway_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('giveaway.index'), false);
    }
}
