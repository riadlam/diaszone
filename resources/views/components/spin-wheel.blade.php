@php
    $segments = $prizes ?? [];
    $spinsLeft = $spinsLeft ?? 0;
    $unlimitedSpins = $unlimitedSpins ?? false;
    $spinUrl = $spinUrl ?? '#';
    $rewardsUrl = $rewardsUrl ?? '#';
    $gameUrl = $gameUrl ?? url('/');
    $loginUrl = $loginUrl ?? route('login');
    $requiresLogin = $requiresLogin ?? false;
    $milestoneMode = $milestoneMode ?? true;
    $initialClaims = $initialClaims ?? [];

    if ($initialClaims instanceof \Illuminate\Support\Collection) {
        $initialClaims = $initialClaims->values()->all();
    }

    $whatsappNumber = '213556988175';
    $facebookUrl = 'https://www.facebook.com/people/Dias-Zone/61584183358240/';

    $wheelConfig = [
        'segments' => array_map(function ($prize) {
            return [
                'key' => $prize['key'] ?? null,
                'type' => $prize['type'] ?? 'currency',
                'label' => $prize['label'] ?? '',
                'sub' => $prize['sub'] ?? '',
                'style' => $prize['style'] ?? 'purple',
                'weight' => $prize['weight'] ?? 1,
                'reward_id' => $prize['reward_id'] ?? null,
                'draws_required' => $prize['draws_required'] ?? null,
                'icon' => $prize['icon'] ?? null,
                'icon_fit' => $prize['icon_fit'] ?? 'contain',
                'gallery' => array_values($prize['gallery'] ?? []),
            ];
        }, $segments),
        'spinUrl' => $spinUrl,
        'rewardsUrl' => $rewardsUrl,
        'gameUrl' => $gameUrl,
        'loginUrl' => $loginUrl,
        'requiresLogin' => (bool) $requiresLogin,
        'spinsLeft' => (int) $spinsLeft,
        'unlimitedSpins' => (bool) $unlimitedSpins,
        'csrf' => csrf_token(),
        'milestoneMode' => (bool) $milestoneMode,
        'initialClaims' => $initialClaims,
        'contact' => [
            'whatsapp' => $whatsappNumber,
            'facebook' => $facebookUrl,
        ],
        'text' => [
            'congrats' => __('event.congrats'),
            'you_won' => __('event.you_won'),
            'no_win_title' => __('event.no_win_title'),
            'no_win_text' => __('event.no_win_text'),
            'no_spins_text' => __('event.no_spins_text'),
            'spin' => __('event.spin'),
            'spinning' => __('event.spinning'),
            'claim' => __('event.claim'),
            'close' => __('event.close'),
            'error' => __('event.error'),
            'contact_title' => __('event.contact_title'),
            'contact_text' => __('event.contact_text'),
            'claim_code' => __('event.claim_code'),
            'unlocked_at' => __('event.unlocked_at'),
            'contact_whatsapp' => __('event.contact_whatsapp'),
            'contact_facebook' => __('event.contact_facebook'),
            'copy_code' => __('event.copy_code'),
            'code_copied' => __('event.code_copied'),
            'discount_ready' => __('event.discount_ready'),
            'discount_ready_text' => __('event.discount_ready_text'),
            'coupon_code' => __('event.coupon_code'),
            'my_rewards' => __('event.my_rewards'),
            'no_rewards_yet' => __('event.no_rewards_yet'),
            'login_required' => __('event.login_required'),
            'login_cta' => __('event.login_cta'),
            'login_modal_title' => __('event.login_modal_title'),
            'whatsapp_prefill' => __('event.whatsapp_prefill', ['code' => ':code']),
        ],
    ];
@endphp

<div class="wheel-stage" id="wheelStage" data-spins-left="{{ $spinsLeft }}" data-unlimited-spins="{{ $unlimitedSpins ? 'true' : 'false' }}">
    <script type="application/json" id="wheelConfig">@json($wheelConfig)</script>

    <canvas class="wheel-confetti" id="wheelConfetti" aria-hidden="true"></canvas>

    <div class="wheel-shell">
        <div class="wheel-pointer" id="wheelPointer" aria-hidden="true">
            <svg viewBox="0 0 30 38" width="30" height="38">
                <path d="M15 38 L2 13 A14.5 14.5 0 0 1 28 13 Z" fill="#7c3aed"/>
                <circle cx="15" cy="13" r="4" fill="#ffffff" opacity="0.9"/>
            </svg>
        </div>

        <div class="wheel-frame">
            <div class="wheel-canvas-wrap" id="wheelCanvasWrap">
                <canvas id="wheelCanvas" role="img"
                        aria-label="{{ __('event.wheel_aria', ['count' => count($segments)]) }}"></canvas>
            </div>

            <button type="button" class="wheel-hub" id="wheelSpinButton" aria-live="polite">
                <span class="wheel-hub__label" id="wheelSpinLabel">{{ __('event.spin') }}</span>
                <span class="wheel-hub__hint" id="wheelSpinHint">{{ $unlimitedSpins ? '∞' : $spinsLeft }}</span>
            </button>
        </div>
    </div>

    <div class="wheel-toolbar">
        <div class="wheel-spins">
            <span class="text-gray-500 font-medium">{{ __('event.spins_left') }}</span>
            <span class="wheel-spins__value" id="wheelSpinsLeft">{{ $unlimitedSpins ? '∞' : $spinsLeft }}</span>
        </div>

        <button type="button" class="wheel-sound" id="wheelSoundToggle" aria-pressed="true"
                data-label-on="{{ __('event.sound_on') }}" data-label-off="{{ __('event.sound_off') }}">
            <svg class="wheel-sound__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            </svg>
            <svg class="wheel-sound__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <line x1="23" y1="9" x2="17" y2="15"></line>
                <line x1="17" y1="9" x2="23" y2="15"></line>
            </svg>
            <span id="wheelSoundLabel">{{ __('event.sound_on') }}</span>
        </button>
    </div>

    <div class="wheel-toast" id="wheelToast" role="status" aria-live="polite"></div>

    <div class="wheel-icon-preview" id="wheelIconPreview" role="dialog" aria-modal="true" aria-labelledby="wheelIconPreviewTitle">
        <button type="button" class="wheel-icon-preview__backdrop" data-icon-preview-close aria-label="{{ __('event.close') }}"></button>
        <div class="wheel-icon-preview__card">
            <button type="button" class="wheel-icon-preview__close" data-icon-preview-close aria-label="{{ __('event.close') }}">&times;</button>
            <div class="wheel-icon-preview__image-wrap">
                <img id="wheelIconPreviewImage" class="wheel-icon-preview__image" alt="">
                <span id="wheelIconPreviewFallback" class="wheel-icon-preview__fallback" aria-hidden="true">%</span>

                <button type="button" class="wheel-icon-preview__nav wheel-icon-preview__nav--prev"
                        id="wheelIconPreviewPrev" aria-label="{{ __('event.previous_image') }}" hidden>&#8249;</button>
                <button type="button" class="wheel-icon-preview__nav wheel-icon-preview__nav--next"
                        id="wheelIconPreviewNext" aria-label="{{ __('event.next_image') }}" hidden>&#8250;</button>
            </div>
            <div class="wheel-icon-preview__dots" id="wheelIconPreviewDots" hidden></div>
            <h3 class="wheel-icon-preview__title" id="wheelIconPreviewTitle"></h3>
            <p class="wheel-icon-preview__sub" id="wheelIconPreviewSub"></p>
        </div>
    </div>

    <div class="wheel-result" id="wheelResult" role="dialog" aria-modal="true" aria-labelledby="wheelResultTitle">
        <div class="wheel-result__backdrop" data-wheel-close></div>
        <div class="wheel-result__card">
            <button type="button" class="wheel-result__dismiss" data-wheel-close aria-label="{{ __('event.close') }}">&times;</button>

            <div class="wheel-result__badge" id="wheelResultBadge" aria-hidden="true"></div>
            <p class="wheel-result__kicker" id="wheelResultKicker"></p>
            <h3 class="wheel-result__title" id="wheelResultTitle"></h3>
            <p class="wheel-result__prize" id="wheelResultPrize"></p>
            <p class="wheel-result__meta" id="wheelResultMeta"></p>

            <div class="wheel-code" id="wheelResultCodeBlock" hidden>
                <span class="wheel-code__label" id="wheelResultCodeLabel"></span>
                <div class="wheel-code__row">
                    <code class="wheel-code__value" id="wheelResultCode"></code>
                    <button type="button" class="wheel-code__copy" id="wheelResultCopy"></button>
                </div>
            </div>

            <p class="wheel-result__note-title" id="wheelResultNoteTitle" hidden></p>
            <p class="wheel-result__note" id="wheelResultNote" hidden></p>

            <div class="wheel-result__actions">
                <a href="#" class="wheel-btn wheel-btn--whatsapp" id="wheelResultWhatsapp" target="_blank" rel="noopener" hidden></a>
                <a href="{{ $facebookUrl }}" class="wheel-btn wheel-btn--facebook" id="wheelResultFacebook" target="_blank" rel="noopener" hidden></a>
                <a href="{{ $gameUrl }}" class="wheel-btn wheel-btn--primary" id="wheelResultUse" hidden></a>
                <button type="button" class="wheel-btn wheel-btn--ghost" id="wheelResultClose" data-wheel-close>{{ __('event.close') }}</button>
            </div>
        </div>
    </div>

    <div class="wheel-login" id="wheelLoginModal" role="dialog" aria-modal="true" aria-labelledby="wheelLoginTitle">
        <button type="button" class="wheel-login__backdrop" data-login-close aria-label="{{ __('event.close') }}"></button>
        <div class="wheel-login__card">
            <button type="button" class="wheel-login__dismiss" data-login-close aria-label="{{ __('event.close') }}">&times;</button>
            <div class="wheel-login__badge" aria-hidden="true">★</div>
            <h3 class="wheel-login__title" id="wheelLoginTitle">{{ __('event.login_modal_title') }}</h3>
            <p class="wheel-login__text">{{ __('event.login_required') }}</p>
            <div class="wheel-login__actions">
                <a href="{{ $loginUrl }}" class="wheel-btn wheel-btn--primary" id="wheelLoginCta">{{ __('event.login_cta') }}</a>
                <button type="button" class="wheel-btn wheel-btn--ghost" data-login-close>{{ __('event.close') }}</button>
            </div>
        </div>
    </div>

    <div class="wheel-rewards" id="myRewardsPanel" role="dialog" aria-modal="true" aria-labelledby="myRewardsTitle">
        <div class="wheel-rewards__backdrop" data-rewards-close></div>
        <div class="wheel-rewards__card">
            <div class="wheel-rewards__head">
                <h3 class="wheel-rewards__title" id="myRewardsTitle">{{ __('event.my_rewards') }}</h3>
                <button type="button" class="wheel-rewards__dismiss" data-rewards-close aria-label="{{ __('event.close') }}">&times;</button>
            </div>
            <div class="wheel-rewards__body">
                <p class="wheel-rewards__empty" id="myRewardsEmpty">{{ __('event.no_rewards_yet') }}</p>
                <ul class="wheel-rewards__list" id="myRewardsList"></ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.wheel-stage {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
    width: 100%;
}

