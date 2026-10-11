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
            <div class="home-stats__viewport" data-stats-viewport>
                <div class="home-stats__track" data-stats-track>
                    @foreach ([0, 1] as $loopCopy)
                        <div class="home-stats__group" @if($loopCopy === 1) aria-hidden="true" @endif>
                            @foreach ($items as $item)
                                <div class="home-stats__chip" data-stat-chip data-stat-target="{{ $item['value'] }}">
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
                                        <span class="home-stats__value" data-stat-value>0</span>
                                        <span class="home-stats__label">{{ $item['label'] }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
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
                         aria-hidden="true">
                    <span class="home-gw-cta__text">{{ __('home.giveaway_cta_title') }}</span>
                    <img class="home-gw-cta__art home-gw-cta__art--diamonds"
                         src="{{ \App\Models\Giveaway::assetUrl('cta-diamonds.svg') }}"
                         alt=""
                         width="26"
                         height="26"
                         decoding="async"
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
    --hs-card: rgba(255, 255, 255, 0.92);
    padding: 0.35rem 0 1rem;
    background:
        radial-gradient(120% 80% at 10% 0%, rgba(192, 132, 252, 0.14), transparent 55%),
        radial-gradient(90% 70% at 90% 100%, rgba(219, 39, 119, 0.08), transparent 50%),
        #ffffff;
    overflow: hidden;
}

.home-stats__inner {
    max-width: 1536px;
    margin: 0 auto;
    padding: 0.35rem 1rem 0;
}

.home-stats__eyebrow {
    margin: 0 0 0.55rem;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hs-muted);
}

.home-stats__viewport {
    position: relative;
    mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent);
    -webkit-mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent);
}

.home-stats__track {
    display: flex;
    width: max-content;
    gap: 0.75rem;
    animation: homeStatsMarquee 38s linear infinite;
    will-change: transform;
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
}

.home-stats__group {
    display: flex;
    gap: 0.75rem;
    padding-right: 0.75rem;
}

.home-stats__chip {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-width: 11.5rem;
    padding: 0.7rem 0.95rem;
    border-radius: 1rem;
    background: var(--hs-card);
    border: 1px solid rgba(124, 58, 237, 0.12);
    box-shadow: 0 8px 22px rgba(49, 28, 92, 0.05);
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.home-stats__chip:hover {
    -webkit-transform: translate3d(0, -2px, 0);
    transform: translate3d(0, -2px, 0);
    box-shadow: 0 12px 28px rgba(49, 28, 92, 0.08);
}

.home-stats__icon {
    display: grid;
    place-items: center;
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 0.85rem;
    color: var(--hs-accent);
    background: linear-gradient(145deg, rgba(124, 58, 237, 0.12), rgba(219, 39, 119, 0.08));
    animation: homeStatsBob 3.6s ease-in-out infinite;
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
}

.home-stats__chip:nth-child(2n) .home-stats__icon { animation-delay: 0.4s; color: #a21caf; }
.home-stats__chip:nth-child(3n) .home-stats__icon { animation-delay: 0.8s; color: var(--hs-accent-2); }
.home-stats__chip:nth-child(4n) .home-stats__icon { animation-delay: 1.1s; color: #6d28d9; }

.home-stats__icon svg {
    width: 1.15rem;
    height: 1.15rem;
}

.home-stats__meta {
    display: flex;
    flex-direction: column;
    gap: 0.08rem;
    min-width: 0;
}

.home-stats__value {
    font-size: 1.15rem;
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
}

/* ---- Slim premium giveaway row ---- */
.home-gw-cta {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 2.45rem;
    margin-top: 0.7rem;
    padding: 0 0.9rem;
    border-radius: 999px;
    text-decoration: none;
    color: #fff;
    overflow: hidden;
    isolation: isolate;
    background: linear-gradient(100deg, #6d28d9 0%, #7c3aed 45%, #c026d3 100%);
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 6px 18px rgba(109, 40, 217, 0.22);
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.home-gw-cta:hover,
.home-gw-cta:focus-visible {
    -webkit-transform: translate3d(0, -1px, 0);
    transform: translate3d(0, -1px, 0);
    box-shadow: 0 8px 22px rgba(109, 40, 217, 0.3);
    outline: none;
}

.home-gw-cta:active {
    -webkit-transform: translate3d(0, 0, 0) scale(0.995);
    transform: translate3d(0, 0, 0) scale(0.995);
}

.home-gw-cta__shine {
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, transparent 35%, rgba(255,255,255,0.32) 50%, transparent 65%);
    -webkit-transform: translate3d(-130%, 0, 0);
    transform: translate3d(-130%, 0, 0);
    animation: homeGwShine 4.2s ease-in-out infinite;
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
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    filter: drop-shadow(0 2px 4px rgba(31, 22, 52, 0.18));
}

.home-gw-cta__art--present {
    width: 1.7rem;
    height: 1.7rem;
    animation: homeGwGift 2.5s ease-in-out infinite;
}

.home-gw-cta__art--diamonds {
    animation: homeGwSpark 2.1s ease-in-out infinite;
}

@keyframes homeStatsMarquee {
    from { -webkit-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); }
    to { -webkit-transform: translate3d(-50%, 0, 0); transform: translate3d(-50%, 0, 0); }
}

html[dir="rtl"] .home-stats__track {
    animation-name: homeStatsMarqueeRtl;
}

@keyframes homeStatsMarqueeRtl {
    from { -webkit-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); }
    to { -webkit-transform: translate3d(50%, 0, 0); transform: translate3d(50%, 0, 0); }
}

@keyframes homeStatsBob {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0); transform: translate3d(0, 0, 0); }
    50% { -webkit-transform: translate3d(0, -2px, 0); transform: translate3d(0, -2px, 0); }
}

