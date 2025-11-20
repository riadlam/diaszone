@extends('layouts.app')

@section('title', 'Pay with Cryptocurrency - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Pay with Cryptocurrency</h1>
            <p class="text-sm text-gray-600">Review your order and proceed to Binance Pay</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Order Number</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Diamonds</span>
                    <span class="text-sm font-semibold text-purple-600">
                        {{ $order->diamondPack->diamonds }} + {{ $order->diamondPack->bonus_diamonds }} Bonus
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">User ID</span>
                    <span class="text-sm font-mono text-gray-900">{{ $order->user_id_ml }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Zone ID</span>
                    <span class="text-sm font-mono text-gray-900">{{ $order->zone_id_ml }}</span>
                </div>
            </div>
        </div>

        <!-- Price Breakdown Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Price Breakdown</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Unit Price</span>
                    <span class="text-sm font-semibold text-gray-900">US$ {{ number_format($unit_price, 2) }}</span>
                </div>
                @if($discount_percentage > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Discount ({{ $discount_percentage }}%)</span>
                        <span class="text-sm font-semibold text-red-500">- US$ {{ number_format($discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-semibold text-gray-900">Total Amount</span>
                        <span class="text-lg font-bold text-purple-600">US$ {{ number_format($total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Binance Pay Info Card -->
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Binance Pay</h3>
                    <p class="text-sm text-gray-600">Secure cryptocurrency payment</p>
                </div>
            </div>
            <div class="space-y-2 text-sm text-gray-700">
                <p class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Fast and secure payment processing
                </p>
                <p class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Multiple cryptocurrency options
                </p>
                <p class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Instant order processing
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('select-payment') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg text-center transition-colors">
                    Change Payment Method
                </a>
                <a href="{{ route('crypto-payment', ['encrypted_order_id' => $encrypted_order_id]) }}" 
                   class="flex-1 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-gray-900 font-semibold py-3 px-6 rounded-lg text-center transition-all shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pay with Crypto</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

