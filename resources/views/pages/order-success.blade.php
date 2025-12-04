@extends('layouts.app')

@section('title', __('orders.order_successful') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-green-50 via-emerald-50/30 to-teal-50/20 min-h-screen py-6" dir="rtl">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-green-100 overflow-hidden">
                <!-- Success Header - More Compact -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-5 py-5 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="text-start">
                            <h1 class="text-xl font-bold text-white">{{ __('orders.order_completed') }}</h1>
                            <p class="text-green-100 text-xs">{{ __('orders.diamonds_sent_successfully') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Details - Compact -->
                <div class="p-4 space-y-3">
                    <!-- Order Number + Status Row -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">
                        <div>
                            <span class="text-xs text-gray-500">{{ __('orders.order') }}</span>
                            <p class="text-sm font-bold text-gray-900">#{{ $order->order_number }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                            {{ __('orders.completed') }}
                        </span>
                    </div>

                    <!-- Game Info - Compact -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-100">
                        <div class="flex items-center gap-2">
                            @php
                                $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                                $gameName = 'موبايل ليجندز';
                                $gameIcon = '💎';
                                $currencyName = 'ماسة';
                                
                                if ($gameType === 'freefire') {
                                    $gameName = 'فري فاير';
                                    $gameIcon = '🔥';
                                } elseif ($gameType === 'pubgmobile') {
                                    $gameName = 'ببجي موبايل';
                                    $gameIcon = '🎮';
                                    $currencyName = 'UC';
                                } elseif ($gameType === 'honorofkings') {
                                    $gameName = 'هونر أوف كينجز';
                                    $gameIcon = '👑';
                                    $currencyName = 'توكن';
                                } elseif ($gameType === 'bloodstrike') {
                                    $gameName = 'بلود سترايك';
                                    $gameIcon = '⚔️';
                                    $currencyName = 'ذهب';
                                }
                            @endphp
                            <div class="text-2xl">{{ $gameIcon }}</div>
                            <div>
                                <p class="text-xs text-gray-500">{{ $gameName }}</p>
                                <p class="text-base font-bold text-purple-600">
                                    {{ number_format($order->diamondPack->diamonds) }} {{ $currencyName }}
                                    @if($order->diamondPack->bonus > 0)
                                        <span class="text-green-600 text-xs">+{{ $order->diamondPack->bonus }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Player Info + Account Info - Compact 2 columns -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Game Account -->
                        <div class="bg-blue-50 rounded-lg p-2.5 border border-blue-100">
                            <h3 class="text-xs font-semibold text-blue-800 mb-1">{{ __('orders.game_account') }}</h3>
                            @if($order->user_id_ml && $order->zone_id_ml)
                                <p class="text-xs text-blue-700">
                                    <span class="font-medium">المعرف:</span> {{ $order->user_id_ml }}<br>
                                    <span class="font-medium">المنطقة:</span> {{ $order->zone_id_ml }}
                                </p>
                            @elseif($order->player_id_ff)
                                <p class="text-xs text-blue-700">
                                    <span class="font-medium">المعرف:</span> {{ $order->player_id_ff }}
                                </p>
                            @elseif($order->player_id_pubg)
                                <p class="text-xs text-blue-700">
                                    <span class="font-medium">المعرف:</span> {{ $order->player_id_pubg }}
                                </p>
                            @elseif($order->player_id_hok)
                                <p class="text-xs text-blue-700">
                                    <span class="font-medium">المعرف:</span> {{ $order->player_id_hok }}
                                </p>
                            @elseif($order->user_id_bs)
                                <p class="text-xs text-blue-700">
                                    <span class="font-medium">المعرف:</span> {{ $order->user_id_bs }}<br>
                                    <span class="font-medium">السيرفر:</span> {{ $order->server_bs }}
                                </p>
                            @endif
                        </div>
                        
                        <!-- User Account -->
                        <div class="bg-indigo-50 rounded-lg p-2.5 border border-indigo-100">
                            <h3 class="text-xs font-semibold text-indigo-800 mb-1">{{ __('orders.your_account') }}</h3>
                            <p class="text-xs text-indigo-700 truncate">
                                <span class="font-medium">{{ Auth::user()->name }}</span><br>
                                <span class="text-indigo-600">{{ Auth::user()->email }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Coupon Info (if applicable) - Compact -->
                    @if($order->coupon)
                    <div class="bg-green-50 rounded-lg p-2.5 border border-green-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-green-700">{{ $order->coupon->code }}
                                    <span class="font-normal text-green-600">
                                        (@if($order->coupon->discount_type === 'percentage'){{ $order->coupon->discount_value }}%@else{{ number_format($order->coupon->discount_value) }} دج@endif خصم)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions - Compact -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('dashboard.orders') }}" 
                           class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors shadow text-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('orders.my_orders') }}
                        </a>
                        <a href="{{ route('home') }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-lg transition-colors text-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            {{ __('orders.home') }}
                        </a>
                    </div>
                </div>

                <!-- Footer - Minimal -->
                <div class="bg-gray-50 px-4 py-2.5 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-500">
                        {{ __('orders.thank_you') }} 💜
                    </p>
                </div>
            </div>

            <!-- Screenshot Request - Compact -->
            <div class="mt-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg p-4 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                    </svg>
                    <span class="text-white font-bold">{{ __('orders.share_success') }}</span>
                </div>
                <p class="text-blue-100 text-xs mb-3">
                    📸 {{ __('orders.screenshot_request') }}
                </p>
                <a href="https://web.facebook.com/profile.php?id=61584183358240" 
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-white hover:bg-gray-100 text-blue-600 font-bold py-2 px-5 rounded-lg transition-colors shadow text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                    </svg>
                    {{ __('orders.send_to_facebook') }}
                </a>
            </div>

            <!-- Support Link - Compact -->
            <div class="mt-3 text-center">
                <p class="text-xs text-gray-600">
                    {{ __('orders.need_help') }} 
                    <a href="{{ route('contact') }}" class="text-purple-600 hover:text-purple-700 font-semibold underline">{{ __('orders.contact_support') }}</a>
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
