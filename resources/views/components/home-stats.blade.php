@php
    /** @var array{members?:int,players_topped_up?:int,topups_delivered?:int,games_live?:int} $publicStats */
    $publicStats = $publicStats ?? [];
    /** @var \App\Models\Giveaway|null $liveGiveaway */
    $liveGiveaway = $liveGiveaway ?? null;
    $giveawayUrl = route('giveaway.index');
    $items = [
        [
            'key' => 'members',
            'value' => (int) ($publicStats['members'] ?? 0),
            'label' => __('home.stats_members'),
            'icon' => 'users',
        ],
        [
            'key' => 'players_topped_up',
            'value' => (int) ($publicStats['players_topped_up'] ?? 0),
            'label' => __('home.stats_players'),
            'icon' => 'player',
        ],
        [
            'key' => 'topups_delivered',
            'value' => (int) ($publicStats['topups_delivered'] ?? 0),
            'label' => __('home.stats_topups'),
            'icon' => 'bolt',
        ],
        [
            'key' => 'games_live',
            'value' => (int) ($publicStats['games_live'] ?? 0),
            'label' => __('home.stats_games'),
            'icon' => 'game',
        ],
    ];
    $items = array_values(array_filter($items, fn ($row) => $row['value'] > 0));
    $showStats = count($items) > 0;
    $showGiveawayCta = $liveGiveaway !== null;
@endphp

