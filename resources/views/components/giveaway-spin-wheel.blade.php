@php
    $giveawayParticipants = $giveawayParticipants ?? [];
    $spinUrl = $spinUrl ?? '#';
    $drawLiveEnabled = (bool) ($drawLiveEnabled ?? false);
    $giveawayTitle = $giveawayTitle ?? 'Giveaway';

    $gwWheelConfig = [
        'participants' => $giveawayParticipants,
        'spinUrl' => $spinUrl,
        'csrf' => csrf_token(),
        'drawLive' => $drawLiveEnabled,
        'title' => $giveawayTitle,
        'text' => [
            'ready' => __('giveaway.wheel_ready'),
            'loading' => __('giveaway.wheel_loading'),
            'spinning' => __('giveaway.wheel_spinning'),
            'spin' => __('giveaway.wheel_spin'),
            'previewBadge' => __('giveaway.wheel_preview_badge'),
            'liveBadge' => __('giveaway.wheel_live_badge'),
            'previewTitle' => __('giveaway.wheel_preview_title'),
            'liveTitle' => __('giveaway.wheel_live_title'),
            'error' => __('giveaway.wheel_error'),
            'noParticipants' => __('giveaway.wheel_no_participants'),
            'metaTopups' => __('giveaway.wheel_meta_topups'),
            'metaJoined' => __('giveaway.wheel_meta_joined'),
            'metaEmail' => __('giveaway.wheel_meta_email'),
        ],
    ];
@endphp

<section class="gw-wheel" data-gw-wheel aria-label="{{ __('giveaway.wheel_aria') }}">
    <div class="gw-wheel__head">
        <p class="gw-wheel__eyebrow">{{ __('giveaway.wheel_eyebrow') }}</p>
        <h2 class="gw-wheel__title">{{ __('giveaway.wheel_title') }}</h2>
        <p class="gw-wheel__sub">
            {{ $drawLiveEnabled ? __('giveaway.wheel_live_hint') : __('giveaway.wheel_preview_hint') }}
        </p>
        <p class="gw-wheel__count">{{ __('giveaway.wheel_pool_count', ['count' => count($giveawayParticipants)]) }}</p>
    </div>

    <div class="gw-wheel__stage">
        <div class="gw-wheel__pointer" aria-hidden="true"></div>
        <div class="gw-wheel__glow" aria-hidden="true"></div>
        <canvas class="gw-wheel__canvas" data-gw-canvas width="420" height="420" aria-hidden="true"></canvas>
        <div class="gw-wheel__hub" aria-hidden="true">
            <span>{{ __('giveaway.wheel_hub') }}</span>
        </div>
        <div class="gw-wheel__loadbar" data-gw-loadbar aria-hidden="true">
            <span data-gw-loadfill></span>
        </div>
        <p class="gw-wheel__status" data-gw-status>{{ __('giveaway.wheel_ready') }}</p>
    </div>

    <div class="gw-wheel__actions">
        <button type="button" class="gw-wheel__spin" data-gw-spin>
            {{ __('giveaway.wheel_spin') }}
        </button>
    </div>

    <div class="gw-wheel__modal" data-gw-modal hidden>
        <div class="gw-wheel__modal-card">
            <p class="gw-wheel__modal-badge" data-gw-modal-badge></p>
            <h3 data-gw-modal-title></h3>
            <p class="gw-wheel__modal-name" data-gw-modal-name></p>
            <p class="gw-wheel__modal-email" data-gw-modal-email></p>
            <div class="gw-wheel__modal-stats">
                <div class="gw-wheel__stat">
                    <span class="gw-wheel__stat-label" data-gw-stat-topups-label>{{ __('giveaway.wheel_meta_topups') }}</span>
                    <strong class="gw-wheel__stat-value" data-gw-modal-topups>—</strong>
                </div>
                <div class="gw-wheel__stat">
                    <span class="gw-wheel__stat-label" data-gw-stat-joined-label>{{ __('giveaway.wheel_meta_joined') }}</span>
                    <strong class="gw-wheel__stat-value" data-gw-modal-joined>—</strong>
                </div>
            </div>
            <p class="gw-wheel__modal-text" data-gw-modal-text></p>
            <button type="button" class="gw-wheel__spin" data-gw-modal-close>{{ __('giveaway.wheel_close') }}</button>
        </div>
    </div>

    <canvas class="gw-wheel__confetti" data-gw-confetti width="1" height="1" aria-hidden="true"></canvas>
