@extends('layouts.app')

@section('title', __('dashboard.page_title'))

@push('styles')
<style>
.dz-dash {
    --dz-ink: #0f172a;
    --dz-muted: #64748b;
    --dz-line: #e2e8f0;
    --dz-soft: #f8fafc;
    --dz-accent: #6d28d9;
    --dz-accent-soft: #f5f3ff;
}
.dz-dash-shell {
    background:
        radial-gradient(1200px 420px at 10% -10%, rgba(109, 40, 217, 0.08), transparent 55%),
        radial-gradient(900px 360px at 100% 0%, rgba(14, 165, 233, 0.06), transparent 50%),
        #f8fafc;
}
.dz-dash-nav {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.15rem;
}
.dz-dash-nav::-webkit-scrollbar { display: none; }
.dz-dash-tab {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--dz-muted);
    background: #fff;
    border: 1px solid var(--dz-line);
    white-space: nowrap;
    transition: background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease;
}
.dz-dash-tab:hover {
    color: var(--dz-accent);
    border-color: #ddd6fe;
    background: var(--dz-accent-soft);
}
.dz-dash-tab.is-active {
    color: #fff;
    background: var(--dz-accent);
    border-color: var(--dz-accent);
    box-shadow: 0 8px 20px rgba(109, 40, 217, 0.22);
}
.dz-order-card {
    background: #fff;
    border: 1px solid var(--dz-line);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: box-shadow .2s ease, border-color .2s ease;
}
@media (min-width: 640px) {
    .dz-order-card { padding: 1.25rem 1.5rem; }
}
.dz-order-card:hover {
    border-color: #ddd6fe;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
}
.dz-meta-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}
@media (min-width: 640px) {
    .dz-meta-grid { grid-template-columns: 1fr 1fr; gap: 0.65rem 1rem; }
}
.dz-meta-item {
    min-width: 0;
    background: var(--dz-soft);
    border: 1px solid var(--dz-line);
    border-radius: 0.75rem;
    padding: 0.65rem 0.8rem;
}
.dz-meta-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--dz-muted);
    margin-bottom: 0.2rem;
}
.dz-meta-value {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--dz-ink);
    word-break: break-word;
}
.dz-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.7rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
    line-height: 1.2;
    white-space: nowrap;
}
.dz-skel {
    background: linear-gradient(90deg, #eef2ff 0%, #f8fafc 45%, #eef2ff 100%);
    background-size: 200% 100%;
    animation: dz-shimmer 1.2s ease-in-out infinite;
    border-radius: 0.75rem;
}
@keyframes dz-shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
</style>
@endpush

@section('content')
@php
    $sectionTitle = match ($activeSection) {
        'orders' => __('seller.my_orders'),
        'invoices' => __('seller.invoices'),
        'notifications' => __('seller.notifications'),
        'account' => __('seller.my_account'),
        default => __('dashboard.title'),
    };
@endphp
<div class="dz-dash dz-dash-shell min-h-screen py-6 sm:py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        <header class="mb-5 sm:mb-7">
            <p class="text-xs sm:text-sm font-bold tracking-wide uppercase text-violet-700/80 mb-1">DiasZone</p>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $sectionTitle }}</h1>
            @if($activeSection === 'orders')
                <p class="mt-1.5 text-sm sm:text-base text-slate-500 max-w-2xl">{{ __('dashboard.orders_subtitle') }}</p>
            @endif
        </header>

        <nav class="dz-dash-nav mb-5 sm:mb-7" aria-label="Dashboard">
            <a href="{{ route('dashboard.orders') }}" class="dz-dash-tab {{ $activeSection === 'orders' ? 'is-active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                {{ __('seller.my_orders') }}
            </a>
            <a href="{{ route('dashboard.invoices') }}" class="dz-dash-tab {{ $activeSection === 'invoices' ? 'is-active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('seller.invoices') }}
            </a>
            <a href="{{ route('dashboard.notifications') }}" class="dz-dash-tab {{ $activeSection === 'notifications' ? 'is-active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                {{ __('seller.notifications') }}
            </a>
            <a href="{{ route('dashboard.myaccount') }}" class="dz-dash-tab {{ $activeSection === 'account' ? 'is-active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                {{ __('seller.my_account') }}
            </a>
        </nav>

        <div class="min-w-0">
            @if($activeSection === 'orders')
                <div id="orders-section">
                    <div id="orders-loading" class="space-y-3 sm:space-y-4">
                        @for($i = 0; $i < 3; $i++)
                        <div class="dz-order-card">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex-1 space-y-2">
                                    <div class="dz-skel h-5 w-36"></div>
                                    <div class="dz-skel h-4 w-48"></div>
                                </div>
                                <div class="dz-skel h-7 w-20"></div>
                            </div>
                            <div class="dz-meta-grid mb-3">
                                <div class="dz-skel h-14"></div>
                                <div class="dz-skel h-14"></div>
                            </div>
                            <div class="dz-skel h-4 w-40"></div>
                        </div>
                        @endfor
                    </div>
                    <div id="orders-container" class="hidden space-y-3 sm:space-y-4"></div>
                    <div id="orders-empty" class="hidden text-center py-14 px-4 bg-white border border-slate-200 rounded-2xl">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-700">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-bold text-slate-900">{{ __('seller.no_orders_yet') }}</p>
                        <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">{{ __('seller.orders_empty_info') }}</p>
                        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-violet-700 hover:bg-violet-800 text-white text-sm font-bold shadow-sm">
                                {{ __('dashboard.start_shopping') }}
                            </a>
                            @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 text-sm font-bold">
                                {{ __('dashboard.login_to_see_orders') }}
                            </a>
                            @endguest
                        </div>
                    </div>
                </div>
            @elseif($activeSection === 'invoices')
                <div id="invoices-section" class="space-y-3 sm:space-y-4">
                    @forelse($invoices as $invoice)
                        <article class="dz-order-card">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <h2 class="text-base sm:text-lg font-black text-slate-900">{{ $invoice['invoice_number'] }}</h2>
                                    <div class="dz-meta-grid mt-3">
                                        <div class="dz-meta-item">
                                            <span class="dz-meta-label">{{ __('dashboard.order_label') }}</span>
                                            <span class="dz-meta-value">{{ $invoice['order_id'] }}</span>
                                        </div>
                                        <div class="dz-meta-item">
                                            <span class="dz-meta-label">{{ __('dashboard.payment') }}</span>
                                            <span class="dz-meta-value">{{ $invoice['payment_method'] }}</span>
                                        </div>
                                        <div class="dz-meta-item">
                                            <span class="dz-meta-label">{{ __('dashboard.issue_date') }}</span>
                                            <span class="dz-meta-value">{{ $invoice['issue_date'] }}</span>
                                        </div>
                                        <div class="dz-meta-item">
                                            <span class="dz-meta-label">{{ __('dashboard.due_date') }}</span>
                                            <span class="dz-meta-value">{{ $invoice['due_date'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 shrink-0">
                                    <p class="text-xl sm:text-2xl font-black text-violet-700">US$ {{ number_format($invoice['amount'], 2) }}</p>
                                    <span class="dz-status {{ $invoice['status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($invoice['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ ucfirst($invoice['status']) }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-14 px-4 bg-white border border-slate-200 rounded-2xl">
                            <p class="text-lg font-bold text-slate-900">{{ __('seller.no_invoices_yet') }}</p>
                        </div>
                    @endforelse
                </div>
            @elseif($activeSection === 'notifications')
                <div id="notifications-section" class="text-center py-14 px-4 bg-white border border-slate-200 rounded-2xl">
                    <p class="text-lg font-bold text-slate-900">{{ __('seller.no_notifications_yet') }}</p>
                </div>
            @elseif($activeSection === 'account')
                @auth
                <div id="account-section" class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-8">
                    <div class="space-y-5 max-w-xl">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.name') }}</label>
                            <input type="text" value="{{ $user['name'] }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.email') }}</label>
                            <input type="email" value="{{ $user['email'] }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900" readonly>
                        </div>
                        @if(!empty($user['phone']))
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">{{ __('dashboard.phone') }}</label>
                            <input type="text" value="{{ $user['phone'] }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900" readonly>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div id="account-section" class="text-center py-14 px-4 bg-white border border-slate-200 rounded-2xl">
                    <h2 class="text-xl font-black text-slate-900 mb-2">{{ __('seller.my_account') }}</h2>
                    <p class="text-slate-500 mb-6">{{ __('auth.login_required_message') ?? 'Please log in to view your account information.' }}</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-violet-700 hover:bg-violet-800 text-white font-bold">
                        {{ __('profile.login') }}
                    </a>
                </div>
                @endauth
            @endif
        </div>
    </div>
</div>

@if($activeSection === 'orders')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const t = {
        game: {!! json_encode(__('dashboard.game')) !!},
        orderNumber: {!! json_encode(__('dashboard.order_number')) !!},
        created: {!! json_encode(__('dashboard.created')) !!},
        userId: {!! json_encode(__('checkout.user_id')) !!},
        zoneId: {!! json_encode(__('checkout.zone_id')) !!},
        playerId: {!! json_encode(__('checkout.player_id')) !!},
        server: {!! json_encode(__('checkout.server')) !!},
        email: {!! json_encode(__('dashboard.email')) !!},
        payment: {!! json_encode(__('dashboard.payment')) !!},
        continuePayment: {!! json_encode(__('dashboard.continue_payment')) !!},
        processing: {!! json_encode(__('dashboard.processing_delivery')) !!},
        processingHint: {!! json_encode(__('dashboard.processing_delivery_hint')) !!},
        deliveryReady: {!! json_encode(__('dashboard.delivery_ready')) !!},
        profile: {!! json_encode(__('dashboard.delivery_profile')) !!},
        openLink: {!! json_encode(__('dashboard.open_login_link')) !!},
        copyLink: {!! json_encode(__('dashboard.copy_link')) !!},
        copied: {!! json_encode(__('dashboard.copied')) !!},
        qty: {!! json_encode(__('dashboard.qty')) !!},
        specialPack: {!! json_encode(__('dashboard.special_pack')) !!},
    };

    const statusLabels = {
        pending: {!! json_encode(__('dashboard.status_pending')) !!},
        pending_flexy: {!! json_encode(__('dashboard.status_pending_flexy')) !!},
        pending_bmccp: {!! json_encode(__('dashboard.status_pending_baridimob')) !!},
        pending_cryptopay: {!! json_encode(__('dashboard.status_pending_crypto')) !!},
        pending_confirmation: {!! json_encode(__('dashboard.status_pending_confirmation')) !!},
        sending: {!! json_encode(__('dashboard.status_sending')) !!},
        completed: {!! json_encode(__('dashboard.status_completed')) !!},
        failed: {!! json_encode(__('dashboard.status_failed')) !!},
        refunded: {!! json_encode(__('dashboard.status_refunded')) !!},
        cancelled: {!! json_encode(__('dashboard.status_cancelled')) !!},
    };

    const paymentLabels = {
        bmccp: {!! json_encode(__('dashboard.pay_baridimob')) !!},
        baridimob: {!! json_encode(__('dashboard.pay_baridimob')) !!},
        flexy: {!! json_encode(__('dashboard.pay_flexy')) !!},
        cryptocurrency: {!! json_encode(__('dashboard.pay_crypto')) !!},
        coupon_free: {!! json_encode(__('dashboard.pay_coupon')) !!},
    };

    const ordersContainer = document.getElementById('orders-container');
    const ordersLoading = document.getElementById('orders-loading');
    const ordersEmpty = document.getElementById('orders-empty');
    const dashboardUserAuthenticated = @json(auth()->check());
    const mineUrl = @json(route('api.orders.mine'));
    const byEncryptedUrl = @json(route('api.orders.get-by-encrypted-id'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function metaItem(label, value) {
        if (value === null || value === undefined || value === '') return '';
        return `<div class="dz-meta-item"><span class="dz-meta-label">${esc(label)}</span><span class="dz-meta-value">${esc(value)}</span></div>`;
    }

    function normalizeGameType(order) {
        let gameType = (order.game_type || order.diamond_pack?.game_type || order.order_items?.[0]?.diamond_pack?.game_type || '').toLowerCase();
        if (!gameType) {
            if (order.user_id_ml || order.zone_id_ml) gameType = 'mobilelegends';
            else if (order.player_id_ff) gameType = 'freefire';
            else if (order.player_id_pubg) gameType = 'pubg_mobile';
            else if (order.player_id_hok) gameType = 'honorofkings';
            else if (order.user_id_bs) gameType = 'bloodstrike';
            else if (order.save_id) gameType = order.diamond_pack?.game_type || 'unknown';
            else gameType = 'unknown';
        }
        if (gameType === 'pubgmobile') gameType = 'pubg_mobile';
        if (gameType.startsWith('genshin_impact')) gameType = 'genshin_impact';
        return gameType;
    }

    function prettyGameName(order, gameType) {
        if (order.game_name) return order.game_name;
        const map = {
            mobilelegends: 'Mobile Legends',
            freefire: 'Free Fire',
            pubg_mobile: 'PUBG Mobile',
            pubgmobile: 'PUBG Mobile',
            genshin_impact: 'Genshin Impact',
            bloodstrike: 'Blood Strike',
            honorofkings: 'Honor of Kings',
            punishinggrayraven: 'Punishing: Gray Raven',
            wutheringwaves: 'Wuthering Waves',
            vipreseller: 'Digital',
        };
        return map[gameType] || gameType.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }

    function currencyLabel(gameType) {
        const map = {
            pubg_mobile: 'UC',
            pubgmobile: 'UC',
            honorofkings: 'Tokens',
            bloodstrike: 'Golds',
            wutheringwaves: 'Astrite',
            punishinggrayraven: 'Black Cards',
            genshin_impact: 'Crystals',
            devil_may_cry_peak_of_combat: 'Devil Gems',
        };
        return map[gameType] || 'Diamonds';
    }

    function buildDestinationMeta(order, gameType, isVipOrder) {
        let html = '';
        if (isVipOrder || gameType === 'vipreseller') {
            html += metaItem(t.email, order.customer_email);
            return html;
        }
        if (gameType === 'mobilelegends') {
            html += metaItem(t.userId, order.user_id_ml);
            html += metaItem(t.zoneId, order.zone_id_ml);
            return html;
        }
        if (gameType === 'freefire') {
            html += metaItem(t.playerId, order.player_id_ff || order.save_id);
            return html;
        }
        if (gameType === 'pubg_mobile' || gameType === 'pubgmobile') {
            html += metaItem(t.playerId, order.player_id_pubg || order.save_id);
            return html;
        }
        if (gameType === 'honorofkings') {
            html += metaItem(t.playerId, order.player_id_hok || order.save_id);
            return html;
        }
        if (gameType === 'bloodstrike') {
            html += metaItem(t.userId, order.user_id_bs || order.save_id);
            if (order.server_bs) html += metaItem(t.server, order.server_bs);
            return html;
        }
        // Genshin / PGR / WW / other save_id games
        html += metaItem(t.userId, order.save_id || order.player_id_ff || order.player_id_pubg || order.player_id_hok || order.user_id_bs || order.user_id_ml);
        if (order.server) html += metaItem(t.server, order.server);
        if (order.zone_id_ml && !order.server) html += metaItem(t.zoneId, order.zone_id_ml);
        return html;
    }

    function statusClass(status) {
        const map = {
            pending: 'bg-amber-100 text-amber-800',
            pending_flexy: 'bg-amber-100 text-amber-800',
            pending_bmccp: 'bg-amber-100 text-amber-800',
            pending_cryptopay: 'bg-amber-100 text-amber-800',
            pending_confirmation: 'bg-orange-100 text-orange-800',
            completed: 'bg-emerald-100 text-emerald-800',
            sending: 'bg-sky-100 text-sky-800',
            failed: 'bg-rose-100 text-rose-800',
            refunded: 'bg-rose-100 text-rose-800',
            cancelled: 'bg-slate-100 text-slate-700',
        };
        return map[status] || 'bg-slate-100 text-slate-700';
    }

    function paymentLabel(method) {
        if (!method) return '';
        return paymentLabels[method] || String(method).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }

    function diaszoneRenderDashboardOrders(validResults) {
        ordersLoading.classList.add('hidden');
        const filtered = (validResults || []).filter(Boolean);

        if (filtered.length === 0) {
            ordersContainer.classList.add('hidden');
            ordersEmpty.classList.remove('hidden');
            return;
        }

        ordersEmpty.classList.add('hidden');
        filtered.sort((a, b) => {
            const dateA = new Date(a.order.created_at).getTime() || 0;
            const dateB = new Date(b.order.created_at).getTime() || 0;
            if (dateB !== dateA) return dateB - dateA;
            return (parseInt(b.order.id || 0, 10) - parseInt(a.order.id || 0, 10));
        });

        function renderOrders(allOrders) {
            ordersContainer.innerHTML = allOrders.map((result) => {
                const order = result.order;
                const encryptedOrderId = result.encryptedId;
                const gameType = normalizeGameType(order);
                const gameName = prettyGameName(order, gameType);
                const currencyText = currencyLabel(gameType);
                const orderItems = Array.isArray(order.order_items) ? order.order_items : [];
                const isVipOrder = !!(order.is_vipreseller || order.vipreseller_pack || orderItems.some((i) => i.vipreseller_pack));
                const statusLabel = statusLabels[order.status]
                    || String(order.status || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

                let packLinesHtml = '';
                if (order.is_flash_sale && order.flash_sale_name) {
                    packLinesHtml = `<p class="text-sm sm:text-base font-bold text-violet-700">${esc(order.flash_sale_name)}</p>`;
                } else if (orderItems.length > 0) {
                    packLinesHtml = orderItems.map((item) => {
                        const vipPack = item.vipreseller_pack || {};
                        const pack = item.diamond_pack || {};
                        const qty = Math.max(1, parseInt(item.quantity || 1, 10));
                        let name = vipPack.name
                            || pack.name
                            || (parseInt(pack.diamonds || 0, 10) === 0
                                ? (pack.membership_name || t.specialPack)
                                : `${pack.diamonds || 0} ${currencyText}`);
                        const bonus = parseInt(pack.bonus_diamonds || 0, 10);
                        if (bonus > 0) name += ` + ${bonus} Bonus ${currencyText}`;
                        const qtyText = qty > 1 ? ` <span class="text-violet-500">× ${qty}</span>` : '';
                        return `<p class="text-sm sm:text-base font-bold text-violet-700">${esc(name)}${qtyText}</p>`;
                    }).join('');
                } else {
                    const packDiamonds = parseInt(order.diamond_pack?.diamonds || 0, 10);
                    let packDisplayName = order.vipreseller_pack?.name
                        || order.diamond_pack?.name
                        || (packDiamonds === 0
                            ? (order.diamond_pack?.membership_name || t.specialPack)
                            : `${packDiamonds} ${currencyText}`);
                    const bonus = order.diamond_pack?.bonus_diamonds || 0;
                    if (bonus > 0) packDisplayName += ` + ${bonus} Bonus ${currencyText}`;
                    const legacyQty = Math.max(1, parseInt(order.quantity || 1, 10));
                    const qtyText = legacyQty > 1 ? ` <span class="text-violet-500">× ${legacyQty}</span>` : '';
                    packLinesHtml = `<p class="text-sm sm:text-base font-bold text-violet-700">${esc(packDisplayName)}${qtyText}</p>`;
                }

                let paymentUrl = '';
                if (order.status === 'pending_flexy') {
                    paymentUrl = `/select/flexy?order_id=${encodeURIComponent(encryptedOrderId)}`;
                } else if (order.status === 'pending_bmccp' || (order.status === 'pending' && ['bmccp', 'baridimob'].includes(order.payment_method))) {
                    paymentUrl = `/select/bmccp/${encodeURIComponent(encryptedOrderId)}`;
                } else if (order.status === 'pending_cryptopay') {
                    paymentUrl = `/select/crypto/${encodeURIComponent(encryptedOrderId)}`;
                }

                const continuePaymentBtn = paymentUrl
                    ? `<a href="${paymentUrl}" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 rounded-xl bg-violet-700 hover:bg-violet-800 text-white text-sm font-bold shadow-sm">${esc(t.continuePayment)}</a>`
                    : '';

                let deliveryHtml = '';
                if (isVipOrder || gameType === 'vipreseller') {
                    if (order.status === 'sending') {
                        deliveryHtml = `
                            <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-3 text-sm text-sky-900">
                                <p class="font-bold mb-0.5">${esc(t.processing)}</p>
                                <p>${esc(t.processingHint)}</p>
                            </div>`;
                    } else if (order.status === 'completed') {
                        const profile = order.delivery_profile || (orderItems.find((i) => i.delivery_profile)?.delivery_profile) || null;
                        const link = order.delivery_link || (orderItems.find((i) => i.delivery_link)?.delivery_link) || null;
                        const note = order.delivery_note || (orderItems.find((i) => i.delivery_note)?.delivery_note) || null;
                        if (profile || link || note) {
                            const safeLink = link ? esc(link) : '';
                            deliveryHtml = `
                                <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900 space-y-2">
                                    <p class="font-bold">${esc(t.deliveryReady)}</p>
                                    ${profile ? `<p><span class="font-semibold">${esc(t.profile)}:</span> ${esc(profile)}</p>` : ''}
                                    ${link ? `
                                        <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                                            <a href="${safeLink}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                                                ${esc(t.openLink)}
                                            </a>
                                            <button type="button"
                                                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-emerald-300 bg-white hover:bg-emerald-100 text-emerald-800 text-xs font-bold"
                                                    onclick="navigator.clipboard.writeText(${JSON.stringify(String(link))}).then(() => this.textContent = ${JSON.stringify(t.copied)}).catch(() => {})">
                                                ${esc(t.copyLink)}
                                            </button>
                                        </div>
                                    ` : (note ? `<p class="break-all text-xs">${esc(note)}</p>` : '')}
                                </div>`;
                        }
                    }
                }

                const amountDzd = parseFloat(order.amount_dzd ?? order.amount ?? 0) || 0;
                const amountUsd = parseFloat(order.amount_usd ?? 0) || 0;
                const isFlexyOrder = order.status === 'pending_flexy' || order.status === 'pending_confirmation';
                let priceDisplay = '';
                if (isFlexyOrder) {
                    priceDisplay = `${Math.round(amountDzd + 50).toLocaleString()} DZD`;
                } else {
                    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
                    priceDisplay = currency === 'DZD'
                        ? `${Math.round(amountDzd).toLocaleString()} DZD`
                        : `$${(amountUsd > 0 ? amountUsd : amountDzd).toFixed(2)} USD`;
                }

                const createdAt = order.created_at
                    ? new Date(order.created_at).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
                    : '';

                const destinationMeta = buildDestinationMeta(order, gameType, isVipOrder);
                const paymentMeta = metaItem(t.payment, paymentLabel(order.payment_method));

                return `
                    <article class="dz-order-card">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <h2 class="text-base sm:text-lg font-black text-slate-900">
                                        ${esc(t.orderNumber.replace(':number', order.order_number || order.id || ''))}
                                    </h2>
                                    <span class="dz-status ${statusClass(order.status)}">${esc(statusLabel)}</span>
                                </div>
                                <div class="space-y-0.5 mb-1">${packLinesHtml}</div>
                                <p class="text-sm font-semibold text-slate-600">${esc(t.game)}: <span class="text-slate-900">${esc(gameName)}</span></p>
                            </div>
                            <div class="sm:text-right shrink-0">
                                <p class="text-xl sm:text-2xl font-black text-violet-700 order-price-display"
                                   data-order-id="${esc(order.id)}"
                                   data-status="${esc(order.status)}"
                                   data-amount-dzd="${amountDzd}"
                                   data-amount-usd="${amountUsd}"
                                   data-is-flexy="${isFlexyOrder}">${esc(priceDisplay)}</p>
                            </div>
                        </div>

                        <div class="dz-meta-grid mt-3">
                            ${destinationMeta}
                            ${paymentMeta}
                        </div>

                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 pt-3">
                            <p class="text-xs sm:text-sm text-slate-500">${esc(t.created)}: ${esc(createdAt)}</p>
                            ${continuePaymentBtn}
                        </div>
                        ${deliveryHtml}
                    </article>
                `;
            }).join('');

            setTimeout(() => {
                updateDashboardPrices();
                pollVipProcessingOrders(allOrders);
            }, 0);
        }

        let vipPollTimer = null;
        async function refreshDashboardOrders() {
            try {
                const rows = await loadOrders();
                diaszoneRenderDashboardOrders(rows);
            } catch (e) {
                console.error('Could not refresh orders', e);
            }
        }

        function pollVipProcessingOrders(allOrders) {
            if (vipPollTimer) {
                clearInterval(vipPollTimer);
                vipPollTimer = null;
            }
            const processing = (allOrders || []).filter((r) => {
                const o = r.order || {};
                return o.status === 'sending' && (o.is_vipreseller || o.game_type === 'vipreseller');
            });
            if (processing.length === 0) return;

            vipPollTimer = setInterval(async () => {
                let changed = false;
                for (const result of processing) {
                    try {
                        const res = await fetch(`/api/orders/${result.order.id}/status`, {
                            headers: { Accept: 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) continue;
                        const data = await res.json();
                        if (data.status && data.status !== result.order.status) {
                            changed = true;
                            break;
                        }
                    } catch (e) {}
                }
                if (changed) {
                    clearInterval(vipPollTimer);
                    vipPollTimer = null;
                    await refreshDashboardOrders();
                }
            }, 30000);
        }

        function updateDashboardPrices() {
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            document.querySelectorAll('.order-price-display').forEach((element) => {
                if (element.getAttribute('data-is-flexy') === 'true') return;
                const amountDzd = parseFloat(element.getAttribute('data-amount-dzd')) || 0;
                const amountUsd = parseFloat(element.getAttribute('data-amount-usd')) || 0;
                element.textContent = currency === 'DZD'
                    ? `${Math.round(amountDzd).toLocaleString()} DZD`
                    : `$${(amountUsd > 0 ? amountUsd : amountDzd).toFixed(2)} USD`;
            });
        }

        ordersContainer.classList.remove('hidden');
        renderOrders(filtered);

        window.addEventListener('currencyChanged', function() {
            updateDashboardPrices();
        });
    }

    function readGuestEncryptedIds() {
        const ids = new Set();
        try {
            const multi = JSON.parse(localStorage.getItem('diaszone_encrypted_order_ids') || '[]');
            if (Array.isArray(multi)) multi.forEach((id) => { if (id) ids.add(String(id)); });
        } catch (e) {}
        const single = localStorage.getItem('diaszone_encrypted_order_id');
        if (single) ids.add(String(single));
        return Array.from(ids).slice(0, 30);
    }

    async function loadGuestOrders() {
        const encryptedIds = readGuestEncryptedIds();
        if (encryptedIds.length === 0) return [];

        const rows = await Promise.all(encryptedIds.map(async (encryptedId) => {
            try {
                const res = await fetch(byEncryptedUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ encrypted_order_id: encryptedId }),
                });
                if (!res.ok) return null;
                const data = await res.json();
                if (!data.success || !data.order) return null;
                return { order: data.order, encryptedId };
            } catch (e) {
                return null;
            }
        }));

        return rows.filter(Boolean);
    }

    async function loadOrders() {
        if (dashboardUserAuthenticated) {
            const res = await fetch(mineUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.success && Array.isArray(data.orders) && data.orders.length > 0) {
                return data.orders.map((row) => ({
                    order: row.order,
                    encryptedId: row.encrypted_id,
                }));
            }
            // Fallback: guest-style localStorage orders (e.g. just logged in, older carts)
            const guestRows = await loadGuestOrders();
            return guestRows;
        }
        return loadGuestOrders();
    }

    try {
        const rows = await loadOrders();
        diaszoneRenderDashboardOrders(rows);
    } catch (e) {
        console.error('Could not load orders', e);
        diaszoneRenderDashboardOrders([]);
    }
});
</script>
@endpush
@endif
@endsection
