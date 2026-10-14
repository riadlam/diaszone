<?php

namespace Tests\Feature;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiveawayWheelFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeGiveaway(array $overrides = []): Giveaway
    {
        return Giveaway::create(array_merge([
            'title' => 'Wheel Giveaway',
            'slug' => 'wheel-giveaway',
            'description' => 'Spin test',
            'facebook_post_url' => 'https://facebook.com/posts/1',
            'facebook_page_url' => 'https://facebook.com/diaszone',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'is_active' => true,
        ], $overrides));
    }

    private function makeUser(string $name, ?string $email = null): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email ?? strtolower(str_replace(' ', '.', $name)).'@example.com',
            'status' => 'active',
        ]);
    }

    private function makeApprovedEntry(Giveaway $giveaway, User $user): GiveawayEntry
    {
        return GiveawayEntry::create([
            'giveaway_id' => $giveaway->id,
            'user_id' => $user->id,
            'referral_code' => GiveawayEntry::generateUniqueReferralCode(),
            'screenshot_path' => 'giveaway-screenshots/test.jpg',
            'liked_followed_ack' => true,
            'status' => GiveawayEntry::STATUS_APPROVED,
        ]);
    }

    private function wheelAdmin(): User
    {
        return $this->makeUser('Riad Admin', (string) config('giveaway.wheel_admin_email'));
    }

    public function test_non_admin_cannot_see_spin_ui(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));
        $user = $this->makeUser('Regular User');

        $this->actingAs($user)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertDontSee('data-gw-wheel', false)
            ->assertDontSee(__('giveaway.wheel_title'), false);
    }

    public function test_admin_email_can_see_spin_ui(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));
        $admin = $this->wheelAdmin();

        $this->actingAs($admin)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('data-gw-wheel', false)
            ->assertSee(__('giveaway.wheel_title'), false)
            ->assertSee(__('giveaway.wheel_hint'), false);
    }

    public function test_non_admin_cannot_post_spin(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));
        $user = $this->makeUser('Regular User');

        $this->actingAs($user)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertForbidden();
    }

    public function test_guest_cannot_post_spin(): void
    {
        $giveaway = $this->makeGiveaway();
        $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));

        $this->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertUnauthorized();
    }

    public function test_preview_spin_does_not_persist_winner(): void
    {
        config(['giveaway.draw_live' => false]);

        $giveaway = $this->makeGiveaway();
        $alice = $this->makeUser('Alice Player');
        $bob = $this->makeUser('Bob Player');
        $entry = $this->makeApprovedEntry($giveaway, $alice);
        $this->makeApprovedEntry($giveaway, $bob);
        $admin = $this->wheelAdmin();

        $response = $this->actingAs($admin)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'preview' => true,
            ]);

        $this->assertCount(2, $response->json('segments'));
        $this->assertContains($response->json('winner.entry_id'), [$entry->id, GiveawayEntry::query()->where('user_id', $bob->id)->value('id')]);
        $this->assertNotEmpty($response->json('winner.full_name'));
        $this->assertNotEmpty($response->json('winner.email'));
        $this->assertArrayHasKey('topups', $response->json('winner'));
        $this->assertArrayHasKey('joined_label', $response->json('winner'));

        $giveaway->refresh();
        $this->assertNull($giveaway->winner_entry_id);
        $this->assertNull($giveaway->winner_drawn_at);
    }

    public function test_spin_loads_all_approved_players_on_wheel(): void
    {
        config(['giveaway.draw_live' => false]);

        $giveaway = $this->makeGiveaway();
        foreach (range(1, 15) as $i) {
            $this->makeApprovedEntry($giveaway, $this->makeUser("Player {$i}"));
        }
        $admin = $this->wheelAdmin();

        $this->actingAs($admin)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertOk()
            ->assertJsonPath('preview', true)
            ->assertJsonCount(15, 'segments');

        $giveaway->refresh();
        $this->assertNull($giveaway->winner_entry_id);
    }

    public function test_live_spin_persists_winner_once(): void
    {
        config(['giveaway.draw_live' => true]);

        $giveaway = $this->makeGiveaway();
        $entryA = $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));
        $entryB = $this->makeApprovedEntry($giveaway, $this->makeUser('Bob Player'));
        $admin = $this->wheelAdmin();

        $first = $this->actingAs($admin)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'preview' => false,
            ]);

        $winnerId = (int) $first->json('winner.entry_id');
        $this->assertContains($winnerId, [$entryA->id, $entryB->id]);

        $giveaway->refresh();
        $this->assertSame($winnerId, (int) $giveaway->winner_entry_id);
        $this->assertNotNull($giveaway->winner_drawn_at);

        $this->actingAs($admin)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'message' => __('giveaway.wheel_already_drawn'),
            ]);

        $giveaway->refresh();
        $this->assertSame($winnerId, (int) $giveaway->winner_entry_id);
    }

    public function test_winner_cards_public_and_personal(): void
    {
        config([
            'giveaway.draw_live' => true,
            'giveaway.winner_reveal_enabled' => true,
        ]);

        $giveaway = $this->makeGiveaway([
            'facebook_page_url' => 'https://facebook.com/diaszone',
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subMinute(),
            'is_active' => true,
        ]);
        $winnerUser = $this->makeUser('Winner User');
        $outsider = $this->makeUser('Outsider User');
        $winnerEntry = $this->makeApprovedEntry($giveaway, $winnerUser);
        $giveaway->forceFill([
            'winner_entry_id' => $winnerEntry->id,
            'winner_drawn_at' => now(),
        ])->save();

        $this->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('data-gw-announced', false)
            ->assertSee(__('giveaway.announced_title'), false)
            ->assertSee('Winner User', false)
            ->assertSee(__('giveaway.more_giveaways_soon'), false)
            ->assertSee(__('giveaway.follow_facebook'), false)
            ->assertDontSee(__('giveaway.how_title'), false)
            ->assertDontSee(__('giveaway.countdown_label'), false)
            ->assertDontSee('data-gw-winner', false);

        $this->actingAs($outsider)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('data-gw-announced', false)
            ->assertDontSee('data-gw-winner', false);

        $this->actingAs($winnerUser)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('data-gw-winner', false)
            ->assertSee(__('giveaway.winner_title'), false)
            ->assertSee('data-gw-announced', false)
            ->assertDontSee('data-gw-wheel', false);
    }

    public function test_featured_giveaway_index_redirects_to_weekly_slug(): void
    {
        $this->makeGiveaway(['slug' => 'weekly-pass', 'title' => 'Weekly Featured']);

        $this->get(route('giveaway.index'))
            ->assertRedirect(route('giveaway.show', 'weekly-pass'));
    }
}