</section>

<script type="application/json" data-gw-wheel-config>@json($gwWheelConfig)</script>

<style>
.gw-wheel {
    --gw-ink: #1a1030;
    --gw-gold: #f5d76e;
    --gw-violet: #7c3aed;
    --gw-pink: #db2777;
    position: relative;
    margin-top: 0.85rem;
    padding: 1.1rem 1rem 1.25rem;
    border-radius: 1.35rem;
    overflow: hidden;
    background:
        radial-gradient(120% 90% at 50% 0%, rgba(245, 215, 110, 0.18), transparent 55%),
        radial-gradient(90% 80% at 100% 100%, rgba(219, 39, 119, 0.16), transparent 50%),
        linear-gradient(160deg, #12091f 0%, #1f1238 48%, #2a1450 100%);
    border: 1px solid rgba(245, 215, 110, 0.22);
    box-shadow: 0 18px 40px rgba(18, 9, 31, 0.35);
    color: #fff;
}
.gw-wheel__head { text-align: center; margin-bottom: 0.85rem; }
.gw-wheel__eyebrow {
    margin: 0 0 0.35rem;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(245, 215, 110, 0.9);
}
.gw-wheel__title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -0.02em;
}
.gw-wheel__sub, .gw-wheel__count {
    margin: 0.35rem 0 0;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.72);
}
.gw-wheel__count { font-weight: 700; color: rgba(245, 215, 110, 0.85); }

