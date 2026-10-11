<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SitePublicStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitePublicStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_stats_exclude_money_keys_and_include_counts(): void
    {
        Cache::forget(SitePublicStatsService::CACHE_KEY);

        User::factory()->count(3)->create();

        $stats = app(SitePublicStatsService::class)->homepageStats();

        $this->assertSame(3, $stats['members']);
        $this->assertArrayHasKey('players_topped_up', $stats);
        $this->assertArrayHasKey('topups_delivered', $stats);
        $this->assertArrayHasKey('products_topped_up', $stats);
        $this->assertArrayHasKey('games_live', $stats);

        foreach (['revenue', 'profit', 'cost', 'balance', 'wallet'] as $sensitive) {
            $this->assertArrayNotHasKey($sensitive, $stats);
        }
    }

    public function test_home_page_renders_stats_row(): void
    {
        Cache::forget(SitePublicStatsService::CACHE_KEY);
        User::factory()->count(2)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-stats', false)
            ->assertSee(__('home.stats_eyebrow'), false);
    }
}
