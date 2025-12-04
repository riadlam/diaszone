@extends('layouts.app')

@section('title', __('orders.order_successful') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-green-50 via-emerald-50/30 to-teal-50/20 min-h-screen py-8 md:py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-lg mx-auto">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-green-100 overflow-hidden">
                <!-- Success Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-8 text-center">
                    <div class="w-16 h-16 md:w-20 md:h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 md:w-10 md:h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-2">{{ __('orders.order_completed') }}</h1>
                    <p class="text-green-100 text-sm">{{ __('orders.diamonds_sent_successfully') }}</p>
                </div>

                <!-- Order Details -->
                <div class="p-4 md:p-6 space-y-4">
                    <!-- Order Number -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 font-medium">{{ __('orders.order') }}</span>
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

                    <!-- Your Account -->
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">{{ __('orders.your_account') }}</h3>
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">{{ Auth::user()->name }}</span><br>
                            <span class="text-blue-600 break-all">{{ Auth::user()->email }}</span>
                        </p>
                    </div>

                    <!-- Coupon Info (if applicable) -->
                    @if($order->coupon)
                    <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-green-700">Coupon Applied: {{ $order->coupon->code }}</p>
                                <p class="text-xs text-green-600">
                                    @if($order->coupon->discount_type === 'percentage')
                                        {{ $order->coupon->discount_value }}% off
                                    @else
                                        {{ number_format($order->coupon->discount_value) }} DZD off
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
                            {{ __('orders.completed') }}
                        </span>
                    </div>

                    <!-- Actions - Responsive -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <a href="{{ route('dashboard.orders') }}" 
                           class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            {{ __('orders.my_orders') }}
                        </a>
                        <a href="{{ route('home') }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            {{ __('orders.home') }}
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-500">
                        {{ __('orders.thank_you') }} 💜
                    </p>
                </div>
            </div>

            <!-- Screenshot Request -->
            <div class="mt-6 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg p-5 text-center">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                    </svg>
                    <span class="text-white font-bold text-lg">{{ __('orders.share_success') }}</span>
                </div>
                <p class="text-blue-100 text-sm mb-4">
                    📸 {{ __('orders.screenshot_request') }}
                </p>
                <a href="https://web.facebook.com/profile.php?id=61584183358240" 
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-white hover:bg-gray-100 text-blue-600 font-bold py-3 px-6 rounded-lg transition-colors shadow-md">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 008.44-9.9c0-5.53-4.5-10.02-10-10.02z"/>
                    </svg>
                    {{ __('orders.send_to_facebook') }}
                </a>
            </div>

            <!-- Support Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
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