.gw-wheel__stage {
    position: relative;
    width: min(100%, 360px);
    margin: 0 auto;
    aspect-ratio: 1;
}
.gw-wheel__canvas {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: block;
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    box-shadow:
        inset 0 0 0 6px rgba(245, 215, 110, 0.35),
        0 0 0 10px rgba(0,0,0,0.25),
        0 16px 40px rgba(0,0,0,0.45);
}
.gw-wheel__pointer {
    position: absolute;
    top: -4px;
    left: 50%;
    z-index: 4;
    width: 0;
    height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-top: 22px solid var(--gw-gold);
    -webkit-transform: translate3d(-50%, 0, 0);
    transform: translate3d(-50%, 0, 0);
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.35));
}
.gw-wheel__glow {
    position: absolute;
    inset: 8%;
    border-radius: 50%;
    pointer-events: none;
    background: radial-gradient(circle, rgba(245,215,110,0.18), transparent 65%);
    animation: gwWheelPulse 2.8s ease-in-out infinite;
}
.gw-wheel__hub {
    position: absolute;
    inset: 50% auto auto 50%;
    z-index: 3;
    width: 72px;
    height: 72px;
    margin: -36px 0 0 -36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: radial-gradient(circle at 35% 30%, #fff7d1, #f5d76e 45%, #b45309 100%);
    color: #3b1d0b;
    font-size: 0.62rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow: 0 8px 18px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.55);
}
.gw-wheel__loadbar {
    position: absolute;
    left: 12%;
    right: 12%;
    bottom: 10%;
    height: 6px;
    border-radius: 999px;
    background: rgba(255,255,255,0.12);
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.25s ease;
    z-index: 5;
}
.gw-wheel__loadbar.is-on { opacity: 1; }
.gw-wheel__loadbar span {
    display: block;
    height: 100%;
    width: 0%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--gw-violet), var(--gw-gold), var(--gw-pink));
    transition: width 0.12s linear;
}
.gw-wheel__status {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -0.15rem;
    margin: 0;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: rgba(255,255,255,0.82);
}
.gw-wheel__actions {
    display: flex;
    justify-content: center;
    margin-top: 1.35rem;
}
.gw-wheel__spin {
    appearance: none;
    border: 0;
    cursor: pointer;
    padding: 0.85rem 1.6rem;
    border-radius: 999px;
    font-size: 0.92rem;
    font-weight: 900;
    letter-spacing: 0.02em;
    color: #2b1608;
    background: linear-gradient(180deg, #ffe7a0 0%, #f5d76e 45%, #d4a017 100%);
    box-shadow: 0 10px 24px rgba(212, 160, 23, 0.35), inset 0 1px 0 rgba(255,255,255,0.55);
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.gw-wheel__spin:hover { -webkit-transform: translate3d(0, -1px, 0); transform: translate3d(0, -1px, 0); }
.gw-wheel__spin:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
}
.gw-wheel__modal {
    position: absolute;
    inset: 0;
    z-index: 20;
    display: grid;
    place-items: center;
    padding: 1rem;
    background: rgba(8, 4, 16, 0.72);
    backdrop-filter: blur(4px);
}
.gw-wheel__modal[hidden] { display: none !important; }
.gw-wheel__modal-card {
    width: min(100%, 340px);
    padding: 1.35rem 1.2rem 1.2rem;
    border-radius: 1.2rem;
    text-align: center;
    background: linear-gradient(160deg, #2a1450, #1a1030);
    border: 1px solid rgba(245, 215, 110, 0.35);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}
.gw-wheel__modal-badge {
    display: inline-flex;
    margin: 0 0 0.5rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgba(245, 215, 110, 0.18);
    color: var(--gw-gold);
}
.gw-wheel__modal-card h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 900;
}
.gw-wheel__modal-name {
    margin: 0.55rem 0 0.2rem;
    font-size: 1.45rem;
    font-weight: 900;
    color: var(--gw-gold);
    line-height: 1.2;
    word-break: break-word;
}
.gw-wheel__modal-email {
    margin: 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: rgba(255,255,255,0.72);
    word-break: break-all;
}
.gw-wheel__modal-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem;
    margin: 0.95rem 0 0.75rem;
}
.gw-wheel__stat {
    padding: 0.65rem 0.5rem;
    border-radius: 0.85rem;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
}
.gw-wheel__stat-label {
    display: block;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.55);
}
.gw-wheel__stat-value {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.95rem;
    font-weight: 900;
    color: #fff;
}
.gw-wheel__modal-text {
    margin: 0 0 1rem;
    font-size: 0.78rem;
    line-height: 1.4;
    color: rgba(255,255,255,0.7);
}
.gw-wheel__confetti {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 15;
}
@keyframes gwWheelPulse {
    0%, 100% { opacity: 0.55; -webkit-transform: scale(1); transform: scale(1); }
    50% { opacity: 1; -webkit-transform: scale(1.03); transform: scale(1.03); }
}
@media (prefers-reduced-motion: reduce) {
    .gw-wheel__glow { animation: none !important; }
}
</style>

