@extends('layouts.app')

@section('title', __('seller.payment_successful') . ' - DiasZone')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
        <!-- Success Animation -->
        <div class="mb-6">
            <div class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center animate-bounce-slow">
                <svg class="w-14 h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        
        <!-- Success Message -->
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('seller.payment_successful') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('payment.success') }}</p>
        
        <!-- Order Details -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">{{ __('checkout.order_number') }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
            </div>
            @if($order->diamondPack)
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-500">{{ __('checkout.product') }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ $order->diamondPack->name ?? $order->diamondPack->diamonds . ' ' . __('game.diamonds') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">{{ __('checkout.price') }}</span>
                <span class="text-sm font-semibold text-green-600">{{ number_format($order->diamondPack->price_dzd ?? 0, 0) }} DZD</span>
            </div>
            @endif
        </div>
        
        <!-- Status Info -->
        <div class="flex items-center justify-center gap-2 mb-6 text-sm text-amber-600 bg-amber-50 rounded-lg py-3 px-4">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('order.diamonds_sent_successfully') }}</span>
        </div>
        
        <!-- Redirect Notice -->
        <p class="text-sm text-gray-500 mb-4">
            {{ __('order.redirecting_to_orders_in_seconds', ['seconds' => 5]) }}
        </p>
        
        <!-- Manual Buttons -->
        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ route('dashboard.orders') }}" 
               class="inline-flex items-center justify-center w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-5 rounded-lg transition-colors">
                {{ __('order.view_my_orders') }}
            </a>
            <a href="{{ route('event.show', 'mobilelegends') }}"
               class="inline-flex items-center justify-center w-full bg-amber-500 hover:bg-amber-600 text-gray-950 font-semibold py-3 px-5 rounded-lg transition-colors">
                {{ __('checkout.spin_lucky_wheel') }}
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }
</style>

<script>
    // Countdown and redirect
    let seconds = 5;
    const countdownEl = document.getElementById('countdown');
    
    const interval = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = '{{ route("dashboard.orders") }}';
        }
    }, 1000);
    
    // Clear cart from localStorage
    localStorage.removeItem('diaszone_cart');
    localStorage.removeItem('diaszone_encrypted_order_id');
</script>
@endsection
