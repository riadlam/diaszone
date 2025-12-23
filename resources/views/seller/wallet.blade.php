@extends('layouts.seller')

@section('title', __('seller.wallet_title'))
@section('header', __('seller.wallet'))

@section('content')
<!-- Page container -->
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Balance + Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 items-start">
        <div class="md:col-span-2 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-6 w-full">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-blue-100 text-sm">{{ __('seller.current_balance') }}</p>
                    <p class="text-4xl font-extrabold text-white tracking-tight">{{ number_format($seller->wallet_balance, 0, '.', '') }}<span class="text-base font-medium text-blue-100"> DZD</span></p>
                    @if(isset($pendingTopupsSum) && $pendingTopupsSum > 0)
                        <p class="text-xs text-slate-200 mt-1">{{ __('seller.pending_topup_requests_info', ['amount' => number_format($pendingTopupsSum, 0, '.', '')]) }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <button id="request-topup-btn" aria-controls="topup-section" aria-expanded="false" class="request-topup-toggle inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold shadow">{{ __('seller.request_topup') }}</button>
                </div>
            </div>
        </div>

            <div class="bg-white/5 border border-white/5 p-4 rounded-lg text-sm text-blue-100">
            <strong class="block mb-1">ℹ️ {{ __('seller.how_it_works') }}</strong>
            <p class="text-xs text-blue-200">{{ __('seller.contact_admin_topup_notice') }}</p>
        </div>
    </div>

<!-- Pending Top-up Requests -->
@if(isset($pendingTopups) && $pendingTopups->count() > 0)
    <div class="mb-6">
        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <!-- Header -->
            <div class="bg-gradient-to-r from-amber-600/20 to-orange-600/20 border-b border-amber-500/20 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-amber-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white">{{ __('seller.pending_topup_requests') }}</h4>
                            <p class="text-xs text-amber-200/70">{{ __('seller.requests_awaiting_approval', ['count' => $pendingTopups->count()]) }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-amber-500/20 text-amber-300 text-sm font-semibold rounded-full">
                        {{ number_format($pendingTopupsSum ?? $pendingTopups->sum('amount'), 0, '.', '') }} DZD
                    </span>
                </div>
            </div>
            
            <!-- Requests Table - hidden on small screens -->
            <div class="overflow-x-auto">
                <table class="w-full hidden md:table">
                    <thead class="bg-slate-700/30">
                        <tr class="text-left text-xs text-gray-400 uppercase tracking-wider">
                            <th class="px-5 py-3 font-semibold">{{ __('seller.table_request') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('seller.table_amount') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('seller.table_method') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('seller.table_date') }}</th>
                            <th class="px-5 py-3 font-semibold">{{ __('seller.table_status') }}</th>
                            <th class="px-5 py-3 font-semibold text-right">{{ __('seller.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($pendingTopups as $tr)
                            <tr class="hover:bg-slate-700/20 transition-colors">
                                <td class="px-5 py-4">
                                    <span class="text-white font-medium">#{{ $tr->id }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-lg font-bold text-white">{{ number_format($tr->amount, 0, '.', '') }}</span>
                                    <span class="text-gray-400 text-sm ml-1">{{ $tr->currency }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($tr->payment_type)
                                        @php
                                            $badgeColors = [
                                                'ccp' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                                'crypto' => 'bg-green-500/20 text-green-300 border-green-500/30',
                                                'baridimob' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                            ];
                                            $badgeColor = $badgeColors[$tr->payment_type] ?? 'bg-slate-500/20 text-slate-300 border-slate-500/30';
                                        @endphp
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full border {{ $badgeColor }}">
                                            {{ strtoupper($tr->payment_type) }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-300">{{ $tr->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $tr->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-semibold rounded-full">
                                        <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                                        {{ ucfirst($tr->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" aria-label="{{ __('seller.view_request', ['id' => $tr->id]) }}" 
                                        onclick="showRequestDetails({{ json_encode([
                                            'id' => $tr->id,
                                            'amount' => number_format($tr->amount, 0, '.', ''),
                                            'currency' => $tr->currency,
                                            'payment_type' => $tr->payment_type,
                                            'status' => $tr->status,
                                            'seller_note' => $tr->seller_note,
                                            'receipt' => $tr->receipt,
                                            'created_at' => $tr->created_at->format('M d, Y \a\t H:i'),
                                        ]) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-medium rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ __('seller.view') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View for Pending Topups -->
            <div class="md:hidden space-y-3 px-3 py-2">
                @foreach($pendingTopups as $tr)
                    <div class="bg-slate-700/20 rounded-lg p-3 flex items-start justify-between" data-topup-id="{{ $tr->id }}">
                        <div>
                            <p class="text-white font-medium">#{{ $tr->id }}</p>
                            <p class="text-sm text-gray-300">{{ $tr->created_at->format('M d, Y - H:i') }}</p>
                            @if($tr->payment_type)
                                <span class="inline-block mt-2 px-2 py-0.5 text-xs rounded bg-slate-600 text-gray-300">{{ strtoupper($tr->payment_type) }}</span>
                            @endif
                        </div>
                        <div class="text-right flex flex-col items-end justify-between">
                            <div>
                                <p class="text-lg font-bold text-white">{{ number_format($tr->amount, 0, '.', '') }} <span class="text-gray-400 text-xs">{{ $tr->currency }}</span></p>
                            </div>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-semibold rounded-full">
                                    <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                                    {{ ucfirst($tr->status) }}
                                </span>
                                <div class="flex items-center gap-2">
                                <button type="button" aria-label="{{ __('seller.view_request', ['id' => $tr->id]) }}" onclick="showRequestDetails({{ json_encode([
                                    'id' => $tr->id,
                                    'amount' => number_format($tr->amount, 0, '.', ''),
                                    'currency' => $tr->currency,
                                    'payment_type' => $tr->payment_type,
                                    'status' => $tr->status,
                                    'seller_note' => $tr->seller_note,
                                    'receipt' => $tr->receipt,
                                    'created_at' => $tr->created_at->format('M d, Y \a\t H:i'),
                                ]) }})" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-medium rounded-lg transition">{{ __('seller.view') }}</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    </div>
    
    <!-- Request Details Modal -->
    <div id="request-details-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeRequestDetails()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl w-full max-w-md shadow-2xl border border-slate-700 overflow-hidden max-h-[90vh] flex flex-col my-8">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">{{ __('seller.request_details') }}</h3>
                        <button type="button" onclick="closeRequestDetails()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                    </div>
                </div>
                
                <!-- Modal Content -->
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <!-- Request ID & Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-sm">{{ __('seller.request') }} <span id="detail-id" class="text-white font-semibold">#1</span></span>
                        <span id="detail-status" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                            Pending
                        </span>
                    </div>
                    
                    <!-- Amount -->
                    <div class="bg-slate-700/30 rounded-xl p-4 text-center">
                                <p class="text-gray-400 text-sm mb-1">{{ __('seller.table_amount') }}</p>
                        <p class="text-3xl font-bold text-white"><span id="detail-amount">500</span> <span id="detail-currency" class="text-lg text-gray-400">DZD</span></p>
                    </div>
                    
                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-700/20 rounded-lg p-3">
                            <p class="text-gray-500 text-xs mb-1">{{ __('checkout.payment_method') }}</p>
                            <p id="detail-method" class="text-white font-medium">{{ __('seller.ccp') }}</p>
                        </div>
                        <div class="bg-slate-700/20 rounded-lg p-3">
                            <p class="text-gray-500 text-xs mb-1">{{ __('seller.submitted') }}</p>
                            <p id="detail-date" class="text-white font-medium text-sm">Dec 08, 2024</p>
                        </div>
                    </div>
                    
                    <!-- Note -->
                    <div id="detail-note-section" class="hidden">
                            <p class="text-gray-500 text-xs mb-2">{{ __('seller.your_note') }}</p>
                        <div class="bg-slate-700/20 rounded-lg p-3">
                            <p id="detail-note" class="text-gray-300 text-sm"></p>
                        </div>
                    </div>
                    
                    <!-- Receipt -->
                    <div id="detail-receipt-section" class="hidden">
                        <p class="text-gray-500 text-xs mb-2">{{ __('seller.receipt') }}</p>
                        <a id="detail-receipt-link" href="#" target="_blank" class="block">
                            <div id="detail-receipt-image" class="rounded-lg overflow-hidden bg-slate-700 max-h-48 flex items-center justify-center">
                                <!-- Image will be inserted here -->
                            </div>
                        </a>
                        <a id="detail-receipt-open" href="#" target="_blank" class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300 text-sm mt-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            {{ __('seller.open_in_new_tab') }}
                        </a>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-800/50 border-t border-slate-700">
                    <button type="button" onclick="closeRequestDetails()" class="w-full px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-xl transition">
                        {{ __('common.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Duplicate pending top-ups block removed (receipt-aware block above is used) --}} 

<!-- Transactions -->
<div class="bg-slate-800 rounded-xl shadow-inner overflow-hidden">
    <div class="p-6 border-b border-slate-700 flex items-center justify-between">
        <div>
                            <h3 class="text-lg font-bold">{{ __('seller.transaction_history') }}</h3>
            <p class="text-sm text-gray-400 mt-1">{{ __('seller.recent_wallet_activity') }}</p>
        </div>
        <div>
            <button class="request-topup-toggle inline-flex items-center gap-2 px-3 py-1 rounded bg-amber-500 text-sm text-white hover:bg-amber-600">{{ __('seller.request_topup') }}</button>
        </div>
    </div>

    <!-- Request Top-up Section (Inline Collapsible) -->
            <div id="topup-section" aria-hidden="true" class="hidden mb-6 animate-slideDown">
        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <!-- Header -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ __('seller.request_topup') }}</h3>
                            <p class="text-amber-100 text-xs">{{ __('seller.add_funds_to_wallet') }}</p>
                        </div>
                    </div>
                    <button type="button" id="topup-close" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <form id="topup-form" method="POST" action="{{ route('seller.topup.request') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-5">
                            <!-- Payment Type Selection -->
                            <div>
                                <label class="block text-gray-300 font-semibold mb-3 flex items-center gap-2">
                                    <span>💳</span> {{ __('checkout.payment_method') }}
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <!-- CCP Option -->
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="payment_type" value="ccp" class="hidden peer" checked>
                                        <div class="p-3 rounded-xl border-2 border-slate-600 bg-slate-700/50 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 transition-all text-center hover:border-slate-500">
                                            <div class="w-9 h-9 mx-auto mb-1.5 bg-slate-600 rounded-lg flex items-center justify-center group-hover:bg-slate-500 transition">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            <span class="text-xs font-medium text-gray-300">{{ __('seller.ccp') }}</span>
                                        </div>
                                    </label>
                                    
                                    <!-- USDT Option -->
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="payment_type" value="crypto" class="hidden peer">
                                        <div class="p-3 rounded-xl border-2 border-slate-600 bg-slate-700/50 peer-checked:border-green-500 peer-checked:bg-green-500/10 transition-all text-center hover:border-slate-500">
                                            <div class="w-9 h-9 mx-auto mb-1.5 bg-slate-600 rounded-lg flex items-center justify-center group-hover:bg-slate-500 transition">
                                                <span class="text-base">₮</span>
                                            </div>
                                            <span class="text-xs font-medium text-gray-300">{{ __('seller.usdt') }}</span>
                                        </div>
                                    </label>
                                    
                                    <!-- Baridimob Option -->
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="payment_type" value="baridimob" class="hidden peer">
                                        <div class="p-3 rounded-xl border-2 border-slate-600 bg-slate-700/50 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition-all text-center hover:border-slate-500">
                                            <div class="w-9 h-9 mx-auto mb-1.5 bg-slate-600 rounded-lg flex items-center justify-center group-hover:bg-slate-500 transition">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <span class="text-xs font-medium text-gray-300">{{ __('seller.baridimob') }}</span>
                                        </div>
                                    </label>
                                </div>
                                
                                <!-- Payment Destination Info Boxes -->
                                <!-- CCP Info -->
                                <div id="payment-info-ccp" class="mt-4 p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl">
                                    <p class="text-amber-300 text-xs font-medium mb-2 flex items-center gap-1">
                                        <span>📬</span> Send to CCP Account:
                                    </p>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div>
                                            <p class="text-white font-bold text-lg font-mono tracking-wider">023709454</p>
                                            <p class="text-gray-400 text-xs">Clé: <span class="text-white font-semibold">04</span></p>
                                        </div>
                                        <button type="button" onclick="copyToClipboard('023709454', this)" class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs rounded-lg transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            {{ __('seller.copy') }}
                                        </button>
                                    </div>
                                    <p class="text-gray-300 text-sm">{{ __('seller.name_label') }}: <span class="font-semibold">Riad Mohamed Laamari</span></p>
                                </div>
                                
                                <!-- Baridimob (RIP) Info -->
                                <div id="payment-info-baridimob" class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-xl hidden">
                                    <p class="text-blue-300 text-xs font-medium mb-2 flex items-center gap-1">
                                        <span>📱</span> Send to RIP Number:
                                    </p>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-white font-bold text-lg font-mono tracking-wider">00799999002370945404</p>
                                        <button type="button" onclick="copyToClipboard('00799999002370945404', this)" class="px-3 py-1.5 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 text-xs rounded-lg transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            {{ __('seller.copy') }}
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- USDT (Binance) Info -->
                                <div id="payment-info-crypto" class="mt-4 p-4 bg-green-500/10 border border-green-500/30 rounded-xl hidden">
                                    <p class="text-green-300 text-xs font-medium mb-2 flex items-center gap-1">
                                        <span>₮</span> Send USDT to Binance ID:
                                    </p>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-white font-bold text-xl font-mono tracking-wider">455432403</p>
                                        <button type="button" onclick="copyToClipboard('455432403', this)" class="px-3 py-1.5 bg-green-500/20 hover:bg-green-500/30 text-green-300 text-xs rounded-lg transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            {{ __('seller.copy') }}
                                        </button>
                                    </div>
                                    <p class="text-gray-400 text-xs mt-2">Network: TRC20 or BEP20</p>
                                </div>
                            </div>
                            
                            <!-- Amount -->
                            <div>
                                <label class="block text-gray-300 font-semibold mb-2 flex items-center gap-2">
                                    <span>💰</span> {{ __('seller.amount_label') }} (<span id="amount-currency">{{ __('seller.currency_dzd') }}</span>)
                                </label>
                                <input id="topup-amount" name="amount" type="number" min="1" step="1" required placeholder="{{ __('seller.enter_amount') }}" class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white text-lg font-semibold placeholder-gray-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition">
                                <p id="topup-amount-hint" class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('seller.current_balance_label') }} <span id="balance-amount" class="font-semibold text-gray-300">{{ number_format($seller->wallet_balance, 0, '.', '') }}</span> <span id="balance-currency">DZD</span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-5">
                            <!-- Note -->
                            <div>
                                <label class="block text-gray-300 font-semibold mb-2 flex items-center gap-2">
                                    <span>📝</span> Note <span class="text-gray-500 text-sm font-normal">(optional)</span>
                                </label>
                                <textarea name="seller_note" rows="2" placeholder="Add any reference or details..." class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition resize-none"></textarea>
                            </div>
                            
                            <!-- Receipt Upload -->
                            <div>
                                <label class="block text-gray-300 font-semibold mb-2 flex items-center gap-2">
                                    <span>📸</span> {{ __('seller.receipt') }} <span class="text-gray-500 text-sm font-normal">({{ __('common.optional') }})</span>
                                </label>
                                <div id="receipt-dropzone" class="border-2 border-dashed border-slate-600 rounded-xl p-4 bg-slate-700/30 cursor-pointer hover:border-amber-500/50 hover:bg-slate-700/50 transition-all group">
                                    <input id="receipt-input" name="receipt" type="file" accept="image/*,application/pdf" class="hidden" />
                                    
                                    <!-- Placeholder -->
                                    <div id="receipt-placeholder" class="flex flex-col items-center justify-center text-center py-2">
                                        <div class="w-10 h-10 bg-slate-600 rounded-full flex items-center justify-center mb-2 group-hover:bg-amber-500/20 transition">
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-300 text-sm font-medium">{{ __('uploader.click_to_upload_or_drag') }}</p>
                                        <p class="text-gray-500 text-xs mt-1">JPG, PNG, PDF — max 10MB</p>
                                    </div>
                                    
                                    <!-- Preview -->
                                    <div id="receipt-preview" class="hidden">
                                        <div class="flex items-center gap-4">
                                            <div id="receipt-thumb" class="w-14 h-14 rounded-lg bg-slate-600 flex items-center justify-center overflow-hidden flex-shrink-0"></div>
                                            <div class="flex-1 min-w-0">
                                                <p id="receipt-filename" class="text-sm text-gray-200 font-medium truncate"></p>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <button type="button" id="receipt-remove" class="text-xs text-red-400 hover:text-red-300 transition">{{ __('seller.remove') }}</button>
                                                    <a id="receipt-view-link" href="#" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 transition">{{ __('seller.open') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error Message -->
                    <div id="topup-error" class="hidden mt-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-sm text-red-400" role="alert" aria-live="assertive"></div>
                    
                    <!-- Submit Buttons -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="topup-cancel" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-medium transition">
                            {{ __('seller.cancel') }}
                        </button>
                        <button id="topup-submit" type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-bold transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            {{ __('seller.submit_request') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="divide-y divide-slate-700">
        @forelse($transactions as $transaction)
            <div class="p-4 flex flex-col md:flex-row items-start md:items-center justify-between hover:bg-slate-700/50">
                <div class="flex items-start md:items-center space-x-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $transaction->type === 'credit' ? 'bg-green-500/20' : 'bg-red-500/20' }}">
                        @if($transaction->type === 'credit')
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-white">{{ $transaction->description }}</p>
                        <p class="text-gray-500 text-sm">{{ $transaction->created_at->format('M d, Y - H:i') }}</p>
                        @if($transaction->reference_type)
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-slate-600 text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $transaction->reference_type)) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="text-right mt-3 md:mt-0">
                    <p class="font-bold {{ $transaction->type === 'credit' ? 'text-green-400' : 'text-red-400' }}">
                        {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2, '.', '') }} DZD
                    </p>
                    <p class="text-gray-500 text-sm">Balance: {{ number_format($transaction->balance_after, 2, '.', '') }} DZD</p>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-gray-400 min-h-[200px] flex flex-col items-center justify-center">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <p class="text-lg font-semibold text-gray-300">{{ __('seller.no_transactions_yet') }}</p>
                <p class="text-sm text-gray-400 mt-2">{{ __('seller.transactions_empty_info') }}</p>
            </div>
        @endforelse
    </div>
    
    @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-700">
            {{ $transactions->links() }}
        </div>
    @endif
