@php
    /** @var \Illuminate\Support\Collection<\App\Models\FlashSaleOffer> $flashSales */
    $flashSales = $flashSales ?? collect();
    $flashSaleEndsAt = $flashSaleEndsAt ?? ($flashSales->max('ends_at'));
@endphp

@if($flashSales->isNotEmpty())
<section class="flash-sale" data-flash-sale aria-label="{{ __('flash_sale.title') }}">
    <div class="container relative z-10 mx-auto w-full max-w-screen-2xl px-4 md:px-8 2xl:px-3 overflow-visible">
        <div class="flash-sale__ribbon relative rounded-md flex items-center w-full h-9 lg:h-16">
            <div class="flash-sale__blitz overflow-hidden relative z-20 ml-4 w-7 h-10 lg:w-16 lg:h-24" aria-hidden="true">
                <svg viewBox="0 0 64 96" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M36 4L12 52h16l-4 40 32-56H40L36 4z" fill="url(#flashBlitzGrad)"/>
                    <defs>
                        <linearGradient id="flashBlitzGrad" x1="12" y1="4" x2="52" y2="92" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FDB37F"/>
                            <stop offset="0.5" stop-color="#F3491B"/>
                            <stop offset="1" stop-color="#EC1F3E"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <h2 class="font-extrabold text-sm lg:text-xl ml-3 text-white drop-shadow">{{ __('flash_sale.title') }}</h2>
            @if($flashSaleEndsAt)
                <div class="flash-sale__timer ml-7 font-semibold h-[18px] lg:h-8 flex text-[9px] lg:text-base items-center px-2 lg:px-4 rounded-sm text-white"
                     data-flash-countdown
                     data-target-at="{{ \Carbon\Carbon::parse($flashSaleEndsAt)->toIso8601String() }}">
                    <span data-cd-h>00</span>:<span data-cd-m>00</span>:<span data-cd-s>00</span>
                </div>
            @endif
        </div>

        <div class="flash-sale__track md:pt-10 pt-5 flex gap-[18px] overflow-x-auto snap-x snap-mandatory pb-2 -mx-1 px-1"
             style="scrollbar-width: thin;">
            @foreach($flashSales as $offer)
                @php
                    $discount = $offer->discountPercent();
                    $image = $offer->imageUrl();
                @endphp
                <button type="button"
                        class="flash-sale__card snap-start relative overflow-hidden py-4 px-2 block h-[100px] lg:h-[146px] shrink-0 w-[261px] lg:w-[375px] rounded-[10px] border border-[#F3491B] text-left"
                        data-flash-offer
                        data-offer-id="{{ $offer->id }}"
                        data-offer-name="{{ e($offer->name) }}"
                        data-game-type="{{ e($offer->game_type) }}"
                        data-game-label="{{ e($offer->gameLabel()) }}"
                        data-sale-price="{{ (float) $offer->sale_price_dzd }}"
                        data-original-price="{{ (float) $offer->original_price_dzd }}"
                        data-checkout-url="{{ route('api.flash-sales.checkout', $offer) }}">
                    <div class="absolute inset-0 bg-black/60"></div>
                    <div class="flex gap-5 relative z-20">
                        <div class="relative shrink-0">
                            @if($image)
                                <img alt="{{ $offer->name }}"
                                     loading="lazy"
                                     class="rounded-md lg:rounded-[10px] w-[62px] h-[62px] lg:w-[90px] lg:h-[90px] object-cover"
                                     src="{{ $image }}">
                            @else
                                <div class="rounded-md lg:rounded-[10px] w-[62px] h-[62px] lg:w-[90px] lg:h-[90px] bg-gradient-to-br from-orange-500 to-red-600"></div>
                            @endif
                            @if($discount > 0)
                                <div class="flash-sale__badge relative rounded-xs py-1 lg:px-3 font-semibold text-[6px] lg:text-[10px] text-center w-[48px] lg:w-[70px] mx-auto -top-2 text-white">
                                    {{ __('flash_sale.off', ['percent' => $discount]) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-[9px] lg:text-sm text-[#9E9E9E]">{{ $offer->gameLabel() }}</p>
                            <p class="mt-1 text-sm lg:text-lg font-semibold leading-none text-white">{{ $offer->name }}</p>
                            <div class="lg:mt-2 flex items-center gap-1">
                                <span class="text-[8px] lg:text-[10px] line-through text-gray-400">{{ number_format((float) $offer->original_price_dzd, 0) }} DZD</span>
                                <strong class="text-[#FF6920] text-[10px] lg:text-sm font-semibold">{{ number_format((float) $offer->sale_price_dzd, 0) }} DZD</strong>
                            </div>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Buy modal --}}
    <div id="flash-sale-modal" class="flash-sale-modal hidden fixed inset-0 z-[120] items-center justify-center p-4" aria-hidden="true">
        <div class="flash-sale-modal__backdrop absolute inset-0 bg-black/70" data-flash-close></div>
        <div class="flash-sale-modal__panel relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl p-6" role="dialog" aria-modal="true">
            <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700" data-flash-close aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <p class="text-xs uppercase tracking-wide text-orange-600 font-semibold" data-flash-game-label></p>
            <h3 class="text-xl font-bold text-gray-900 mt-1" data-flash-offer-title></h3>
            <p class="text-sm text-gray-500 mt-1">
                <span class="line-through" data-flash-original></span>
                <strong class="text-[#FF6920] ml-1" data-flash-sale></strong>
            </p>

            <form id="flash-sale-form" class="mt-5 space-y-3">
                <div data-flash-fields></div>
                <p class="text-sm text-green-600 hidden" data-flash-nickname></p>
                <p class="text-sm text-red-600 hidden" data-flash-error></p>
                <button type="submit" class="w-full bg-gradient-to-r from-[#F3491B] to-[#EC1F3E] hover:opacity-95 text-white font-semibold py-3 rounded-lg transition">
                    {{ __('flash_sale.buy_now') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Login gate --}}
    <div id="flash-sale-login" class="flash-sale-modal hidden fixed inset-0 z-[130] items-center justify-center p-4" aria-hidden="true">
        <div class="flash-sale-modal__backdrop absolute inset-0 bg-black/70" data-flash-login-close></div>
        <div class="flash-sale-modal__panel relative z-10 w-full max-w-sm rounded-2xl bg-white shadow-2xl p-6 text-center">
            <h3 class="text-lg font-bold text-gray-900">{{ __('flash_sale.login_modal_title') }}</h3>
            <p class="text-sm text-gray-600 mt-2">{{ __('flash_sale.login_required') }}</p>
            <a href="{{ route('login') }}" class="mt-5 inline-flex w-full justify-center bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg">
                {{ __('nav.login') ?? 'Log in' }}
            </a>
            <button type="button" class="mt-3 text-sm text-gray-500 underline" data-flash-login-close>Close</button>
        </div>
    </div>
</section>

<style>
.flash-sale { padding: 1.25rem 0 0.5rem; }
.flash-sale__ribbon {
    background: linear-gradient(103.06deg, #F3491B -8.3%, #F3491B 19.29%, #FDB37F 51.87%, #EC1F3E 82.25%, #EC1F3E 116.43%);
    box-shadow: 0 8px 24px rgba(243, 73, 27, 0.25);
}
.flash-sale__timer {
    background: linear-gradient(103.06deg, #F3491B -8.3%, #F3491B 19.29%, #FDB37F 51.87%, #EC1F3E 82.25%, #EC1F3E 116.43%);
    filter: brightness(0.92);
}
.flash-sale__badge {
    background: linear-gradient(103.06deg, rgb(243, 73, 27) -8.3%, rgb(243, 73, 27) 19.29%, rgb(253, 179, 127) 51.87%, rgb(236, 31, 62) 82.25%, rgb(236, 31, 62) 116.43%);
}
.flash-sale__card { background: #111; }
.flash-sale__track::-webkit-scrollbar { height: 6px; }
.flash-sale__track::-webkit-scrollbar-thumb { background: #F3491B88; border-radius: 999px; }
.flash-sale-modal:not(.hidden) { display: flex; }
</style>

<script>
(function () {
    const root = document.querySelector('[data-flash-sale]');
    if (!root) return;

    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    const loginUrl = @json(route('login'));
    const validateUrl = @json(route('api.validate-nickname'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const modal = document.getElementById('flash-sale-modal');
    const loginModal = document.getElementById('flash-sale-login');
    const form = document.getElementById('flash-sale-form');
    const fieldsWrap = modal.querySelector('[data-flash-fields]');
    const errorEl = modal.querySelector('[data-flash-error]');
    const nickEl = modal.querySelector('[data-flash-nickname]');
    let current = null;
    let nicknameOk = false;

    function fieldsFor(gameType) {
        if (gameType === 'mobilelegends') {
            return [
                { name: 'user_id', label: @json(__('game.user_id')), pattern: true },
                { name: 'zone_id', label: @json(__('game.zone_id')), pattern: true },
            ];
        }
        if (gameType === 'bloodstrike') {
            return [
                { name: 'user_id_bs', label: 'User ID', pattern: true },
                { name: 'server_bs', label: 'Server', select: [{ value: 'global', label: 'Global' }] },
            ];
        }
        if (['freefire', 'pubgmobile', 'pubg_mobile', 'honorofkings'].includes(gameType)) {
            return [{ name: 'player_id', label: @json(__('game.player_id')), pattern: true }];
        }
        return [{ name: 'save_id', label: 'User ID', pattern: false }];
    }

    function renderFields(gameType) {
        nicknameOk = false;
        nickEl.classList.add('hidden');
        nickEl.textContent = '';
        fieldsWrap.innerHTML = fieldsFor(gameType).map((f) => {
            if (f.select) {
                const opts = f.select.map((o) => `<option value="${o.value}">${o.label}</option>`).join('');
                return `<div><label class="block text-sm font-medium text-gray-700 mb-1">${f.label}</label>
                    <select name="${f.name}" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm">${opts}</select></div>`;
            }
            const pattern = f.pattern ? 'pattern="[0-9]+"' : '';
            return `<div><label class="block text-sm font-medium text-gray-700 mb-1">${f.label}</label>
                <input type="text" name="${f.name}" required ${pattern}
                    class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm"
                    placeholder="${f.label}"></div>`;
        }).join('');
    }

    function openModal(btn) {
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
        errorEl.classList.add('hidden');
        renderFields(current.gameType);
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        current = null;
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

    async function verifyMlIfNeeded(payload) {
        if (current.gameType !== 'mobilelegends') {
            nicknameOk = true;
            return true;
        }
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
        if (!res.ok || !data.success) {
            errorEl.textContent = data.message || 'Invalid User ID / Zone ID';
            errorEl.classList.remove('hidden');
            return false;
        }
        nickEl.textContent = (@json(__('flash_sale.nickname'))) + ': ' + (data.nickname || data.data?.username || data.username || 'OK');
        nickEl.classList.remove('hidden');
        nicknameOk = true;
        return true;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!current) return;
        if (!isLoggedIn) {
            openLogin();
            return;
        }

        errorEl.classList.add('hidden');
        const fd = new FormData(form);
        const payload = Object.fromEntries(fd.entries());

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
                openLogin();
                return;
            }
            if (!res.ok || !data.success) {
                errorEl.textContent = data.message || @json(__('flash_sale.not_available'));
                errorEl.classList.remove('hidden');
                return;
            }

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
            errorEl.textContent = @json(__('flash_sale.not_available'));
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
})();
</script>
@endif