.wheel-confetti {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
    /* Above the result modal so the celebration falls over the whole screen. */
    z-index: 200;
}

.wheel-confetti.is-active {
    opacity: 1;
}

.wheel-shell {
    position: relative;
    width: min(460px, 88vw);
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wheel-frame {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    padding: 10px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 18px 40px -24px rgba(17, 24, 39, 0.45);
}

.wheel-canvas-wrap {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    touch-action: manipulation;
}

#wheelCanvas {
    display: block;
    width: 100%;
    height: 100%;
    transform: rotate(0rad);
    will-change: transform;
    backface-visibility: hidden;
}

/* ---------- Reward icon preview ---------- */

.wheel-icon-preview {
    position: fixed;
    inset: 0;
    z-index: 130;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.wheel-icon-preview.is-open {
    display: flex;
}

.wheel-icon-preview__backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    border: 0;
    background: rgba(17, 24, 39, 0.55);
    cursor: default;
    animation: wheelFade 0.2s ease both;
}

.wheel-icon-preview__card {
    position: relative;
    width: min(420px, 100%);
    padding: 1.5rem;
    border: 1px solid rgba(124, 58, 237, 0.14);
    border-radius: 1.5rem;
    background: #fff;
    text-align: center;
    box-shadow: 0 24px 70px -30px rgba(17, 24, 39, 0.6);
    animation: wheelPop 0.25s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.wheel-icon-preview__close {
    position: absolute;
    top: 0.5rem;
    inset-inline-end: 0.6rem;
    width: 2rem;
    height: 2rem;
    border: 0;
    border-radius: 0.6rem;
    background: transparent;
    color: #6b7280;
    font-size: 1.5rem;
    line-height: 1;
}

.wheel-icon-preview__image-wrap {
    position: relative;
    width: 100%;
    height: min(60vh, 320px);
    margin: 0 auto 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #ede9fe;
    border-radius: 1.25rem;
    background: linear-gradient(145deg, #faf5ff, #fff);
    touch-action: pan-y;
}

.wheel-icon-preview__image {
    display: none;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0.5rem;
}

.wheel-icon-preview__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(124, 58, 237, 0.18);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.92);
    color: #4c1d95;
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
}

.wheel-icon-preview__nav--prev { inset-inline-start: 0.5rem; }
.wheel-icon-preview__nav--next { inset-inline-end: 0.5rem; }

.wheel-icon-preview__dots {
    display: flex;
    justify-content: center;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
}

.wheel-icon-preview__dot {
    width: 0.45rem;
    height: 0.45rem;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: rgba(124, 58, 237, 0.25);
    cursor: pointer;
}

.wheel-icon-preview__dot.is-active {
    width: 1.1rem;
    background: #7c3aed;
}

.wheel-icon-preview__fallback {
    color: #7c3aed;
    font-size: 5rem;
    font-weight: 900;
}

.wheel-icon-preview__title {
    margin: 0;
    color: #111827;
    font-size: 1.2rem;
    font-weight: 800;
}

.wheel-icon-preview__sub {
    margin: 0.3rem 0 0;
    color: #6b7280;
    font-size: 0.85rem;
}

.wheel-pointer {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    transform-origin: 50% 15%;
    z-index: 6;
    filter: drop-shadow(0 3px 5px rgba(17, 24, 39, 0.28));
}