<script>
(function () {
    const root = document.querySelector('[data-gw-wheel]');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    const cfgEl = document.querySelector('[data-gw-wheel-config]');
    let cfg = {};
    try { cfg = JSON.parse(cfgEl?.textContent || '{}'); } catch (e) { cfg = {}; }

    const canvas = root.querySelector('[data-gw-canvas]');
    const ctx = canvas.getContext('2d');
    const spinBtn = root.querySelector('[data-gw-spin]');
    const statusEl = root.querySelector('[data-gw-status]');
    const loadbar = root.querySelector('[data-gw-loadbar]');
    const loadfill = root.querySelector('[data-gw-loadfill]');
    const modal = root.querySelector('[data-gw-modal]');
    const modalBadge = root.querySelector('[data-gw-modal-badge]');
    const modalTitle = root.querySelector('[data-gw-modal-title]');
    const modalName = root.querySelector('[data-gw-modal-name]');
    const modalEmail = root.querySelector('[data-gw-modal-email]');
    const modalTopups = root.querySelector('[data-gw-modal-topups]');
    const modalJoined = root.querySelector('[data-gw-modal-joined]');
    const modalText = root.querySelector('[data-gw-modal-text]');
    const modalClose = root.querySelector('[data-gw-modal-close]');
    const confettiCanvas = root.querySelector('[data-gw-confetti]');
    const confettiCtx = confettiCanvas.getContext('2d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const colors = ['#7c3aed', '#a21caf', '#db2777', '#9333ea', '#6d28d9', '#c026d3', '#e11d48', '#8b5cf6'];
    let segments = [];
    let rotation = 0;
    let spinning = false;
    let confettiBits = [];
    let confettiRaf = 0;

    function resize() {
        const size = Math.min(420, Math.floor(canvas.clientWidth || 320));
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = size * dpr;
        canvas.height = size * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        confettiCanvas.width = root.clientWidth * dpr;
        confettiCanvas.height = root.clientHeight * dpr;
        confettiCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        draw();
    }

    function segmentLabel(seg, n, size) {
        const name = (seg?.name || '?').trim() || '?';
        if (n >= 24) return (seg?.initial || name.slice(0, 1)).toUpperCase();
        if (n >= 16) return name.slice(0, 6);
        if (n >= 12) return name.slice(0, 8);
        return name.slice(0, 10);
    }

    function draw() {
        const size = canvas.clientWidth || 320;
        const cx = size / 2;
        const cy = size / 2;
        const radius = size / 2 - 8;
        ctx.clearRect(0, 0, size, size);

        const n = Math.max(segments.length, 1);
        const arc = (Math.PI * 2) / n;
        const fontPx = n >= 24
            ? Math.max(9, Math.floor(size * 0.028))
            : n >= 14
                ? Math.max(10, Math.floor(size * 0.032))
                : Math.max(11, Math.floor(size * 0.042));

        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(rotation);

        for (let i = 0; i < n; i++) {
            const start = i * arc - Math.PI / 2;
            const end = start + arc;
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.arc(0, 0, radius, start, end);
            ctx.closePath();
            ctx.fillStyle = colors[i % colors.length];
            ctx.fill();
            ctx.strokeStyle = 'rgba(255,255,255,0.22)';
            ctx.lineWidth = n >= 20 ? 1 : 2;
            ctx.stroke();

            const seg = segments[i];
            if (!seg) continue;
            ctx.save();
            ctx.rotate(start + arc / 2);
            ctx.textAlign = 'right';
            ctx.fillStyle = '#fff';
            ctx.font = '700 ' + fontPx + 'px system-ui,sans-serif';
            ctx.fillText(segmentLabel(seg, n, size), radius - (n >= 20 ? 10 : 16), 4);
            ctx.restore();
        }

        ctx.restore();

        // rim
        ctx.beginPath();
        ctx.arc(cx, cy, radius, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(245,215,110,0.55)';
        ctx.lineWidth = 5;
        ctx.stroke();
    }

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    function wait(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    async function loadNamesOntoWheel(pool, finalSegments) {
        segments = [];
        draw();
        loadbar?.classList.add('is-on');
        setStatus(cfg.text?.loading || 'Loading players…');

        const target = finalSegments.length ? finalSegments : pool.slice();
        const steps = target.length;
        const stepDelay = steps > 0 ? Math.max(18, Math.min(85, Math.floor(1400 / steps))) : 40;
        for (let i = 0; i < steps; i++) {
            segments = target.slice(0, i + 1);
            if (loadfill) loadfill.style.width = Math.round(((i + 1) / steps) * 100) + '%';
            draw();
            if (!reduceMotion) await wait(stepDelay + Math.random() * 20);
        }
        segments = target.slice();
        draw();
        if (loadfill) loadfill.style.width = '100%';
        await wait(reduceMotion ? 0 : 180);
        loadbar?.classList.remove('is-on');
        if (loadfill) loadfill.style.width = '0%';
    }

    function targetRotationFor(index, count) {
        const arc = (Math.PI * 2) / count;
        const center = index * arc + arc / 2;
        // Pointer is at top; wheel rotation should bring segment center under pointer.
        const base = (Math.PI * 2) * (5 + Math.floor(Math.random() * 3));
        return base + ((Math.PI * 2) - center);
    }

    function animateTo(target) {
        return new Promise((resolve) => {
            if (reduceMotion) {
                rotation = target;
                draw();
                resolve();
                return;
            }
            const start = rotation;
            const delta = target - start;
            const duration = 5200 + Math.random() * 900;
            const t0 = performance.now();
            const tick = (now) => {
                const p = Math.min(1, (now - t0) / duration);
                rotation = start + delta * easeOutQuart(p);
                draw();
                if (p < 1) requestAnimationFrame(tick);
                else resolve();
            };
            requestAnimationFrame(tick);
        });
    }

    function burstConfetti() {
        if (reduceMotion || !confettiCanvas) return;
        const w = root.clientWidth;
        const h = root.clientHeight;
        confettiBits = Array.from({ length: 70 }, () => ({
            x: w * 0.5 + (Math.random() - 0.5) * 40,
            y: h * 0.35,
            vx: (Math.random() - 0.5) * 7,
            vy: -Math.random() * 8 - 2,
            g: 0.18 + Math.random() * 0.08,
            life: 70 + Math.random() * 40,
            color: colors[Math.floor(Math.random() * colors.length)],
            s: 3 + Math.random() * 4,
        }));
        cancelAnimationFrame(confettiRaf);
        const step = () => {
            confettiCtx.clearRect(0, 0, w, h);
            confettiBits = confettiBits.filter((b) => b.life > 0);
            confettiBits.forEach((b) => {
                b.vy += b.g;
                b.x += b.vx;
                b.y += b.vy;
                b.life -= 1;
                confettiCtx.fillStyle = b.color;
                confettiCtx.fillRect(b.x, b.y, b.s, b.s * 0.7);
            });
            if (confettiBits.length) confettiRaf = requestAnimationFrame(step);
        };
        confettiRaf = requestAnimationFrame(step);
    }

    function showModal(payload) {
        const preview = !!payload.preview;
        const winner = payload.winner || {};
        modalBadge.textContent = preview ? (cfg.text?.previewBadge || 'PREVIEW') : (cfg.text?.liveBadge || 'LIVE');
        modalTitle.textContent = preview ? (cfg.text?.previewTitle || 'Preview winner') : (cfg.text?.liveTitle || 'Winner locked');
        modalName.textContent = winner.full_name || winner.name || '—';
        if (modalEmail) modalEmail.textContent = winner.email || '—';
        if (modalTopups) modalTopups.textContent = String(Number.isFinite(Number(winner.topups)) ? Number(winner.topups) : 0);
        if (modalJoined) modalJoined.textContent = winner.joined_label || '—';
        modalText.textContent = payload.message || '';
        modal.hidden = false;
        burstConfetti();
    }

    async function requestSpin() {
        const res = await fetch(cfg.spinUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({}),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
            throw new Error(data.message || cfg.text?.error || 'Spin failed');
        }
        return data;
    }

    async function onSpin() {
        if (spinning) return;
        const pool = Array.isArray(cfg.participants) ? cfg.participants : [];
        if (!pool.length) {
            setStatus(cfg.text?.noParticipants || 'No approved players yet');
            return;
        }
        spinning = true;
        spinBtn.disabled = true;
        modal.hidden = true;
        try {
            setStatus(cfg.text?.loading || 'Loading…');
            const data = await requestSpin();
            const finalSegments = Array.isArray(data.segments) && data.segments.length
                ? data.segments
                : pool.slice();
            await loadNamesOntoWheel(pool, finalSegments);
            setStatus(cfg.text?.spinning || 'Spinning…');
            const idx = Number.isInteger(data.winner_index) ? data.winner_index : 0;
            const target = targetRotationFor(idx, finalSegments.length);
            await animateTo(target);
            setStatus(data.winner?.full_name || data.winner?.name || '');
            showModal(data);
        } catch (err) {
            setStatus(err.message || cfg.text?.error || 'Error');
        } finally {
            spinning = false;
            spinBtn.disabled = false;
            spinBtn.textContent = cfg.text?.spin || 'Spin';
        }
    }

    spinBtn?.addEventListener('click', onSpin);
    modalClose?.addEventListener('click', () => { modal.hidden = true; });
    window.addEventListener('resize', resize, { passive: true });

    // Idle placeholder slices
    segments = Array.from({ length: 8 }, (_, i) => ({
        entry_id: -i - 1,
        name: '· · ·',
        initial: '·',
    }));
    resize();
    setStatus(cfg.text?.ready || 'Ready');
})();
</script>
