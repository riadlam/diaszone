@extends('layouts.app')

@section('title', __('Order Successful') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-green-50 via-emerald-50/30 to-teal-50/20 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-green-100 overflow-hidden">
                <!-- Success Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-8 text-center">
                    <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">{{ __('Order Completed!') }}</h1>
                    <p class="text-green-100 text-sm">{{ __('Your diamonds have been sent successfully') }}</p>
                </div>

                <!-- Order Details -->
                <div class="p-6 space-y-4">
                    <!-- Order Number -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 font-medium">{{ __('Order Number') }}</span>
                            <span class="text-sm font-bold text-gray-900">#{{ $order->order_number }}</span>
                        </div>
                    </div>

                    <!-- Game Info -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
                        <div class="flex items-center gap-3">
                            @php
                                $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                                $gameName = 'Mobile Legends';
                                $gameIcon = '💎';
                                $currencyName = 'Diamonds';
                                
                                if ($gameType === 'freefire') {
                                    $gameName = 'Free Fire';
                                    $gameIcon = '🔥';
                                } elseif ($gameType === 'pubgmobile') {
                                    $gameName = 'PUBG Mobile';
                                    $gameIcon = '🎮';
                                    $currencyName = 'UC';
                                } elseif ($gameType === 'honorofkings') {
                                    $gameName = 'Honor of Kings';
                                    $gameIcon = '👑';
                                    $currencyName = 'Tokens';
                                } elseif ($gameType === 'bloodstrike') {
                                    $gameName = 'Blood Strike';
                                    $gameIcon = '⚔️';
                                    $currencyName = 'Golds';
                                }
                            @endphp
                            <div class="text-3xl">{{ $gameIcon }}</div>
                            <div>
                                <p class="text-sm text-gray-600">{{ $gameName }}</p>
                                <p class="text-lg font-bold text-purple-600">
                                    {{ number_format($order->diamondPack->diamonds) }} {{ $currencyName }}
                                    @if($order->diamondPack->bonus > 0)
                                        <span class="text-green-600 text-sm">+{{ $order->diamondPack->bonus }} Bonus</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Player Info -->
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">{{ __('Delivered To') }}</h3>
                        @if($order->user_id_ml && $order->zone_id_ml)
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">User ID:</span> {{ $order->user_id_ml }}<br>
                                <span class="font-medium">Zone ID:</span> {{ $order->zone_id_ml }}
                            </p>
                        @elseif($order->player_id_ff)
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">Player ID:</span> {{ $order->player_id_ff }}
                            </p>
                        @elseif($order->player_id_pubg)
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">Player ID:</span> {{ $order->player_id_pubg }}
                            </p>
                        @elseif($order->player_id_hok)
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">Player ID:</span> {{ $order->player_id_hok }}
                            </p>
                        @elseif($order->user_id_bs)
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">User ID:</span> {{ $order->user_id_bs }}<br>
                                <span class="font-medium">Server:</span> {{ $order->server_bs }}
                            </p>
                        @endif
                    </div>

                    <!-- Coupon Info (if applicable) -->
                    @if($order->coupon)
                    <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-green-700">{{ __('Coupon Applied') }}: {{ $order->coupon->code }}</p>
                                <p class="text-xs text-green-600">
                                    @if($order->coupon->discount_type === 'percentage')
                                        {{ $order->coupon->discount_value }}% {{ __('off') }}
                                    @else
                                        {{ number_format($order->coupon->discount_value) }} DZD {{ __('off') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Status Badge -->
                    <div class="flex items-center justify-center">
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            {{ __('Completed') }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 space-y-3">
                        <a href="{{ route('dashboard.orders') }}" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('View My Orders') }}
                        </a>
                        <a href="{{ route('home') }}" 
                           class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            {{ __('Back to Home') }}
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-500">
                        {{ __('Thank you for choosing DiasZone!') }} 💜
                    </p>
                </div>
            </div>

            <!-- Support Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    {{ __('Need help?') }} 
                    <a href="{{ route('contact') }}" class="text-purple-600 hover:text-purple-700 font-semibold underline">{{ __('Contact Support') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Clear cart from localStorage after successful order
    localStorage.removeItem('diaszone_cart');
</script>
@endpush
@endsection