</div> {{-- /.max-w-6xl container --}}
</div>
@endsection

@push('scripts')
<script>
const tCopied = {!! json_encode(__('seller.copied')) !!};
    // Inline section toggle
    const topupSection = document.getElementById('topup-section');
    const requestTopupBtns = document.querySelectorAll('.request-topup-toggle');
    const topupCancelBtn = document.getElementById('topup-cancel');
    const topupCloseBtn = document.getElementById('topup-close');

    function openTopupSection() {
        if (!topupSection) return;
        topupSection.classList.remove('hidden');
        topupSection.setAttribute('aria-hidden', 'false');
        // Scroll to the section smoothly
        setTimeout(() => {
            topupSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('topup-amount')?.focus();
        }, 100);
        requestTopupBtns?.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
    }

    function closeTopupSection() {
        if (!topupSection) return;
        topupSection.classList.add('hidden');
        topupSection.setAttribute('aria-hidden', 'true');
        // Reset preview and errors
        resetReceipt();
        topupErrorEl?.classList.add('hidden');
        requestTopupBtns?.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    }

    requestTopupBtns?.forEach(btn => btn.addEventListener('click', openTopupSection));
    topupCancelBtn?.addEventListener('click', closeTopupSection);
    topupCloseBtn?.addEventListener('click', closeTopupSection);

    // Close with ESC key
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && topupSection && !topupSection.classList.contains('hidden')) {
            closeTopupSection();
        }
        // Also close request details modal
        if (e.key === 'Escape') {
            closeRequestDetails();
        }
    });

    // Request Details Modal
    const requestDetailsModal = document.getElementById('request-details-modal');
    
    function showRequestDetails(data) {
        if (!requestDetailsModal) return;
        
        // Populate modal with data
        document.getElementById('detail-id').textContent = '#' + data.id;
        document.getElementById('detail-amount').textContent = data.amount;
        document.getElementById('detail-currency').textContent = data.currency;
        document.getElementById('detail-method').textContent = data.payment_type ? data.payment_type.toUpperCase() : '—';
        document.getElementById('detail-date').textContent = data.created_at;
        
        // Status
        const statusEl = document.getElementById('detail-status');
        statusEl.innerHTML = `<span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span> ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}`;
        
        // Note
        const noteSection = document.getElementById('detail-note-section');
        const noteEl = document.getElementById('detail-note');
        if (data.seller_note) {
            noteSection.classList.remove('hidden');
            noteEl.textContent = data.seller_note;
        } else {
            noteSection.classList.add('hidden');
        }
        
        // Receipt
        const receiptSection = document.getElementById('detail-receipt-section');
        const receiptImage = document.getElementById('detail-receipt-image');
        const receiptLink = document.getElementById('detail-receipt-link');
        const receiptOpen = document.getElementById('detail-receipt-open');
        
        if (data.receipt) {
            receiptSection.classList.remove('hidden');
            receiptLink.href = data.receipt;
            receiptOpen.href = data.receipt;
            
            // Check if it's an image
            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(data.receipt);
            if (isImage) {
                receiptImage.innerHTML = `<img src="${data.receipt}" alt="Receipt" class="w-full h-full object-contain max-h-48">`;
            } else {
                receiptImage.innerHTML = `
                    <div class="p-6 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-400 text-sm">{{ __('seller.pdf_document') }}</p>
                    </div>
                `;
            }
        } else {
            receiptSection.classList.add('hidden');
        }
        
        // Show modal
        requestDetailsModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeRequestDetails() {
        if (!requestDetailsModal) return;
        requestDetailsModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    
    // Make functions globally available
    window.showRequestDetails = showRequestDetails;
    window.closeRequestDetails = closeRequestDetails;

    // Validate topup amount client-side (must be positive integer)
    const topupAmountEl = document.getElementById('topup-amount');
    const topupErrorEl = document.getElementById('topup-error');
    const topupSubmitBtn = document.getElementById('topup-submit');

    function validateTopup() {
        if (!topupAmountEl) return true;
        const val = parseInt(topupAmountEl.value || '0', 10) || 0;
        if (val <= 0) {
            topupErrorEl.classList.remove('hidden');
            topupErrorEl.textContent = {!! json_encode(__('seller.please_enter_valid_topup_amount')) !!};
            topupSubmitBtn.disabled = true;
            return false;
        }
        topupErrorEl.classList.add('hidden');
        topupErrorEl.textContent = '';
        topupSubmitBtn.disabled = false;
        return true;
    }

    topupAmountEl?.addEventListener('input', validateTopup);

    document.getElementById('topup-form')?.addEventListener('submit', function(e){
        if (!validateTopup()) {
            e.preventDefault();
        }
    });

    // Receipt dropzone + preview
    const dropzone = document.getElementById('receipt-dropzone');
    const fileInput = document.getElementById('receipt-input');
    const placeholder = document.getElementById('receipt-placeholder');
    const preview = document.getElementById('receipt-preview');
    const thumb = document.getElementById('receipt-thumb');
    const filenameEl = document.getElementById('receipt-filename');
    const removeBtn = document.getElementById('receipt-remove');
    const viewLink = document.getElementById('receipt-view-link');

    const MAX_SIZE = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    function resetReceipt() {
        if (fileInput) fileInput.value = '';
        if (preview) preview.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
        if (thumb) thumb.innerHTML = '';
        if (filenameEl) filenameEl.textContent = '';
        if (viewLink) viewLink.href = '#';
    }

    function showFilePreview(file) {
        if (!file) return resetReceipt();
        if (placeholder) placeholder.classList.add('hidden');
        if (preview) preview.classList.remove('hidden');
        if (filenameEl) filenameEl.textContent = file.name;

        if (file.type === 'application/pdf') {
            thumb.innerHTML = "<svg class='w-12 h-12 text-gray-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M12 2v20M4 6h16M4 18h16'/></svg>";
        } else if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'object-cover w-full h-full';
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
            thumb.innerHTML = '';
            thumb.appendChild(img);
        } else {
            thumb.innerHTML = `<div class="text-xs text-gray-400">${{!! json_encode(__('seller.unsupported')) !!}}</div>`;
        }

        viewLink.href = URL.createObjectURL(file);
    }

    dropzone?.addEventListener('click', () => fileInput?.click());

    dropzone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-blue-400');
    });

    dropzone?.addEventListener('dragleave', (e) => {
        dropzone.classList.remove('border-blue-400');
    });

    dropzone?.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-blue-400');
        const f = e.dataTransfer?.files?.[0];
        if (f && fileInput) {
            const dt = new DataTransfer();
            dt.items.add(f);
            fileInput.files = dt.files;
            handleFileChange(f);
        }
    });

    fileInput?.addEventListener('change', (e) => {
        const f = fileInput.files[0];
        handleFileChange(f);
    });

    function handleFileChange(file) {
        if (!file) return resetReceipt();
        // validate
        if (!allowedTypes.includes(file.type)) {
            topupErrorEl.classList.remove('hidden');
            topupErrorEl.textContent = {!! json_encode(__('seller.unsupported_receipt_file_type')) !!};
            if (fileInput) fileInput.value = '';
            return;
        }
        if (file.size > MAX_SIZE) {
            topupErrorEl.classList.remove('hidden');
            topupErrorEl.textContent = {!! json_encode(__('seller.receipt_file_too_large')) !!};
            if (fileInput) fileInput.value = '';
            return;
        }

        topupErrorEl.classList.add('hidden');
        topupErrorEl.textContent = '';
        showFilePreview(file);
    }

    removeBtn?.addEventListener('click', function(){
        resetReceipt();
    });

    // Reset preview when user cancels the modal (or dialog is closed)
    topupCancelBtn?.addEventListener('click', resetReceipt);
    topupCloseBtn?.addEventListener('click', resetReceipt);

    // Payment mini-menu behaviour
    const paymentRadios = document.querySelectorAll('input[name="payment_type"]');
    const amountCurrency = document.getElementById('amount-currency');
    const balanceCurrency = document.getElementById('balance-currency');
    
    // Payment info boxes
    const paymentInfoCcp = document.getElementById('payment-info-ccp');
    const paymentInfoBaridimob = document.getElementById('payment-info-baridimob');
    const paymentInfoCrypto = document.getElementById('payment-info-crypto');

    function updatePaymentInfo(val) {
        // Hide all payment info boxes
        paymentInfoCcp?.classList.add('hidden');
        paymentInfoBaridimob?.classList.add('hidden');
        paymentInfoCrypto?.classList.add('hidden');
        
        // Show the relevant one
        if (val === 'ccp') {
            paymentInfoCcp?.classList.remove('hidden');
        } else if (val === 'baridimob') {
            paymentInfoBaridimob?.classList.remove('hidden');
        } else if (val === 'crypto') {
            paymentInfoCrypto?.classList.remove('hidden');
        }
    }

    paymentRadios.forEach(r => r.addEventListener('change', (e) => {
        const val = e.target.value;

        // Update payment info boxes
        updatePaymentInfo(val);

        // change currency display when selecting crypto (USDT)
        if (val === 'crypto') {
            if (amountCurrency) amountCurrency.textContent = 'USD';
            if (balanceCurrency) balanceCurrency.textContent = 'USD';
            // hide current balance hint when paying with crypto (USDT)
            document.getElementById('topup-amount-hint')?.classList.add('hidden');
        } else {
            if (amountCurrency) amountCurrency.textContent = 'DZD';
            if (balanceCurrency) balanceCurrency.textContent = 'DZD';
            document.getElementById('topup-amount-hint')?.classList.remove('hidden');
        }
    }));

    // initialize visibility and currency for payment_type
    const initial = document.querySelector('input[name="payment_type"]:checked')?.value || 'ccp';
    // reflect correct currency initial state
    if (initial === 'crypto') {
        if (amountCurrency) amountCurrency.textContent = 'USD';
        if (balanceCurrency) balanceCurrency.textContent = 'USD';
        document.getElementById('topup-amount-hint')?.classList.add('hidden');
    }
    // Initialize payment info box visibility
    updatePaymentInfo(initial);

    // Copy to clipboard function with visual feedback
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                ${tCopied}
            `;
            btn.classList.add('bg-green-500/30');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('bg-green-500/30');
            }, 1500);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
    
    // Make copyToClipboard available globally
    window.copyToClipboard = copyToClipboard;
</script>
@endpush