@if($showStats || $showGiveawayCta)
<section class="home-stats" data-home-stats aria-label="{{ __('home.stats_aria') }}">
    <div class="home-stats__inner">
        @if($showStats)
            <p class="home-stats__eyebrow">{{ __('home.stats_eyebrow') }}</p>
            <div class="home-stats__grid" data-stats-grid>
                @foreach ($items as $item)
                    @php
                        $display = number_format($item['value']);
                    @endphp
                    <div class="home-stats__chip">
                        <span class="home-stats__icon" data-icon="{{ $item['icon'] }}" aria-hidden="true">
                            @if($item['icon'] === 'users')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z" stroke="currentColor" stroke-width="1.7"/><path d="M4.5 19.2a6.8 6.8 0 0 1 15 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            @elseif($item['icon'] === 'player')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.7"/><path d="M5 20c1.4-3.2 3.8-4.8 7-4.8s5.6 1.6 7 4.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M17.5 8.5 19 7l1.5 1.5M17.5 11.5 19 13l1.5-1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @elseif($item['icon'] === 'bolt')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M13 2 6 13h5l-1 9 8-12h-5l0-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="6.5" width="17" height="11" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M8 10.5h2.5M13.5 10.5H16M8 14h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            @endif
                        </span>
                        <span class="home-stats__meta">
                            <span class="home-stats__value">{{ $display }}</span>
                            <span class="home-stats__label">{{ $item['label'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        @if($showGiveawayCta)
            <a href="{{ $giveawayUrl }}"
               class="home-gw-cta"
               data-home-gw-cta
               aria-label="{{ __('home.giveaway_cta_title') }}">
                <span class="home-gw-cta__shine" aria-hidden="true"></span>
                <span class="home-gw-cta__center">
                    <img class="home-gw-cta__art home-gw-cta__art--present"
                         src="{{ \App\Models\Giveaway::assetUrl('cta-present.svg') }}"
                         alt=""
                         width="28"
                         height="28"
                         decoding="async"
                         loading="lazy"
                         aria-hidden="true">
                    <span class="home-gw-cta__text">{{ __('home.giveaway_cta_title') }}</span>
                    <img class="home-gw-cta__art home-gw-cta__art--diamonds"
                         src="{{ \App\Models\Giveaway::assetUrl('cta-diamonds.svg') }}"
                         alt=""
                         width="26"
                         height="26"
                         decoding="async"
                         loading="lazy"
                         aria-hidden="true">
                </span>
            </a>
        @endif
    </div>
</section>

<style>
.home-stats {
    --hs-ink: #1f1634;
    --hs-muted: #6b6280;
    --hs-accent: #7c3aed;
    --hs-accent-2: #db2777;
    --hs-card: #ffffff;
    padding: 0.45rem 0 1rem;
    background: #ffffff;
    overflow: hidden;
}

.home-stats__inner {
    max-width: 1536px;
    margin: 0 auto;
    padding: 0.25rem 1rem 0;
}

.home-stats__eyebrow {
    margin: 0 0 0.65rem;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hs-muted);
}

.home-stats__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
    width: 100%;
}

.home-stats__chip {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-width: 0;
    padding: 0.75rem 0.9rem;
    border-radius: 1rem;
    background: var(--hs-card);
    border: 1px solid rgba(124, 58, 237, 0.12);
    box-shadow: 0 6px 16px rgba(49, 28, 92, 0.04);
}

.home-stats__icon {
    display: grid;
    place-items: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.8rem;
    flex-shrink: 0;
    color: var(--hs-accent);
    background: linear-gradient(145deg, rgba(124, 58, 237, 0.12), rgba(219, 39, 119, 0.08));
}

.home-stats__chip:nth-child(2) .home-stats__icon { color: #a21caf; }
.home-stats__chip:nth-child(3) .home-stats__icon { color: var(--hs-accent-2); }
.home-stats__chip:nth-child(4) .home-stats__icon { color: #6d28d9; }

.home-stats__icon svg {
    width: 1.1rem;
    height: 1.1rem;
}

.home-stats__meta {
    display: flex;
    flex-direction: column;
    gap: 0.08rem;
    min-width: 0;
}

.home-stats__value {
    font-size: 1.12rem;
    line-height: 1.1;
    font-weight: 800;
    color: var(--hs-ink);
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}

.home-stats__label {
    font-size: 0.72rem;
    line-height: 1.25;
    font-weight: 600;
    color: var(--hs-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ---- Slim premium giveaway row ---- */
.home-gw-cta {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 2.45rem;
    margin-top: 0.75rem;
    padding: 0 0.9rem;
    border-radius: 999px;
    text-decoration: none;
    color: #fff;
    overflow: hidden;
    isolation: isolate;
    background: linear-gradient(100deg, #6d28d9 0%, #7c3aed 45%, #c026d3 100%);
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 6px 18px rgba(109, 40, 217, 0.22);
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.home-gw-cta:hover,
.home-gw-cta:focus-visible {
    box-shadow: 0 8px 22px rgba(109, 40, 217, 0.3);
    outline: none;
}

.home-gw-cta__shine {
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, transparent 38%, rgba(255,255,255,0.28) 50%, transparent 62%);
    -webkit-transform: translate3d(-130%, 0, 0);
    transform: translate3d(-130%, 0, 0);
    animation: homeGwShine 5s ease-in-out infinite;
    pointer-events: none;
    z-index: 1;
}

.home-gw-cta__center {
    position: relative;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    max-width: 100%;
}

.home-gw-cta__text {
    font-size: 0.86rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -0.01em;
    white-space: nowrap;
    text-align: center;
}

.home-gw-cta__art {
    display: block;
    width: 1.55rem;
    height: 1.55rem;
    object-fit: contain;
    background: transparent;
    border: 0;
    flex-shrink: 0;
}

.home-gw-cta__art--present {
    width: 1.7rem;
    height: 1.7rem;
}

@keyframes homeGwShine {
    0%, 62% { -webkit-transform: translate3d(-130%, 0, 0); transform: translate3d(-130%, 0, 0); }
    88%, 100% { -webkit-transform: translate3d(130%, 0, 0); transform: translate3d(130%, 0, 0); }
}

@media (max-width: 900px) {
    .home-stats__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.6rem;
    }
}

@media (max-width: 480px) {
    .home-stats__chip {
        padding: 0.65rem 0.75rem;
        gap: 0.55rem;
    }
    .home-stats__value {
        font-size: 1rem;
    }
    .home-stats__label {
        font-size: 0.68rem;
    }
    .home-gw-cta {
        height: 2.3rem;
        padding: 0 0.7rem;
    }
    .home-gw-cta__center {
        gap: 0.4rem;
    }
    .home-gw-cta__text {
        font-size: 0.78rem;
    }
    .home-gw-cta__art {
        width: 1.35rem;
        height: 1.35rem;
    }
    .home-gw-cta__art--present {
        width: 1.45rem;
        height: 1.45rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .home-gw-cta__shine {
        animation: none !important;
    }
}
</style>
@endif