.wheel-hub {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 26%;
    height: 26%;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    z-index: 8;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1px;
    color: #ffffff;
    background: linear-gradient(180deg, #8b5cf6, #6d28d9);
    box-shadow: 0 0 0 6px #ffffff, 0 8px 18px -6px rgba(76, 29, 149, 0.55);
    transition: transform 0.15s ease, background 0.2s ease;
}

.wheel-hub:hover:not(:disabled) {
    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    transform: translate(-50%, -50%) scale(1.04);
}

.wheel-hub:active:not(:disabled) {
    transform: translate(-50%, -50%) scale(0.96);
}

.wheel-hub:disabled {
    cursor: not-allowed;
    background: linear-gradient(180deg, #cbd5e1, #94a3b8);
    box-shadow: 0 0 0 6px #ffffff, 0 6px 14px -8px rgba(17, 24, 39, 0.4);
}

.wheel-hub__label {
    font-size: clamp(0.85rem, 2.4vw, 1.15rem);
    font-weight: 800;
    letter-spacing: 0.06em;
    line-height: 1;
}

.wheel-hub__hint {
    font-size: clamp(0.6rem, 1.4vw, 0.7rem);
    font-weight: 600;
    opacity: 0.8;
}

.wheel-toolbar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.wheel-spins,
.wheel-sound {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.9rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
}

.wheel-spins__value {
    color: #6d28d9;
    font-weight: 800;
}

.wheel-sound {
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.wheel-sound:hover {
    background: #f3f4f6;
    color: #6d28d9;
}

.wheel-sound svg {
    width: 16px;
    height: 16px;
}

.wheel-sound .wheel-sound__off,
.wheel-sound[aria-pressed="false"] .wheel-sound__on {
    display: none;
}

.wheel-sound[aria-pressed="false"] .wheel-sound__off {
    display: block;
}

.wheel-toast {
    position: absolute;
    left: 50%;
    bottom: -0.75rem;
    transform: translate(-50%, 10px);
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    background: #111827;
    color: #f9fafb;
    font-size: 0.825rem;
    font-weight: 600;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
    z-index: 130;
    max-width: 90vw;
    text-align: center;
}

.wheel-toast.is-visible {
    opacity: 1;
    transform: translate(-50%, 0);
}

/* ---------- Result modal ---------- */

.wheel-result {
    position: fixed;
    inset: 0;
    z-index: 120;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.wheel-result.is-open {
    display: flex;
}

.wheel-login {
    position: fixed;
    inset: 0;
    z-index: 125;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.wheel-login.is-open {
    display: flex;
}

.wheel-login__backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    border: 0;
    background: rgba(17, 24, 39, 0.55);
    cursor: default;
    animation: wheelFade 0.2s ease both;
}

.wheel-login__card {
    position: relative;
    z-index: 1;
    width: min(420px, 100%);
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 1.25rem;
    padding: 1.75rem 1.5rem 1.5rem;
    text-align: center;
    box-shadow: 0 30px 60px -20px rgba(17, 24, 39, 0.45);
    animation: wheelPop 0.28s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.wheel-login__dismiss {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 2rem;
    height: 2rem;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #9ca3af;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
}

.wheel-login__dismiss:hover {
    background: #f3f4f6;
    color: #111827;
}

.wheel-login__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin-bottom: 0.75rem;
    border-radius: 999px;
    background: linear-gradient(180deg, #fbbf24, #f59e0b);
    color: #111827;
    font-size: 1.25rem;
}

.wheel-login__title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: #111827;
}

.wheel-login__text {
    margin: 0.6rem 0 1.25rem;
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.5;
}

.wheel-login__actions {
    display: grid;
    gap: 0.65rem;
}

.wheel-result__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.5);
    animation: wheelFade 0.2s ease both;
}

.wheel-result__card {
    position: relative;
    width: min(420px, 100%);
    max-height: 88vh;
    overflow-y: auto;
    padding: 2rem 1.75rem 1.5rem;
    border-radius: 1rem;
    text-align: center;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 25px 50px -12px rgba(17, 24, 39, 0.35);
    animation: wheelPop 0.28s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.wheel-result__dismiss,
.wheel-rewards__dismiss {
    position: absolute;
    top: 0.5rem;
    inset-inline-end: 0.65rem;
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    color: #9ca3af;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    border-radius: 0.5rem;
}

.wheel-result__dismiss:hover,
.wheel-rewards__dismiss:hover {
    background: #f3f4f6;
    color: #374151;
}

.wheel-result__badge {
    width: 56px;
    height: 56px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    font-weight: 800;
    color: #b45309;
    background: #fef3c7;
}

.wheel-result.is-progress .wheel-result__badge {
    color: #6d28d9;
    background: #f5f3ff;
}

.wheel-result__kicker {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #9ca3af;
    margin: 0 0 0.25rem;
}

.wheel-result__kicker:empty {
    display: none;
}

.wheel-result__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 0.5rem;
}

.wheel-result__prize {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1.25;
    color: #6d28d9;
    margin: 0 0 0.75rem;
}

.wheel-result.is-progress .wheel-result__prize {
    font-size: 0.95rem;
    font-weight: 500;
    color: #4b5563;
}

.wheel-result__meta {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0 0 1rem;
}

.wheel-result__meta:empty {
    display: none;
}

.wheel-result__note-title {
    font-size: 0.925rem;
    font-weight: 700;
    color: #111827;
    margin: 1rem 0 0.35rem;
}

.wheel-result__note {
    font-size: 0.85rem;
    line-height: 1.6;
    color: #4b5563;
    margin: 0 0 1.25rem;
}

.wheel-code {
    margin: 0 0 0.5rem;
    padding: 0.85rem 1rem;
    border-radius: 0.75rem;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    text-align: start;
}

.wheel-code__label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 0.35rem;
}

.wheel-code__row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.wheel-code__value {
    flex: 1 1 auto;
    min-width: 0;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #111827;
    word-break: break-all;
}

.wheel-code__copy {
    flex: 0 0 auto;
    padding: 0.4rem 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #4b5563;
    font-size: 0.775rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.wheel-code__copy:hover {
    border-color: #a78bfa;
    color: #6d28d9;
}

.wheel-code__copy.is-copied {
    border-color: #86efac;
    background: #f0fdf4;
    color: #15803d;
}

.wheel-result__actions {
    display: flex;
    gap: 0.6rem;
    justify-content: center;
    flex-wrap: wrap;
}

.wheel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1.4rem;
    border-radius: 0.5rem;
    font-weight: 700;
    font-size: 0.925rem;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s ease, color 0.15s ease;
}

.wheel-btn[hidden] {
    display: none;
}

.wheel-btn--primary {
    color: #ffffff;
    background: #7c3aed;
}

.wheel-btn--primary:hover {
    background: #6d28d9;
}

.wheel-btn--whatsapp {
    color: #ffffff;
    background: #16a34a;
}

.wheel-btn--whatsapp:hover {
    background: #15803d;
}

.wheel-btn--facebook {
    color: #ffffff;
    background: #1877f2;
}

.wheel-btn--facebook:hover {
    background: #1462c8;
}

.wheel-btn--ghost {
    color: #374151;
    background: #ffffff;
    border: 1px solid #d1d5db;
}

.wheel-btn--ghost:hover {
    background: #f9fafb;
}

/* ---------- My rewards panel ---------- */

.wheel-rewards {
    position: fixed;
    inset: 0;
    z-index: 110;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.wheel-rewards.is-open {
    display: flex;
}

.wheel-rewards__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.5);
    animation: wheelFade 0.2s ease both;
}

.wheel-rewards__card {
    position: relative;
    width: min(520px, 100%);
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    border-radius: 1rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 25px 50px -12px rgba(17, 24, 39, 0.35);
    animation: wheelPop 0.28s cubic-bezier(0.22, 1, 0.36, 1) both;
    overflow: hidden;
}

.wheel-rewards__head {
    position: relative;
    padding: 1.1rem 3rem 1.1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.wheel-rewards__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #111827;
}

.wheel-rewards__dismiss {
    top: 0.7rem;
}

.wheel-rewards__body {
    padding: 0.75rem;
    overflow-y: auto;
}