@keyframes homeGwShine {
    0%, 58% { -webkit-transform: translate3d(-130%, 0, 0); transform: translate3d(-130%, 0, 0); }
    82%, 100% { -webkit-transform: translate3d(130%, 0, 0); transform: translate3d(130%, 0, 0); }
}

@keyframes homeGwGift {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) rotate(0deg); transform: translate3d(0, 0, 0) rotate(0deg); }
    40% { -webkit-transform: translate3d(0, -1.5px, 0) rotate(-5deg); transform: translate3d(0, -1.5px, 0) rotate(-5deg); }
    70% { -webkit-transform: translate3d(0, 0, 0) rotate(4deg); transform: translate3d(0, 0, 0) rotate(4deg); }
}

@keyframes homeGwSpark {
    0%, 100% { -webkit-transform: translate3d(0, 0, 0) scale(1); transform: translate3d(0, 0, 0) scale(1); }
    50% { -webkit-transform: translate3d(0, -1.5px, 0) scale(1.08); transform: translate3d(0, -1.5px, 0) scale(1.08); }
}

@media (max-width: 480px) {
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
    .home-stats__track,
    .home-stats__icon,
    .home-gw-cta__shine,
    .home-gw-cta__art--present,
    .home-gw-cta__art--diamonds {
        animation: none !important;
    }
    .home-stats__viewport {
        overflow-x: auto;
        mask-image: none;
        -webkit-mask-image: none;
    }
    .home-stats__track {
        width: auto;
        max-width: 100%;
        margin: 0 auto;
        justify-content: center;
        flex-wrap: wrap;
    }
    .home-stats__group:last-child {
        display: none;
    }
}

@media (max-width: 640px) {
    .home-stats__chip {
        min-width: 10.25rem;
        padding: 0.62rem 0.8rem;
    }
    .home-stats__value {
        font-size: 1.05rem;
    }
}
</style>

<script>
(function () {
    const root = document.querySelector('[data-home-stats]');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const chips = Array.from(root.querySelectorAll('[data-stat-chip]'));
    const firstGroupChips = Array.from(root.querySelectorAll('.home-stats__group:first-child [data-stat-chip]'));
    const targets = firstGroupChips.length ? firstGroupChips : chips;

    const format = (n) => {
        try {
            return new Intl.NumberFormat(document.documentElement.lang || undefined).format(n);
        } catch (e) {
            return String(n);
        }
    };

    const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

    const animateChip = (chip) => {
        const el = chip.querySelector('[data-stat-value]');
        const target = Number(chip.dataset.statTarget || 0);
        if (!el) return;
        if (reduceMotion || target <= 0) {
            el.textContent = format(target);
            return;
        }
        const duration = 1400 + Math.min(900, Math.log10(target + 10) * 280);
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min(1, (now - start) / duration);
            el.textContent = format(Math.round(target * easeOutCubic(p)));
            if (p < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = format(target);
                root.querySelectorAll('.home-stats__group:last-child [data-stat-chip]').forEach((dup, i) => {
                    const src = firstGroupChips[i];
                    const dupEl = dup.querySelector('[data-stat-value]');
                    if (src && dupEl) dupEl.textContent = format(Number(src.dataset.statTarget || 0));
                });
            }
        };
        requestAnimationFrame(tick);
    };

    let started = false;
    const start = () => {
        if (started) return;
        started = true;
        targets.forEach(animateChip);
    };

    if (targets.length) {
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        start();
                        io.disconnect();
                    }
                });
            }, { threshold: 0.25 });
            io.observe(root);
        } else {
            start();
        }
    }

    const track = root.querySelector('[data-stats-track]');
    if (track && !reduceMotion) {
        root.addEventListener('mouseenter', () => { track.style.animationPlayState = 'paused'; });
        root.addEventListener('mouseleave', () => { track.style.animationPlayState = 'running'; });
        root.addEventListener('touchstart', () => { track.style.animationPlayState = 'paused'; }, { passive: true });
        root.addEventListener('touchend', () => { track.style.animationPlayState = 'running'; }, { passive: true });
    }

})();
</script>
@endif
