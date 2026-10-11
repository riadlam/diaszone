@php
    /** @var array{members?:int,players_topped_up?:int,topups_delivered?:int,products_topped_up?:int,games_live?:int} $publicStats */
    $publicStats = $publicStats ?? [];
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
            'key' => 'products_topped_up',
            'value' => (int) ($publicStats['products_topped_up'] ?? 0),
            'label' => __('home.stats_products'),
            'icon' => 'pack',
        ],
        [
            'key' => 'games_live',
            'value' => (int) ($publicStats['games_live'] ?? 0),
            'label' => __('home.stats_games'),
            'icon' => 'game',
        ],
    ];
    $items = array_values(array_filter($items, fn ($row) => $row['value'] > 0));
@endphp

@if(count($items) > 0)
<section class="home-stats" data-home-stats aria-label="{{ __('home.stats_aria') }}">
    <div class="home-stats__inner">
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
                                    @elseif($item['icon'] === 'pack')
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5v-7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 12v8M4 8.5l8 3.5 8-3.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
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
    </div>
</section>

<style>
.home-stats {
    --hs-ink: #1f1634;
    --hs-muted: #6b6280;
    --hs-accent: #7c3aed;
    --hs-accent-2: #db2777;
    --hs-soft: rgba(124, 58, 237, 0.08);
    --hs-card: rgba(255, 255, 255, 0.92);
    padding: 0.35rem 0 0.85rem;
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
    transform: translateZ(0);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.home-stats__chip:hover {
    transform: translateY(-2px);
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
}

.home-stats__chip:nth-child(2n) .home-stats__icon { animation-delay: 0.4s; color: #a21caf; }
.home-stats__chip:nth-child(3n) .home-stats__icon { animation-delay: 0.8s; color: var(--hs-accent-2); }
.home-stats__chip:nth-child(4n) .home-stats__icon { animation-delay: 1.1s; color: #6d28d9; }
.home-stats__chip:nth-child(5n) .home-stats__icon { animation-delay: 1.5s; color: #c026d3; }

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

@keyframes homeStatsMarquee {
    from { transform: translate3d(0, 0, 0); }
    to { transform: translate3d(-50%, 0, 0); }
}

html[dir="rtl"] .home-stats__track {
    animation-name: homeStatsMarqueeRtl;
}

@keyframes homeStatsMarqueeRtl {
    from { transform: translate3d(0, 0, 0); }
    to { transform: translate3d(50%, 0, 0); }
}

@keyframes homeStatsBob {
    0%, 100% { transform: translate3d(0, 0, 0); }
    50% { transform: translate3d(0, -2px, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .home-stats__track,
    .home-stats__icon {
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
    // Only animate the first group so the marquee duplicate stays in sync visually after count-up.
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
            const value = Math.round(target * easeOutCubic(p));
            el.textContent = format(value);
            if (p < 1) requestAnimationFrame(tick);
            else {
                el.textContent = format(target);
                // Mirror final values onto the duplicate group.
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

    // Pause marquee while hovering for readability.
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
