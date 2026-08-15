<?php

namespace App\Http\Controllers;

use App\Models\WheelClaim;
use App\Models\WheelEvent;
use App\Services\WheelProgressService;
use App\Services\WheelQualificationService;
use App\Support\MobileLegendsPackIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * URL slugs that do not match a game_type directly.
     */
    private const SLUG_ALIASES = [
        'mobile-legends' => 'mobilelegends',
        'free-fire' => 'freefire',
        'free-fire-diamonds-top-up' => 'freefire',
        'pubg-mobile' => 'pubgmobile',
        'pubg-mobile-uc-top-up-global' => 'pubgmobile',
        'honor-of-kings' => 'honorofkings',
        'honor-of-kings-tokens-top-up-global' => 'honorofkings',
        'blood-strike' => 'bloodstrike',
        'blood-strike-golds-top-up-global' => 'bloodstrike',
        'steam-gift-cards' => 'steam_giftcard',
    ];

    /**
     * Slice budget for the rendered wheel, blanks included.
     */
    private const MIN_WHEEL_SLICES = 8;

    private const MAX_WHEEL_SLICES = 12;

    private const DISPLAY_NAMES = [
        'mobilelegends' => 'Mobile Legends',
        'freefire' => 'Free Fire',
        'pubgmobile' => 'PUBG Mobile',
        'honorofkings' => 'Honor of Kings',
        'bloodstrike' => 'Blood Strike',
        'steam_giftcard' => 'Steam Gift Cards',
    ];

    public function __construct(
        private readonly WheelQualificationService $qualification,
        private readonly WheelProgressService $progress
    ) {}

    public function show(Request $request, $gameSlug)
    {
        $gameType = $this->resolveGameType($gameSlug);
        $gameTitle = self::DISPLAY_NAMES[$gameType]
            ?? ucwords(str_replace('_', ' ', $gameType));

        $available = $gameType === WheelQualificationService::GAME_TYPE;
        $event = $available ? $this->qualification->currentActiveEvent() : null;
        $nextEvent = $available
            ? WheelEvent::query()
                ->forGame($gameType)
                ->where('is_active', true)
                ->where('starts_at', '>', now())
                ->orderBy('starts_at')
                ->first()
            : null;

        if (! $available) {
            return view('pages.event', [
                'available' => false,
                'gameSlug' => $gameSlug,
                'gameType' => $gameType,
                'gameTitle' => $gameTitle,
                'gameUrl' => $this->gameUrl($gameType),
                'requiresLogin' => false,
                'event' => null,
                'nextEvent' => null,
                'snapshot' => null,
                'prizes' => [],
            ]);
        }

        if (! $event) {
            return view('pages.event', [
                'available' => true,
                'requiresLogin' => false,
                'gameSlug' => $gameSlug,
                'gameType' => $gameType,
                'gameTitle' => $gameTitle,
                'gameUrl' => $this->gameUrl($gameType),
                'gameImage' => app(HomeController::class)->findGameImage($gameType, $gameTitle),
                'event' => null,
                'nextEvent' => $nextEvent,
                'snapshot' => null,
                'prizes' => [],
            ]);
        }

        // Guests can browse the live wheel; Spin / My Rewards ask them to log in.
        if (! Auth::check()) {
            return view('pages.event', [
                'available' => true,
                'requiresLogin' => true,
                'gameSlug' => $gameSlug,
                'gameType' => $gameType,
                'gameTitle' => $gameTitle,
                'gameUrl' => $this->gameUrl($gameType),
                'gameImage' => app(HomeController::class)->findGameImage($gameType, $gameTitle),
                'event' => $event,
                'nextEvent' => null,
                'snapshot' => null,
                'prizes' => $this->wheelSegments($event),
                'spinsLeft' => 0,
                'unlimitedSpins' => false,
                'currency' => 'Diamonds',
            ]);
        }

        $snapshot = $this->progress->snapshot(Auth::user(), $event);

        return view('pages.event', [
            'available' => true,
            'requiresLogin' => false,
            'gameSlug' => $gameSlug,
            'gameType' => $gameType,
            'gameTitle' => $gameTitle,
            'gameUrl' => $this->gameUrl($gameType),
            'gameImage' => app(HomeController::class)->findGameImage($gameType, $gameTitle),
            'event' => $event,
            'nextEvent' => null,
            'snapshot' => $snapshot,
            'prizes' => $this->wheelSegments($event),
            'spinsLeft' => $snapshot['available_spins'],
            'unlimitedSpins' => $snapshot['unlimited_spins'],
            'currency' => 'Diamonds',
        ]);
    }

    public function spin(Request $request, $gameSlug)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('event.login_required'),
                'require_login' => true,
            ], 401);
        }

        $gameType = $this->resolveGameType($gameSlug);
        if ($gameType !== WheelQualificationService::GAME_TYPE) {
            return response()->json([
                'success' => false,
                'message' => __('event.not_available'),
            ], 404);
        }

        $result = $this->progress->draw(Auth::user());

        $status = ($result['success'] ?? false) ? 200 : (
            ($result['code'] ?? null) === 'no_spins' ? 429 : 422
        );

        return response()->json($result, $status);
    }

    public function rewards(Request $request, $gameSlug)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('event.login_required'),
                'require_login' => true,
            ], 401);
        }

        $gameType = $this->resolveGameType($gameSlug);
        if ($gameType !== WheelQualificationService::GAME_TYPE) {
            return response()->json(['success' => false, 'message' => __('event.not_available')], 404);
        }

        $claims = WheelClaim::with(['reward.diamondPack', 'coupon'])
            ->where('user_id', Auth::id())
            ->orderByDesc('unlocked_at')
            ->get()
            ->map(fn (WheelClaim $claim) => $this->progress->serializeClaim($claim));

        return response()->json([
            'success' => true,
            'claims' => $claims,
        ]);
    }

    private function wheelSegments(?\App\Models\WheelEvent $event): array
    {
        if (! $event) {
            return [];
        }

        $styles = ['purple', 'pink', 'teal', 'gold'];
        $rewardSegments = [];

        foreach ($event->activeRewards as $index => $reward) {
            $images = $reward->imageUrls();
            $icon = $images[0]
                ?? ($reward->diamondPack ? MobileLegendsPackIcon::url($reward->diamondPack) : null);

            $rewardSegments[] = [
                'key' => 'reward_'.$reward->id,
                'type' => $reward->reward_type === 'discount' ? 'coupon' : 'currency',
                'label' => $reward->label,
                'sub' => null,
                'style' => $styles[$index % count($styles)],
                'weight' => 1,
                'reward_id' => $reward->id,
                'draws_required' => $reward->draws_required,
                'icon' => $icon,
                'icon_fit' => $images ? 'cover' : 'contain',
                'gallery' => $images ?: array_values(array_filter([$icon])),
            ];
        }

        return $this->withBlankSegments($rewardSegments);
    }

    /**
     * Spread "no prize" slices between the reward slices. They are generated here
     * instead of being configured by an admin, and every losing spin lands on one.
     *
     * @param  array<int, array<string, mixed>>  $rewardSegments
     * @return array<int, array<string, mixed>>
     */
    private function withBlankSegments(array $rewardSegments): array
    {
        $rewardCount = count($rewardSegments);

        if ($rewardCount === 0) {
            return [];
        }

        $blanksLeft = max(
            1,
            self::MIN_WHEEL_SLICES - $rewardCount,
            min($rewardCount, self::MAX_WHEEL_SLICES - $rewardCount)
        );
        $segments = [];

        foreach ($rewardSegments as $index => $segment) {
            $segments[] = $segment;

            $share = intdiv($blanksLeft, $rewardCount - $index);
            $blanksLeft -= $share;

            for ($i = 0; $i < $share; $i++) {
                $segments[] = [
                    'key' => 'blank_'.count($segments),
                    'type' => 'none',
                    'label' => __('event.no_win_label'),
                    'sub' => null,
                    'style' => 'slate',
                    'weight' => 1,
                    'reward_id' => null,
                    'draws_required' => null,
                    'icon' => null,
                    'icon_fit' => 'contain',
                    'gallery' => [],
                ];
            }
        }

        return $segments;
    }

    private function resolveGameType($gameSlug): string
    {
        $slug = strtolower((string) $gameSlug);

        return self::SLUG_ALIASES[$slug] ?? str_replace('-', '_', $slug);
    }

    private function gameUrl(string $gameType): string
    {
        $named = ['mobilelegends', 'freefire', 'pubgmobile', 'honorofkings', 'bloodstrike', 'steam_giftcard'];
        if (in_array($gameType, $named, true)) {
            return route($gameType);
        }

        return url('/'.$gameType);
    }
}
