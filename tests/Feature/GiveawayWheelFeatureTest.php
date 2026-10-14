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
            ->assertSee(__('giveaway.wheel_preview_hint'), false);
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
        $entry = $this->makeApprovedEntry($giveaway, $this->makeUser('Alice Player'));
        $admin = $this->wheelAdmin();

        $response = $this->actingAs($admin)
            ->postJson(route('giveaway.spin', $giveaway->slug))
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'preview' => true,
            ]);

        $response->assertJsonPath('winner.entry_id', $entry->id);

        $giveaway->refresh();
        $this->assertNull($giveaway->winner_entry_id);
        $this->assertNull($giveaway->winner_drawn_at);
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

    public function test_winner_reveal_absent_unless_flag_and_winner_match(): void
    {
        config([
            'giveaway.draw_live' => true,
            'giveaway.winner_reveal_enabled' => false,
        ]);

        $giveaway = $this->makeGiveaway();
        $winnerUser = $this->makeUser('Winner User');
        $winnerEntry = $this->makeApprovedEntry($giveaway, $winnerUser);
        $giveaway->forceFill([
            'winner_entry_id' => $winnerEntry->id,
            'winner_drawn_at' => now(),
        ])->save();

        $this->actingAs($winnerUser)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertDontSee('data-gw-winner', false)
            ->assertDontSee(__('giveaway.winner_title'), false);

        config(['giveaway.winner_reveal_enabled' => true]);

        $other = $this->makeUser('Other User');
        $this->actingAs($other)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertDontSee('data-gw-winner', false);

        $this->actingAs($winnerUser)
            ->get(route('giveaway.show', $giveaway->slug))
            ->assertOk()
            ->assertSee('data-gw-winner', false)
            ->assertSee(__('giveaway.winner_title'), false)
            ->assertSee('Winner', false);
    }
}
