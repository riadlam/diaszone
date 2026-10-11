<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SitePublicStatsService
{
    public const CACHE_KEY = 'homepage.public_stats.v3';

    public const CACHE_TTL_SECONDS = 600;

    /**
     * Non-sensitive aggregate stats for homepage social proof.
     * Never includes revenue, profit, cost, or balances.
     *
     * @return array{
     *     members: int,
     *     players_topped_up: int,
     *     topups_delivered: int,
     *     games_live: int
     * }
     */
    public function homepageStats(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            $finance = app(AdminFinanceService::class);
            $paidStatuses = $finance->paidStatuses();

            $members = (int) User::query()->count();

            $registeredPlayers = (int) Order::query()
                ->whereIn('status', $paidStatuses)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id');

            $guestCompletions = (int) Order::query()
                ->where('status', 'completed')
                ->whereNull('user_id')
                ->count();

            $topupsDelivered = (int) $finance->deliveredTopupsCount();

            $gamesLive = (int) Game::query()->where('is_active', true)->count();

            return [
                'members' => max(0, $members),
                'players_topped_up' => max(0, $registeredPlayers + $guestCompletions),
                'topups_delivered' => max(0, $topupsDelivered),
                'games_live' => max(0, $gamesLive),
            ];
        });
    }
}
