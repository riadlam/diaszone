@php
    /** @var \Illuminate\Support\Collection<\App\Models\FlashSaleOffer> $flashSales */
    $flashSales = $flashSales ?? collect();
    $flashSaleEndsAt = $flashSaleEndsAt ?? ($flashSales->max('ends_at'));
@endphp

@if($flashSales->isNotEmpty())
<section class="flash-sale" data-flash-sale aria-label="{{ __('flash_sale.title') }}">
    <div class="container relative z-10 mx-auto w-full max-w-screen-2xl px-4 md:px-8 2xl:px-3 overflow-visible">
        <div class="flash-sale__ribbon relative rounded-xl flex items-center w-full h-10 lg:h-14 px-1">
            <div class="flash-sale__blitz overflow-hidden relative z-20 ml-3 w-6 h-8 lg:w-10 lg:h-12 shrink-0" aria-hidden="true">
                <svg viewBox="0 0 64 96" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M36 4L12 52h16l-4 40 32-56H40L36 4z" fill="url(#flashBlitzGrad)"/>
                    <defs>
                        <linearGradient id="flashBlitzGrad" x1="12" y1="4" x2="52" y2="92" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#e9d5ff"/>
                            <stop offset="0.45" stop-color="#c084fc"/>
                            <stop offset="1" stop-color="#7c3aed"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <h2 class="font-extrabold text-sm lg:text-xl ml-2 text-white drop-shadow">{{ __('flash_sale.title') }}</h2>
            @if($flashSaleEndsAt)
                <div class="flash-sale__timer ml-auto mr-3 font-semibold h-[22px] lg:h-8 flex text-[10px] lg:text-sm items-center px-2.5 lg:px-4 rounded-md text-white"
                     data-flash-countdown
                     data-target-at="{{ \Carbon\Carbon::parse($flashSaleEndsAt)->toIso8601String() }}">
                    <span data-cd-h>00</span>:<span data-cd-m>00</span>:<span data-cd-s>00</span>
                </div>
            @endif
        </div>

        <div class="flash-sale__track md:pt-8 pt-4 flex gap-4 overflow-x-auto snap-x snap-mandatory pb-2 -mx-1 px-1"
             style="scrollbar-width: thin;">
            @foreach($flashSales as $offer)
                @php
                    $discount = $offer->discountPercent();
                    $image = $offer->imageUrl();
                @endphp
                <button type="button"
                        class="flash-sale__card snap-start relative overflow-hidden py-3 px-3 block min-h-[112px] lg:min-h-[148px] shrink-0 w-[280px] lg:w-[380px] rounded-xl border border-purple-300/40 text-left"
                        data-flash-offer
                        data-offer-id="{{ $offer->id }}"
                        data-offer-name="{{ e($offer->name) }}"
                        data-game-type="{{ e($offer->game_type) }}"
                        data-game-label="{{ e($offer->gameLabel()) }}"
                        data-sale-price="{{ (float) $offer->sale_price_dzd }}"
                        data-original-price="{{ (float) $offer->original_price_dzd }}"
                        data-checkout-url="{{ route('api.flash-sales.checkout', $offer) }}">
                    <div class="flex items-center gap-3 relative z-10 h-full">
                        <div class="relative shrink-0">
                            @if($image)
                                <img alt="{{ $offer->name }}"
                                     loading="eager"
                                     fetchpriority="high"
                                     decoding="async"
                                     class="flash-sale__pack-img loaded rounded-lg w-[78px] h-[78px] lg:w-[110px] lg:h-[110px] object-cover shadow-md ring-2 ring-white/25"
                                     src="{{ $image }}">
                            @else
                                <div class="rounded-lg w-[78px] h-[78px] lg:w-[110px] lg:h-[110px] bg-gradient-to-br from-purple-500 to-fuchsia-600"></div>
                            @endif
                            @if($discount > 0)
                                <div class="flash-sale__badge absolute -top-2 -right-2 rounded-md py-0.5 px-1.5 lg:px-2 font-bold text-[8px] lg:text-[10px] text-center text-white shadow">
                                    {{ __('flash_sale.off', ['percent' => $discount]) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 pr-1">
                            <p class="text-[10px] lg:text-sm text-purple-200/90 truncate">{{ $offer->gameLabel() }}</p>
                            <p class="mt-1 text-sm lg:text-lg font-semibold leading-snug text-white line-clamp-2">{{ $offer->name }}</p>
                            <div class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                <span class="text-[10px] lg:text-xs line-through text-white/45">{{ number_format((float) $offer->original_price_dzd, 0) }} DZD</span>
                                <strong class="text-fuchsia-300 text-sm lg:text-base font-bold">{{ number_format((float) $offer->sale_price_dzd, 0) }} DZD</strong>
                            </div>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Buy modal --}}
    <div id="flash-sale-modal" class="flash-sale-modal hidden fixed inset-0 z-[120] items-center justify-center p-4" aria-hidden="true">
        <div class="flash-sale-modal__backdrop absolute inset-0 bg-black/60" data-flash-close></div>
        <div class="flash-sale-modal__panel relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl border border-purple-100 p-6" role="dialog" aria-modal="true">
            <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-purple-700" data-flash-close aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <p class="text-xs uppercase tracking-wide text-purple-600 font-semibold" data-flash-game-label></p>
            <h3 class="text-xl font-bold text-gray-900 mt-1" data-flash-offer-title></h3>
            <p class="text-sm text-gray-500 mt-1">
                <span class="line-through" data-flash-original></span>
                <strong class="text-purple-600 ml-1" data-flash-sale></strong>
            </p>

            <form id="flash-sale-form" class="mt-5 space-y-3">
                <div data-flash-fields></div>
                <p class="text-sm text-green-600 hidden" data-flash-nickname></p>
                <p class="text-sm text-red-600 hidden" data-flash-error></p>
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-700 hover:to-fuchsia-700 text-white font-semibold py-3 rounded-lg transition shadow-md shadow-purple-200">
                    {{ __('flash_sale.buy_now') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Login gate --}}
    <div id="flash-sale-login" class="flash-sale-modal hidden fixed inset-0 z-[130] items-center justify-center p-4" aria-hidden="true">
        <div class="flash-sale-modal__backdrop absolute inset-0 bg-black/60" data-flash-login-close></div>
        <div class="flash-sale-modal__panel relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-purple-100 p-6 text-center">
            <h3 class="text-lg font-bold text-gray-900">{{ __('flash_sale.login_modal_title') }}</h3>
            <p class="text-sm text-gray-600 mt-2">{{ __('flash_sale.login_required') }}</p>
            <a id="flash-sale-login-link"
               href="{{ route('login', ['redirect' => '/?flash_resume=1']) }}"
               class="mt-5 inline-flex w-full justify-center bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-700 hover:to-fuchsia-700 text-white font-semibold py-3 rounded-lg shadow-md shadow-purple-200">
                {{ __('nav.login') ?? 'Log in' }}
            </a>
            <button type="button" class="mt-3 text-sm text-gray-500 underline" data-flash-login-close>{{ __('flash_sale.close') }}</button>
        </div>
    </div>
</section>

<style>
.flash-sale { padding: 1.25rem 0 0.5rem; }
.flash-sale__ribbon {
    background: linear-gradient(105deg, #7c3aed 0%, #9333ea 40%, #c084fc 70%, #db2777 100%);
    box-shadow: 0 8px 24px rgba(124, 58, 237, 0.28);
}
.flash-sale__timer {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.28);
    backdrop-filter: blur(6px);
}
.flash-sale__badge {
    background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
}
.flash-sale__card {
    background: linear-gradient(135deg, #4c1d95 0%, #6b21a8 45%, #7e22ce 100%);
    box-shadow: 0 10px 24px rgba(88, 28, 135, 0.25);
}
.flash-sale__card:hover {
    border-color: rgba(216, 180, 254, 0.7);
    box-shadow: 0 14px 28px rgba(124, 58, 237, 0.35);
}
.flash-sale__track::-webkit-scrollbar { height: 6px; }
.flash-sale__track::-webkit-scrollbar-thumb { background: #a855f788; border-radius: 999px; }
.flash-sale-modal:not(.hidden) { display: flex; }
.flash-sale__field-error { min-height: 1.1rem; }
</style>

<script>
(function () {
    const root = document.querySelector('[data-flash-sale]');
    if (!root) return;

    const DRAFT_KEY = 'diaszone_flash_sale_draft';
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    const loginUrl = @json(route('login'));
    const validateUrl = @json(route('api.validate-nickname'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const i18n = {
        invalidIds: @json(__('flash_sale.invalid_user_zone')),
        invalidPlayer: @json(__('flash_sale.invalid_player_id')),
        notAvailable: @json(__('flash_sale.not_available')),
        nickname: @json(__('flash_sale.nickname')),
        userId: @json(__('game.user_id')),
        zoneId: @json(__('game.zone_id')),
        playerId: @json(__('game.player_id')),
    };

    const modal = document.getElementById('flash-sale-modal');
    const loginModal = document.getElementById('flash-sale-login');
    const loginLink = document.getElementById('flash-sale-login-link');
    const form = document.getElementById('flash-sale-form');
    const fieldsWrap = modal.querySelector('[data-flash-fields]');
    const errorEl = modal.querySelector('[data-flash-error]');
    const nickEl = modal.querySelector('[data-flash-nickname]');
    let current = null;
    let nicknameOk = false;

    function fieldsFor(gameType) {
        if (gameType === 'mobilelegends') {
            return [
                { name: 'user_id', label: i18n.userId, pattern: true },
                { name: 'zone_id', label: i18n.zoneId, pattern: true },
            ];
        }
        if (gameType === 'bloodstrike') {
            return [
                { name: 'user_id_bs', label: i18n.userId, pattern: true },
                { name: 'server_bs', label: 'Server', select: [{ value: 'global', label: 'Global' }] },
            ];
        }
        if (['freefire', 'pubgmobile', 'pubg_mobile', 'honorofkings'].includes(gameType)) {
            return [{ name: 'player_id', label: i18n.playerId, pattern: true }];
        }
        return [{ name: 'save_id', label: i18n.userId, pattern: false }];
    }

    function clearFieldErrors() {
        fieldsWrap.querySelectorAll('[data-field-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        fieldsWrap.querySelectorAll('input, select').forEach((el) => {
            el.classList.remove('border-red-500', 'ring-1', 'ring-red-400');
            el.classList.add('border-gray-200');
        });
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
    }

    function setFieldError(name, message) {
        const input = fieldsWrap.querySelector(`[name="${name}"]`);
        const err = fieldsWrap.querySelector(`[data-field-error="${name}"]`);
        if (input) {
            input.classList.remove('border-gray-200');
            input.classList.add('border-red-500', 'ring-1', 'ring-red-400');
        }
        if (err) {
            err.textContent = message;
            err.classList.remove('hidden');
        }
    }

    function renderFields(gameType, prefill = {}) {
        nicknameOk = false;
        nickEl.classList.add('hidden');
        nickEl.textContent = '';
        fieldsWrap.innerHTML = fieldsFor(gameType).map((f) => {
            if (f.select) {
                const opts = f.select.map((o) => {
                    const selected = String(prefill[f.name] || '') === String(o.value) ? 'selected' : '';
                    return `<option value="${o.value}" ${selected}>${o.label}</option>`;
                }).join('');
                return `<div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">${f.label}</label>
                    <select name="${f.name}" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-200">${opts}</select>
                    <p class="flash-sale__field-error text-xs text-red-600 mt-1 hidden" data-field-error="${f.name}"></p>
                </div>`;
            }
            const pattern = f.pattern ? 'pattern="[0-9]+"' : '';
            const value = prefill[f.name] ? `value="${String(prefill[f.name]).replace(/"/g, '&quot;')}"` : '';
            return `<div>
                <label class="block text-sm font-medium text-gray-700 mb-1">${f.label}</label>
                <input type="text" name="${f.name}" required ${pattern} ${value}
                    class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-200"
                    placeholder="${f.label}" autocomplete="off">
                <p class="flash-sale__field-error text-xs text-red-600 mt-1 hidden" data-field-error="${f.name}"></p>
            </div>`;
        }).join('');
    }

    function openModal(btn, prefill = {}) {
        current = {
            id: btn.dataset.offerId,
            name: btn.dataset.offerName,
            gameType: btn.dataset.gameType,
            gameLabel: btn.dataset.gameLabel,
            sale: btn.dataset.salePrice,
            original: btn.dataset.originalPrice,
            url: btn.dataset.checkoutUrl,
        };
        modal.querySelector('[data-flash-game-label]').textContent = current.gameLabel;
        modal.querySelector('[data-flash-offer-title]').textContent = current.name;
        modal.querySelector('[data-flash-original]').textContent = Number(current.original).toLocaleString() + ' DZD';
        modal.querySelector('[data-flash-sale]').textContent = Number(current.sale).toLocaleString() + ' DZD';
        clearFieldErrors();
        renderFields(current.gameType, prefill);
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        try {
            root.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (_) {}
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        current = null;
    }

    function saveDraft(fields) {
        if (!current) return;
        try {
            sessionStorage.setItem(DRAFT_KEY, JSON.stringify({
                offerId: String(current.id),
                fields: fields || {},
                savedAt: Date.now(),
            }));
        } catch (_) {}
    }

    function readDraft() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (!raw) return null;
            const draft = JSON.parse(raw);
            if (!draft || !draft.offerId) return null;
            if (draft.savedAt && (Date.now() - draft.savedAt) > 2 * 60 * 60 * 1000) {
                sessionStorage.removeItem(DRAFT_KEY);
                return null;
            }
            return draft;
        } catch (_) {
            return null;
        }
    }

    function clearDraft() {
        try { sessionStorage.removeItem(DRAFT_KEY); } catch (_) {}
    }

    function goToLogin() {
        const fd = form ? Object.fromEntries(new FormData(form).entries()) : {};
        saveDraft(fd);
        const resumePath = '/?flash_resume=1';
        if (loginLink) {
            loginLink.href = loginUrl + (loginUrl.includes('?') ? '&' : '?') + 'redirect=' + encodeURIComponent(resumePath);
        }
        openLogin();
    }

    function openLogin() {
        loginModal.classList.remove('hidden');
        loginModal.setAttribute('aria-hidden', 'false');
    }

    function closeLogin() {
        loginModal.classList.add('hidden');
        loginModal.setAttribute('aria-hidden', 'true');
    }

    root.querySelectorAll('[data-flash-offer]').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn));
    });
    modal.querySelectorAll('[data-flash-close]').forEach((el) => el.addEventListener('click', closeModal));
    loginModal.querySelectorAll('[data-flash-login-close]').forEach((el) => el.addEventListener('click', closeLogin));
    if (loginLink) {
        loginLink.addEventListener('click', () => {
            const fd = form ? Object.fromEntries(new FormData(form).entries()) : {};
            saveDraft(fd);
        });
    }

    async function verifyMlIfNeeded(payload) {
        if (current.gameType !== 'mobilelegends') {
            nicknameOk = true;
            return true;
        }
        clearFieldErrors();
        const res = await fetch(validateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ user_id: payload.user_id, zone_id: payload.zone_id }),
        });
        const data = await res.json().catch(() => ({}));
        const ok = res.ok && (data.result === true || data.success === true);
        if (!ok) {
            const msg = data.message || i18n.invalidIds;
            setFieldError('user_id', msg);
            setFieldError('zone_id', msg);
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
            return false;
        }
        const nick = data.nickname || data.data?.username || (typeof data.data === 'string' ? data.data : null) || data.username || 'OK';
        nickEl.textContent = i18n.nickname + ': ' + nick;
        nickEl.classList.remove('hidden');
        nicknameOk = true;
        return true;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!current) return;
        if (!isLoggedIn) {
            goToLogin();
            return;
        }

        clearFieldErrors();
        const fd = new FormData(form);
        const payload = Object.fromEntries(fd.entries());
        saveDraft(payload);

        const ok = await verifyMlIfNeeded(payload);
        if (!ok) return;

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const res = await fetch(current.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 401 || data.require_login) {
                goToLogin();
                return;
            }
            if (!res.ok || !data.success) {
                const msg = data.message || i18n.notAvailable;
                if (data.errors) {
                    Object.keys(data.errors).forEach((key) => {
                        const first = Array.isArray(data.errors[key]) ? data.errors[key][0] : data.errors[key];
                        setFieldError(key, first);
                    });
                }
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
                return;
            }

            clearDraft();

            if (data.encrypted_order_id) {
                try {
                    const raw = localStorage.getItem('diaszone_encrypted_order_ids');
                    let arr = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(arr)) arr = [];
                    if (!arr.includes(data.encrypted_order_id)) arr.push(data.encrypted_order_id);
                    localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(arr));
                } catch (_) {}
            }

            window.location.href = data.redirect_url;
        } catch (err) {
            errorEl.textContent = i18n.notAvailable;
            errorEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    const cd = root.querySelector('[data-flash-countdown]');
    if (cd) {
        const target = new Date(cd.dataset.targetAt).getTime();
        const tick = () => {
            const diff = Math.max(0, target - Date.now());
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            cd.querySelector('[data-cd-h]').textContent = String(h).padStart(2, '0');
            cd.querySelector('[data-cd-m]').textContent = String(m).padStart(2, '0');
            cd.querySelector('[data-cd-s]').textContent = String(s).padStart(2, '0');
        };
        tick();
        setInterval(tick, 1000);
    }

    // After Google login: reopen modal with saved fields
    function maybeResumeAfterLogin() {
        const params = new URLSearchParams(window.location.search);
        const wantsResume = params.get('flash_resume') === '1' || params.get('flash_offer');
        const draft = readDraft();
        const offerId = params.get('flash_offer') || (draft && draft.offerId);
        if (!offerId) return;
        if (!isLoggedIn && !wantsResume) return;

        const btn = root.querySelector('[data-flash-offer][data-offer-id="' + String(offerId).replace(/"/g, '') + '"]');
        if (!btn) return;

        openModal(btn, (draft && String(draft.offerId) === String(offerId)) ? (draft.fields || {}) : {});

        if (wantsResume) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('flash_resume');
                url.searchParams.delete('flash_offer');
                window.history.replaceState({}, '', url.pathname + (url.search || '') + url.hash);
            } catch (_) {}
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', maybeResumeAfterLogin);
    } else {
        maybeResumeAfterLogin();
    }
})();
</script>
@endif