.wheel-rewards__empty {
    margin: 0;
    padding: 1.75rem 1rem;
    text-align: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.wheel-rewards__list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.wheel-reward-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.8rem 0.9rem;
    border-radius: 0.65rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    cursor: pointer;
    text-align: start;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.wheel-reward-item:hover {
    border-color: #a78bfa;
    background: #faf5ff;
}

.wheel-reward-item__icon {
    flex: 0 0 auto;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.55rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 800;
    color: #6d28d9;
    background: #f5f3ff;
    border: 1px solid #ede9fe;
}

.wheel-reward-item__body {
    flex: 1 1 auto;
    min-width: 0;
}

.wheel-reward-item__label {
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    color: #111827;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.wheel-reward-item__code {
    display: block;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.775rem;
    color: #6b7280;
    word-break: break-all;
}

.wheel-reward-item__date {
    display: block;
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.15rem;
}

.wheel-reward-item__status {
    flex: 0 0 auto;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.675rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #92400e;
    background: #fef3c7;
}

.wheel-reward-item__status.is-used,
.wheel-reward-item__status.is-fulfilled {
    color: #15803d;
    background: #dcfce7;
}

.wheel-reward-item__status.is-expired,
.wheel-reward-item__status.is-cancelled {
    color: #6b7280;
    background: #f3f4f6;
}

@keyframes wheelPop {
    from { opacity: 0; transform: scale(0.96) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes wheelFade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (max-width: 480px) {
    .wheel-result__prize { font-size: 1.4rem; }
    .wheel-result__card { padding: 1.75rem 1.25rem 1.25rem; }
    .wheel-btn { padding: 0.65rem 1.1rem; font-size: 0.875rem; }
}

@media (prefers-reduced-motion: reduce) {
    .wheel-result__card,
    .wheel-rewards__card,
    .wheel-result__backdrop,
    .wheel-rewards__backdrop { animation: none; }
    .wheel-hub { transition: none; }
}
</style>
@endpush

@push('scripts')
@verbatim
<script>
(function () {
    const stage = document.getElementById('wheelStage');
    if (!stage) return;

    const cfg = JSON.parse(document.getElementById('wheelConfig').textContent);
    const segments = cfg.segments || [];
    const T = cfg.text || {};
    if (!segments.length) return;

    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    const confettiCanvas = document.getElementById('wheelConfetti');
    const confettiCtx = confettiCanvas.getContext('2d');
    const wrap = document.getElementById('wheelCanvasWrap');
    const button = document.getElementById('wheelSpinButton');
    const buttonLabel = document.getElementById('wheelSpinLabel');
    const buttonHint = document.getElementById('wheelSpinHint');
    const pointer = document.getElementById('wheelPointer');
    const spinsLeftEl = document.getElementById('wheelSpinsLeft');
    const soundToggle = document.getElementById('wheelSoundToggle');
    const soundLabel = document.getElementById('wheelSoundLabel');
    const toast = document.getElementById('wheelToast');

    const modal = document.getElementById('wheelResult');
    const modalBadge = document.getElementById('wheelResultBadge');
    const modalKicker = document.getElementById('wheelResultKicker');
    const modalTitle = document.getElementById('wheelResultTitle');
    const modalPrize = document.getElementById('wheelResultPrize');
    const modalMeta = document.getElementById('wheelResultMeta');
    const codeBlock = document.getElementById('wheelResultCodeBlock');
    const codeLabel = document.getElementById('wheelResultCodeLabel');
    const codeValue = document.getElementById('wheelResultCode');
    const codeCopy = document.getElementById('wheelResultCopy');
    const noteTitle = document.getElementById('wheelResultNoteTitle');
    const note = document.getElementById('wheelResultNote');
    const waLink = document.getElementById('wheelResultWhatsapp');
    const fbLink = document.getElementById('wheelResultFacebook');
    const useLink = document.getElementById('wheelResultUse');
    const loginModal = document.getElementById('wheelLoginModal');

    const rewardsPanel = document.getElementById('myRewardsPanel');
    const rewardsList = document.getElementById('myRewardsList');
    const rewardsEmpty = document.getElementById('myRewardsEmpty');
    const iconPreview = document.getElementById('wheelIconPreview');
    const iconPreviewImage = document.getElementById('wheelIconPreviewImage');
    const iconPreviewFallback = document.getElementById('wheelIconPreviewFallback');
    const iconPreviewTitle = document.getElementById('wheelIconPreviewTitle');
    const iconPreviewSub = document.getElementById('wheelIconPreviewSub');
    const iconPreviewPrev = document.getElementById('wheelIconPreviewPrev');
    const iconPreviewNext = document.getElementById('wheelIconPreviewNext');
    const iconPreviewDots = document.getElementById('wheelIconPreviewDots');

    const TAU = Math.PI * 2;
    const COUNT = segments.length;
    const ARC = TAU / COUNT;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const PALETTE = {
        purple: { from: '#8b5cf6', to: '#6d28d9', text: '#ffffff', sub: '#ede9fe' },
        pink:   { from: '#f472b6', to: '#db2777', text: '#ffffff', sub: '#fce7f3' },
        slate:  { from: '#e2e8f0', to: '#cbd5e1', text: '#334155', sub: '#64748b' },
        teal:   { from: '#2dd4bf', to: '#0d9488', text: '#ffffff', sub: '#ccfbf1' },
        gold:   { from: '#fbbf24', to: '#f59e0b', text: '#422006', sub: '#78350f' }
    };

    let rotation = 0;
    let spinning = false;
    let spinsLeft = parseInt(stage.dataset.spinsLeft || '0', 10);
    const unlimitedSpins = cfg.unlimitedSpins === true;
    let soundEnabled = true;
    let audioCtx = null;
    let size = 0;
    let claims = Array.isArray(cfg.initialClaims) ? cfg.initialClaims.slice() : [];
    let rewardsLoaded = false;
    let iconPressTimer = null;
    let iconPressStart = null;
    let previewGallery = [];
    let previewIndex = 0;
    let previewSwipeStart = null;

    const iconImages = segments.map(function (segment) {
        if (!segment.icon) return null;

        const image = new Image();
        image.decoding = 'async';
        image.onload = drawWheel;
        image.onerror = function () {
            segment.icon = null;
            drawWheel();
        };
        image.src = segment.icon;

        return image;
    });

    /* ---------- Wheel rendering ---------- */

    function resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2.5);
        size = wrap.clientWidth || 400;
        canvas.width = Math.round(size * dpr);
        canvas.height = Math.round(size * dpr);
        canvas.style.width = size + 'px';
        canvas.style.height = size + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        drawWheel();

        confettiCanvas.width = Math.round(confettiCanvas.clientWidth * dpr);
        confettiCanvas.height = Math.round(confettiCanvas.clientHeight * dpr);
        confettiCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function drawWheel() {
        const c = size / 2;
        const r = c;
        ctx.clearRect(0, 0, size, size);

        segments.forEach(function (seg, i) {
            const colors = PALETTE[seg.style] || PALETTE.purple;
            const center = -Math.PI / 2 + i * ARC;
            const start = center - ARC / 2;
            const end = center + ARC / 2;

            const grad = ctx.createLinearGradient(
                c + Math.cos(center) * r * 0.15, c + Math.sin(center) * r * 0.15,
                c + Math.cos(center) * r, c + Math.sin(center) * r
            );
            grad.addColorStop(0, colors.from);
            grad.addColorStop(1, colors.to);

            ctx.beginPath();
            ctx.moveTo(c, c);
            ctx.arc(c, c, r, start, end);
            ctx.closePath();
            ctx.fillStyle = grad;
            ctx.fill();

            // Slice divider.
            ctx.beginPath();
            ctx.moveTo(c, c);
            ctx.lineTo(c + Math.cos(start) * r, c + Math.sin(start) * r);
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = Math.max(1.5, size * 0.006);
            ctx.stroke();

            // Icon and labels, drawn along the radius so they read outward.
            ctx.save();
            ctx.translate(c, c);
            ctx.rotate(center);
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            if (seg.type === 'none') {
                ctx.fillStyle = colors.text;
                ctx.font = '700 ' + (size * 0.034) + 'px Cairo, system-ui, sans-serif';
                ctx.fillText(seg.label, r * 0.66, 0);
                ctx.restore();
                return;
            }

            const hasSub = seg.sub && seg.sub.length;
            const iconSize = size * 0.14;
            const iconX = r * 0.68;
            const iconY = -size * 0.045;
            const image = iconImages[i];

            drawSegmentIcon(seg, image, iconX, iconY, iconSize, colors);

            ctx.fillStyle = colors.text;
            ctx.font = '800 ' + (size * 0.038) + 'px Cairo, system-ui, sans-serif';
            ctx.fillText(seg.label, iconX, iconY + iconSize * 0.68);

            if (hasSub) {
                ctx.fillStyle = colors.sub;
                ctx.font = '600 ' + (size * 0.026) + 'px Cairo, system-ui, sans-serif';
                ctx.fillText(seg.sub, iconX, iconY + iconSize * 0.98);
            }
            ctx.restore();
        });

        // Thin outline so the slices sit cleanly inside the white frame.
        ctx.beginPath();
        ctx.arc(c, c, r - 1, 0, TAU);
        ctx.strokeStyle = 'rgba(17, 24, 39, 0.08)';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    function drawSegmentIcon(segment, image, x, y, iconSize, colors) {
        const half = iconSize / 2;
        const radius = iconSize * 0.22;

        ctx.save();
        ctx.beginPath();
        ctx.roundRect(x - half, y - half, iconSize, iconSize, radius);
        ctx.clip();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.94)';
        ctx.fillRect(x - half, y - half, iconSize, iconSize);

        if (image && image.complete && image.naturalWidth) {
            const fit = segment.icon_fit === 'cover' ? 'cover' : 'contain';
            const padding = fit === 'contain' ? iconSize * 0.09 : 0;
            const box = iconSize - padding * 2;
            const sourceRatio = image.naturalWidth / image.naturalHeight;
            let width;
            let height;

            if ((fit === 'cover' && sourceRatio > 1) || (fit === 'contain' && sourceRatio < 1)) {
                height = box;
                width = box * sourceRatio;
            } else {
                width = box;
                height = box / sourceRatio;
            }

            ctx.drawImage(image, x - width / 2, y - height / 2, width, height);
        } else {
            ctx.fillStyle = colors.text;
            ctx.font = '900 ' + (iconSize * 0.52) + 'px Cairo, system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(segment.type === 'coupon' ? '%' : '◆', x, y + 1);
        }

        ctx.restore();
    }

    function applyRotation() {
        canvas.style.transform = 'rotate(' + rotation + 'rad)';
    }

    /* ---------- Sound ---------- */

    function ensureAudio() {
        if (audioCtx || !soundEnabled) return;
        const Ctor = window.AudioContext || window.webkitAudioContext;
        if (Ctor) audioCtx = new Ctor();
    }

    function playTick(intensity) {
        if (!soundEnabled || !audioCtx) return;
        const now = audioCtx.currentTime;
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'square';
        osc.frequency.setValueAtTime(1000 + intensity * 450, now);
        osc.frequency.exponentialRampToValueAtTime(520, now + 0.05);
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.04 + intensity * 0.05, now + 0.005);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.07);
        osc.connect(gain).connect(audioCtx.destination);
        osc.start(now);
        osc.stop(now + 0.08);
    }

    function playChime(isUnlock) {
        if (!soundEnabled || !audioCtx) return;
        const notes = isUnlock ? [523.25, 659.25, 783.99] : [523.25, 622.25];
        notes.forEach(function (freq, i) {
            const start = audioCtx.currentTime + i * 0.11;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, start);
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.exponentialRampToValueAtTime(isUnlock ? 0.12 : 0.07, start + 0.03);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.38);
            osc.connect(gain).connect(audioCtx.destination);
            osc.start(start);
            osc.stop(start + 0.4);
        });
    }

    /* ---------- Confetti ---------- */

    const CONFETTI_COLORS = ['#7c3aed', '#a78bfa', '#db2777', '#f472b6', '#f59e0b', '#fbbf24', '#14b8a6', '#38bdf8'];
    const CONFETTI_MAX_PIECES = 380;

    let confettiPieces = [];
    let confettiRaf = null;
    let confettiRainUntil = 0;
    let confettiRainRate = 0;
    let confettiSpawnCarry = 0;
    let confettiLastFrame = 0;

    function confettiPiece(options) {
        return {
            x: options.x,
            y: options.y,
            vx: options.vx,
            vy: options.vy,
            size: options.size,
            rot: Math.random() * TAU,
            vr: (Math.random() - 0.5) * 0.34,
            // Horizontal drift so pieces flutter instead of dropping straight down.
            swayPhase: Math.random() * TAU,
            swaySpeed: 1.6 + Math.random() * 2.2,
            swayAmount: options.swayAmount,
            // Simulates the piece turning edge-on as it tumbles.
            flipPhase: Math.random() * TAU,
            flipSpeed: 3 + Math.random() * 4,
            drag: options.drag,
            maxFall: options.maxFall,
            round: Math.random() < 0.25,
            color: CONFETTI_COLORS[(Math.random() * CONFETTI_COLORS.length) | 0],
            life: 1,
            decay: options.decay
        };
    }

    function burstConfetti(power) {
        if (reducedMotion) return;

        const w = confettiCanvas.clientWidth;
        const h = confettiCanvas.clientHeight;
        const now = performance.now();

        // Two cannons firing inwards from the lower corners.
        [{ x: w * 0.06, dir: 1 }, { x: w * 0.94, dir: -1 }].forEach(function (cannon) {
            const shots = Math.round(45 * power);

            for (let i = 0; i < shots; i++) {
                const angle = -Math.PI / 2 + cannon.dir * (0.25 + Math.random() * 0.55);
                const speed = (11 + Math.random() * 12) * power;

                confettiPieces.push(confettiPiece({
                    x: cannon.x,
                    y: h * 0.92,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    size: 7 + Math.random() * 7,
                    swayAmount: 0.4 + Math.random() * 0.8,
                    drag: 0.985,
                    maxFall: 15,
                    decay: 0.0045
                }));
            }
        });

        confettiRainRate = 90 * power;
        confettiRainUntil = now + 2600 + 900 * power;

        confettiCanvas.classList.add('is-active');

        if (!confettiRaf) {
            confettiLastFrame = now;
            confettiRaf = requestAnimationFrame(stepConfetti);
        }
    }

    function spawnConfettiRain(count, width) {
        for (let i = 0; i < count; i++) {
            if (confettiPieces.length >= CONFETTI_MAX_PIECES) return;

            confettiPieces.push(confettiPiece({
                x: Math.random() * width,
                y: -20 - Math.random() * 60,
                vx: (Math.random() - 0.5) * 1.6,
                vy: 1.5 + Math.random() * 2.5,
                size: 6 + Math.random() * 7,
                swayAmount: 0.8 + Math.random() * 1.6,
                drag: 0.995,
                maxFall: 5.5 + Math.random() * 3,
                decay: 0.0022
            }));
        }
    }

    function stepConfetti(timestamp) {
        const w = confettiCanvas.clientWidth;
        const h = confettiCanvas.clientHeight;
        const now = timestamp || performance.now();
        // Clamped so a backgrounded tab does not teleport every piece off screen.
        const delta = Math.min((now - confettiLastFrame) / 16.667, 3);
        confettiLastFrame = now;

        if (now < confettiRainUntil) {
            confettiSpawnCarry += (confettiRainRate * delta) / 60;
            const spawn = Math.floor(confettiSpawnCarry);

            if (spawn > 0) {
                confettiSpawnCarry -= spawn;
                spawnConfettiRain(spawn, w);
            }
        }

        confettiCtx.clearRect(0, 0, w, h);

        confettiPieces = confettiPieces.filter(function (p) {
            p.vy = Math.min(p.vy + 0.22 * delta, p.maxFall);
            p.vx *= Math.pow(p.drag, delta);
            p.swayPhase += p.swaySpeed * 0.016 * delta;
            p.flipPhase += p.flipSpeed * 0.016 * delta;

            p.x += (p.vx + Math.sin(p.swayPhase) * p.swayAmount) * delta;
            p.y += p.vy * delta;
            p.rot += p.vr * delta;

            if (p.y > h * 0.55) {
                p.life -= p.decay * delta * 60;
            }

            return p.life > 0 && p.y < h + 60;
        });

        confettiPieces.forEach(function (p) {
            const flip = Math.abs(Math.cos(p.flipPhase));

            confettiCtx.save();
            confettiCtx.globalAlpha = Math.max(0, Math.min(1, p.life));
            confettiCtx.translate(p.x, p.y);
            confettiCtx.rotate(p.rot);
            confettiCtx.fillStyle = p.color;

            if (p.round) {
                confettiCtx.beginPath();
                confettiCtx.ellipse(0, 0, p.size / 2, (p.size / 2) * flip, 0, 0, TAU);
                confettiCtx.fill();
            } else {
                confettiCtx.fillRect(-p.size / 2, (-p.size / 2) * flip, p.size, p.size * flip);
            }

            confettiCtx.restore();
        });

        if (confettiPieces.length || now < confettiRainUntil) {
            confettiRaf = requestAnimationFrame(stepConfetti);
        } else {
            confettiCtx.clearRect(0, 0, w, h);
            confettiCanvas.classList.remove('is-active');
            confettiRaf = null;
        }
    }

    function stopConfetti() {
        confettiRainUntil = 0;
        confettiSpawnCarry = 0;
    }

    /* ---------- Rotation maths ---------- */

    function normalize(angle) {
        return ((angle % TAU) + TAU) % TAU;
    }

    function targetRotationFor(index) {
        // Segment 0 is centred at the top when rotation is 0, so bringing
        // segment `index` under the pointer means rotating back by index * ARC.
        const jitter = (Math.random() - 0.5) * ARC * 0.6;
        const desired = normalize(-index * ARC + jitter);
        const turns = reducedMotion ? 1 : 5 + Math.floor(Math.random() * 3);
        const delta = normalize(desired - normalize(rotation));

        return rotation + turns * TAU + delta;
    }

    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    function easeOutElastic(t) {
        if (t === 0 || t === 1) return t;
        return Math.pow(2, -11 * t) * Math.sin((t * 10 - 0.75) * (TAU / 3)) + 1;
    }

    function animateTo(target, duration) {
        return new Promise(function (resolve) {
            const from = rotation;
            const distance = target - from;
            const start = performance.now();
            let lastTickSlot = Math.floor((from + ARC / 2) / ARC);

            const frame = function (now) {
                const t = Math.min(1, (now - start) / duration);
                const prev = rotation;
                rotation = from + distance * easeOutQuart(t);
                applyRotation();

                const slot = Math.floor((rotation + ARC / 2) / ARC);
                if (slot !== lastTickSlot) {
                    const velocity = Math.abs(rotation - prev);
                    lastTickSlot = slot;
                    playTick(Math.min(1, velocity * 12));
                    if (!reducedMotion) {
                        pointer.style.transition = 'transform 90ms ease-out';
                        pointer.style.transform = 'translateX(-50%) rotate(' + (-7 - Math.min(7, velocity * 80)) + 'deg)';
                        setTimeout(function () {
                            pointer.style.transform = 'translateX(-50%) rotate(0deg)';
                        }, 90);
                    }
                }

                if (t < 1) {
                    requestAnimationFrame(frame);
                } else {
                    resolve();
                }
            };

            requestAnimationFrame(frame);
        });
    }

    function settle(target) {
        if (reducedMotion) return Promise.resolve();

        return new Promise(function (resolve) {
            const from = rotation;
            const overshoot = ARC * 0.05;
            const start = performance.now();
            const duration = 600;

            const frame = function (now) {
                const t = Math.min(1, (now - start) / duration);
                rotation = from - overshoot + overshoot * easeOutElastic(t);
                applyRotation();
                if (t < 1) {
                    requestAnimationFrame(frame);
                } else {
                    rotation = target;
                    applyRotation();
                    resolve();
                }
            };

            requestAnimationFrame(frame);
        });
    }

    /* ---------- Counters and toast ---------- */

    function setSpinsLeft(value) {
        spinsLeft = Math.max(0, parseInt(value, 10) || 0);
        stage.dataset.spinsLeft = String(spinsLeft);
        const displayValue = unlimitedSpins ? '∞' : spinsLeft;
        if (spinsLeftEl) spinsLeftEl.textContent = displayValue;
        if (buttonHint) buttonHint.textContent = displayValue;

        const pageSpins = document.getElementById('progressSpins');
        if (pageSpins) pageSpins.textContent = displayValue;

        button.disabled = !unlimitedSpins && spinsLeft <= 0;
    }

    function showToast(message) {
        if (!message) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 4200);
    }

    /* ---------- Result modal ---------- */

    function hideAll() {
        modal.classList.remove('is-progress');
        modalKicker.textContent = '';
        modalMeta.textContent = '';
        codeBlock.hidden = true;
        noteTitle.hidden = true;
        note.hidden = true;
        waLink.hidden = true;
        fbLink.hidden = true;
        useLink.hidden = true;
        codeCopy.classList.remove('is-copied');
        codeCopy.textContent = T.copy_code || 'Copy';
    }

    function formatPercent(value) {
        const num = parseFloat(value);
        if (!isFinite(num)) return '';
        return (Math.round(num * 100) / 100).toString().replace(/\.0+$/, '') + '%';
    }

    function whatsappHref(code) {
        const template = T.whatsapp_prefill || '';
        const message = template.replace(':code', code || '');
        return 'https://wa.me/' + cfg.contact.whatsapp + '?text=' + encodeURIComponent(message);
    }

    function openNoWinResult() {
        hideAll();
        modal.classList.add('is-progress');
        modalBadge.textContent = '↻';
        modalTitle.textContent = T.no_win_title || '';
        modalPrize.textContent = T.no_win_text || '';

        modal.classList.add('is-open');
        playChime(false);
    }

    function openClaimResult(claim) {
        if (!claim) return;
        hideAll();

        const code = claim.claim_code || claim.coupon_code || '';
        const percent = claim.discount_percentage ? formatPercent(claim.discount_percentage) : '';

        modalKicker.textContent = T.you_won || '';

        if (claim.is_discount_reward) {
            modalBadge.textContent = '%';
            modalTitle.textContent = T.discount_ready || T.congrats || '';
            modalPrize.textContent = percent
                ? (percent + (claim.label ? ' · ' + claim.label : ''))
                : (claim.label || '');

            codeLabel.textContent = T.coupon_code || T.claim_code || '';
            codeValue.textContent = code;
            codeBlock.hidden = !code;

            note.textContent = T.discount_ready_text || '';
            note.hidden = false;

            useLink.textContent = T.claim || '';
            useLink.setAttribute('href', cfg.gameUrl);
            useLink.hidden = false;
        } else {
            modalBadge.textContent = '★';
            modalTitle.textContent = T.congrats || '';
            modalPrize.textContent = claim.label || claim.pack_name || '';

            codeLabel.textContent = T.claim_code || '';
            codeValue.textContent = code;
            codeBlock.hidden = !code;

            noteTitle.textContent = T.contact_title || '';
            noteTitle.hidden = false;
            note.textContent = T.contact_text || '';
            note.hidden = false;

            waLink.textContent = T.contact_whatsapp || 'WhatsApp';
            waLink.setAttribute('href', whatsappHref(code));
            waLink.hidden = false;

            fbLink.textContent = T.contact_facebook || 'Facebook';
            fbLink.setAttribute('href', cfg.contact.facebook);
            fbLink.hidden = false;
        }

        if (claim.unlocked_at) {
            modalMeta.textContent = (T.unlocked_at || '') + ': ' + claim.unlocked_at;
        }

        modal.classList.add('is-open');
        playChime(true);
        burstConfetti(claim.is_discount_reward ? 1 : 1.5);
    }

    function closeResult() {
        modal.classList.remove('is-open');
        stopConfetti();
    }

    async function copyCode() {
        const value = codeValue.textContent || '';
        if (!value) return;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(value);
            } else {
                const helper = document.createElement('textarea');
                helper.value = value;
                helper.setAttribute('readonly', '');
                helper.style.position = 'fixed';
                helper.style.opacity = '0';
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                document.body.removeChild(helper);
            }

            codeCopy.textContent = T.code_copied || 'Copied';
            codeCopy.classList.add('is-copied');
            setTimeout(function () {
                codeCopy.textContent = T.copy_code || 'Copy';
                codeCopy.classList.remove('is-copied');
            }, 2000);
        } catch (e) {
            showToast(T.error || '');
        }
    }

    /* ---------- My rewards ---------- */

    function renderRewards() {
        rewardsList.innerHTML = '';

        if (!claims.length) {
            rewardsEmpty.hidden = false;
            return;
        }

        rewardsEmpty.hidden = true;

        claims.forEach(function (claim, index) {
            const li = document.createElement('li');
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'wheel-reward-item';
            item.dataset.claimIndex = String(index);

            const icon = document.createElement('span');
            icon.className = 'wheel-reward-item__icon';
            icon.textContent = claim.is_discount_reward ? '%' : '★';

            const body = document.createElement('span');
            body.className = 'wheel-reward-item__body';

            const label = document.createElement('span');
            label.className = 'wheel-reward-item__label';
            const percent = claim.is_discount_reward && claim.discount_percentage
                ? ' · ' + formatPercent(claim.discount_percentage)
                : '';
            label.textContent = (claim.label || claim.pack_name || '') + percent;

            const code = document.createElement('span');
            code.className = 'wheel-reward-item__code';
            code.textContent = claim.claim_code || claim.coupon_code || '';

            body.appendChild(label);
            body.appendChild(code);

            if (claim.unlocked_at) {
                const date = document.createElement('span');
                date.className = 'wheel-reward-item__date';
                date.textContent = (T.unlocked_at || '') + ': ' + claim.unlocked_at;
                body.appendChild(date);
            }

            const status = document.createElement('span');
            status.className = 'wheel-reward-item__status is-' + (claim.status || 'unlocked');
            status.textContent = claim.status || '';

            item.appendChild(icon);
            item.appendChild(body);
            item.appendChild(status);
            li.appendChild(item);
            rewardsList.appendChild(li);
        });
    }

    async function loadRewards(force) {
        if (rewardsLoaded && !force) return;
        if (!cfg.rewardsUrl || cfg.rewardsUrl === '#') return;

        try {
            const response = await fetch(cfg.rewardsUrl, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': cfg.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (response.status === 401) {
                showToast(T.login_required || T.error || '');
                return;
            }

            const data = await response.json().catch(function () { return null; });

            if (response.ok && data && data.success && Array.isArray(data.claims)) {
                claims = data.claims;
                rewardsLoaded = true;
                renderRewards();
            }
        } catch (e) {
            showToast(T.error || '');
        }
    }

    function openLoginModal() {
        if (!loginModal) return;
        loginModal.classList.add('is-open');
    }

    function closeLoginModal() {
        if (!loginModal) return;
        loginModal.classList.remove('is-open');
    }

    function openRewards() {
        if (cfg.requiresLogin) {
            openLoginModal();
            return;
        }

        rewardsPanel.classList.add('is-open');
        renderRewards();
        loadRewards(true);
    }

    function closeRewards() {
        rewardsPanel.classList.remove('is-open');
    }

    /* ---------- Spin ---------- */

    function cosmeticIndex(data) {
        // Purely visual: the backend decides the outcome, the wheel just needs a
        // slice to land on. Winning spins stop on the reward, the rest on a blank.
        if (data && data.reward_unlocked) {
            const rewardId = (data.spun_toward && data.spun_toward.reward_id) || null;
            const matched = segments.findIndex(function (seg) {
                return rewardId && seg.reward_id === rewardId;
            });
            if (matched >= 0) return matched;
        }

        const blanks = [];
        segments.forEach(function (seg, i) {
            if (seg.type === 'none') blanks.push(i);
        });

        if (blanks.length) {
            return blanks[(Math.random() * blanks.length) | 0];
        }

        return (Math.random() * COUNT) | 0;
    }

    async function requestSpin() {
        const response = await fetch(cfg.spinUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const data = await response.json().catch(function () { return null; });

        return { status: response.status, ok: response.ok, data: data };
    }

    async function spin() {
        if (spinning) return;

        if (cfg.requiresLogin) {
            openLoginModal();
            return;
        }

        ensureAudio();
        if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();

        if (!unlimitedSpins && spinsLeft <= 0) {
            showToast(T.no_spins_text || '');
            return;
        }

        spinning = true;
        button.disabled = true;
        buttonLabel.textContent = T.spinning || '...';

        let result;
        try {
            result = await requestSpin();
        } catch (e) {
            spinning = false;
            buttonLabel.textContent = T.spin || '';
            button.disabled = !unlimitedSpins && spinsLeft <= 0;
            showToast(T.error || '');
            return;
        }

        const data = result.data;

        if (result.status === 401) {
            spinning = false;
            buttonLabel.textContent = T.spin || '';
            button.disabled = !unlimitedSpins && spinsLeft <= 0;
            openLoginModal();
            return;
        }

        if (result.status === 429 || (data && data.code === 'no_spins')) {
            spinning = false;
            buttonLabel.textContent = T.spin || '';
            setSpinsLeft(0);
            showToast((data && data.message) || T.no_spins_text || '');
            return;
        }

        if (!result.ok || !data || !data.success) {
            spinning = false;
            buttonLabel.textContent = T.spin || '';
            button.disabled = !unlimitedSpins && spinsLeft <= 0;
            showToast((data && data.message) || T.error || '');
            return;
        }

        const index = cosmeticIndex(data);
        const target = targetRotationFor(index);
        const duration = reducedMotion ? 900 : 4800 + Math.random() * 800;

        await animateTo(target, duration);
        await settle(target);

        rotation = normalize(rotation);
        applyRotation();

        if (typeof data.available_spins === 'number') {
            setSpinsLeft(data.available_spins);
        } else if (!unlimitedSpins) {
            setSpinsLeft(spinsLeft - 1);
        }

        buttonLabel.textContent = T.spin || '';
        spinning = false;
        button.disabled = !unlimitedSpins && spinsLeft <= 0;

        if (data.reward_unlocked && data.claim) {
            claims.unshift(data.claim);
            renderRewards();
            setTimeout(function () { openClaimResult(data.claim); }, 300);
        } else {
            setTimeout(openNoWinResult, 300);
        }
    }

    /* ---------- Reward icon preview ---------- */

    function segmentAtPoint(clientX, clientY) {
        const rect = wrap.getBoundingClientRect();
        const x = clientX - rect.left - rect.width / 2;
        const y = clientY - rect.top - rect.height / 2;
        const distance = Math.sqrt(x * x + y * y);

        // Ignore the center spin button and anything outside the wheel.
        if (distance < rect.width * 0.19 || distance > rect.width * 0.5) return null;

        const screenAngle = Math.atan2(y, x);
        const segmentNumber = Math.round((screenAngle - rotation + Math.PI / 2) / ARC);
        const index = ((segmentNumber % COUNT) + COUNT) % COUNT;

        const segment = segments[index] || null;

        return segment && segment.type === 'none' ? null : segment;
    }

    function openIconPreview(segment) {
        if (!segment || spinning) return;

        iconPreviewTitle.textContent = segment.label || '';
        iconPreviewSub.textContent = segment.sub || '';

        previewGallery = Array.isArray(segment.gallery) && segment.gallery.length
            ? segment.gallery.slice()
            : (segment.icon ? [segment.icon] : []);
        previewIndex = 0;

        if (previewGallery.length) {
            iconPreviewFallback.style.display = 'none';
            iconPreviewImage.alt = segment.label || '';
            iconPreviewImage.style.display = 'block';
        } else {
            iconPreviewImage.removeAttribute('src');
            iconPreviewImage.style.display = 'none';
            iconPreviewFallback.textContent = segment.type === 'coupon' ? '%' : '◆';
            iconPreviewFallback.style.display = 'block';
        }

        renderPreviewSlide();

        iconPreview.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function renderPreviewSlide() {
        const many = previewGallery.length > 1;

        if (previewGallery.length) {
            iconPreviewImage.src = previewGallery[previewIndex];
        }

        iconPreviewPrev.hidden = !many;
        iconPreviewNext.hidden = !many;
        iconPreviewDots.hidden = !many;
        iconPreviewDots.innerHTML = '';

        if (!many) return;

        previewGallery.forEach(function (src, i) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'wheel-icon-preview__dot' + (i === previewIndex ? ' is-active' : '');
            dot.dataset.previewIndex = String(i);
            iconPreviewDots.appendChild(dot);
        });
    }

    function movePreview(step) {
        if (previewGallery.length < 2) return;

        previewIndex = (previewIndex + step + previewGallery.length) % previewGallery.length;
        renderPreviewSlide();
    }

    function closeIconPreview() {
        iconPreview.classList.remove('is-open');
        document.body.style.overflow = '';
        previewSwipeStart = null;
    }

    function cancelIconPress() {
        if (iconPressTimer) window.clearTimeout(iconPressTimer);
        iconPressTimer = null;
        iconPressStart = null;
    }

    /* ---------- Wiring ---------- */

    button.addEventListener('click', spin);
    codeCopy.addEventListener('click', copyCode);

    wrap.addEventListener('pointerdown', function (event) {
        if (spinning) return;

        const segment = segmentAtPoint(event.clientX, event.clientY);
        if (!segment) return;

        iconPressStart = {
            x: event.clientX,
            y: event.clientY,
            segment: segment,
            opened: false
        };

        iconPressTimer = window.setTimeout(function () {
            if (!iconPressStart) return;
            iconPressStart.opened = true;
            openIconPreview(iconPressStart.segment);
        }, 450);
    });

    wrap.addEventListener('pointermove', function (event) {
        if (!iconPressStart) return;

        if (Math.hypot(event.clientX - iconPressStart.x, event.clientY - iconPressStart.y) > 10) {
            cancelIconPress();
        }
    });

    wrap.addEventListener('pointerup', function (event) {
        if (!iconPressStart) return;

        const press = iconPressStart;
        if (iconPressTimer) window.clearTimeout(iconPressTimer);
        iconPressTimer = null;
        iconPressStart = null;

        if (!press.opened && Math.hypot(event.clientX - press.x, event.clientY - press.y) <= 10) {
            openIconPreview(press.segment);
        }
    });

    wrap.addEventListener('pointercancel', cancelIconPress);
    wrap.addEventListener('pointerleave', function (event) {
        if (event.pointerType === 'mouse') cancelIconPress();
    });

    iconPreview.addEventListener('click', function (event) {
        if (event.target.closest('[data-icon-preview-close]')) {
            closeIconPreview();
            return;
        }

        if (event.target.closest('#wheelIconPreviewPrev')) {
            movePreview(-1);
            return;
        }

        if (event.target.closest('#wheelIconPreviewNext')) {
            movePreview(1);
            return;
        }

        const dot = event.target.closest('.wheel-icon-preview__dot');
        if (dot) {
            previewIndex = parseInt(dot.dataset.previewIndex, 10) || 0;
            renderPreviewSlide();
        }
    });

    iconPreviewImage.addEventListener('pointerdown', function (event) {
        if (previewGallery.length < 2) return;
        previewSwipeStart = event.clientX;
    });

    iconPreviewImage.addEventListener('pointerup', function (event) {
        if (previewSwipeStart === null) return;

        const distance = event.clientX - previewSwipeStart;
        previewSwipeStart = null;

        if (Math.abs(distance) > 40) {
            movePreview(distance < 0 ? 1 : -1);
        }
    });

    iconPreviewImage.addEventListener('pointercancel', function () {
        previewSwipeStart = null;
    });

    soundToggle.addEventListener('click', function () {
        soundEnabled = !soundEnabled;
        soundToggle.setAttribute('aria-pressed', soundEnabled ? 'true' : 'false');
        soundLabel.textContent = soundToggle.dataset[soundEnabled ? 'labelOn' : 'labelOff'] || soundLabel.textContent;
        if (soundEnabled) ensureAudio();
    });

    modal.addEventListener('click', function (event) {
        if (event.target.closest('[data-wheel-close]')) {
            event.preventDefault();
            closeResult();
        }
    });

    if (loginModal) {
        loginModal.addEventListener('click', function (event) {
            if (event.target.closest('[data-login-close]')) {
                event.preventDefault();
                closeLoginModal();
            }
        });
    }

    rewardsPanel.addEventListener('click', function (event) {
        if (event.target.closest('[data-rewards-close]')) {
            event.preventDefault();
            closeRewards();
            return;
        }

        const item = event.target.closest('.wheel-reward-item');
        if (item) {
            const claim = claims[parseInt(item.dataset.claimIndex, 10)];
            if (claim) openClaimResult(claim);
        }
    });

    // The trigger usually lives outside this component, so listen on document.
    document.addEventListener('click', function (event) {
        if (event.target.closest('#openMyRewards')) {
            event.preventDefault();
            openRewards();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (iconPreview.classList.contains('is-open') && (event.key === 'ArrowLeft' || event.key === 'ArrowRight')) {
            event.preventDefault();
            movePreview(event.key === 'ArrowRight' ? 1 : -1);
            return;
        }

        if (event.key !== 'Escape') return;
        if (loginModal && loginModal.classList.contains('is-open')) {
            closeLoginModal();
        } else if (iconPreview.classList.contains('is-open')) {
            closeIconPreview();
        } else if (modal.classList.contains('is-open')) {
            closeResult();
        } else if (rewardsPanel.classList.contains('is-open')) {
            closeRewards();
        }
    });

    if (window.ResizeObserver) {
        new ResizeObserver(function () { resize(); applyRotation(); }).observe(wrap);
    }

    // The confetti canvas covers the viewport, so it needs window resizes too.
    window.addEventListener('resize', function () { resize(); applyRotation(); });

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(drawWheel);
    }

    resize();
    applyRotation();
    setSpinsLeft(spinsLeft);
    renderRewards();
})();
</script>
@endverbatim
@endpush
