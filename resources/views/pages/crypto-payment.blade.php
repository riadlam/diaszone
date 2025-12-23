@extends('layouts.app')

@section('title', __('payment.crypto') . ' ' . __('payment.payment') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-blue-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('payment.crypto') }} {{ __('payment.payment') }}</h1>
            <p class="text-sm text-gray-600">Complete your payment using cryptocurrency</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($is_localhost) && $is_localhost)
            <div class="bg-yellow-100 border-2 border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg mb-4" role="alert">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Localhost/Testing Mode</p>
                        <p class="text-sm">NOWPayments API is not configured for localhost. This is a test view. In production, the cryptocurrency checkout will work normally.</p>
                        @if(isset($payment_error))
                            <p class="text-xs mt-1 italic">Error: {{ $payment_error }}</p>
                        @endif
                    </div>
                </div>
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
                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                    <span class="text-base font-semibold text-gray-900">{{ __('checkout.total') }}</span>
                    <span class="text-lg font-bold text-purple-600">US$ {{ number_format($total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Cryptocurrency Payment Instructions -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('checkout.pay_with') }} {{ __('payment.crypto') }}</h2>
            </div>
            
            <div class="space-y-4">
                @if(isset($payment_url) && $payment_url && !isset($is_localhost))
                    <!-- Payment Address (if available) -->
                    @if(isset($pay_address) && $pay_address)
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('payment.payment_address') }}</p>
                        <div class="flex items-center gap-2 bg-white border border-gray-300 rounded p-2">
                            <code class="text-xs font-mono text-gray-800 flex-1 break-all">{{ $pay_address }}</code>
                            <button onclick="copyAddress('{{ $pay_address }}')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs font-semibold transition-colors">
                                {{ __('common.copy') }}
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Send the exact amount to this address</p>
                    </div>
                    @endif

                    <!-- Checkout Button -->
                    <div class="text-center">
                        <a href="{{ $payment_url }}" 
                           target="_blank"
                           class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-8 rounded-lg transition-colors shadow-lg hover:shadow-xl text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('checkout.pay_with') }} {{ __('payment.crypto') }}
                        </a>
                        <p class="text-xs text-gray-500 mt-2">{{ __('payment.click_to_open_payment_page') }}</p>
                    </div>
                @else
                    <!-- Localhost/Testing Mode or No Payment URL -->
                    <div class="bg-gray-50 border-2 border-gray-300 border-dashed rounded-lg p-6 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-gray-700 mb-1">
                            @if(isset($is_localhost) && $is_localhost)
                                {{ __('payment.crypto') }} (Test Mode)
                                @else
                                {{ __('payment.payment_unavailable') }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">
                            @if(isset($is_localhost) && $is_localhost)
                                In production, this button will open the cryptocurrency payment page.
                            @else
                                {{ __('seller.failed_to_load_payment_data') }}
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Payment Instructions -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-2">
                        <strong class="text-gray-900">Step 1:</strong> {{ __('checkout.pay_with') }} {{ __('payment.crypto') }}
                    </p>
                    <p class="text-sm text-gray-700 mb-2">
                        <strong class="text-gray-900">Step 2:</strong> Complete the payment using your preferred cryptocurrency
                    </p>
                    <p class="text-sm text-gray-700">
                        <strong class="text-gray-900">Step 3:</strong> Your order will be processed automatically after payment confirmation
                    </p>
                </div>

                <!-- Payment Status -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-900 mb-1">{{ __('payment.payment') }} Status: <span id="payment-status" class="text-blue-600">{{ __('payment.waiting_for_payment') }}</span></p>
                            <p class="text-xs text-blue-700">We will automatically detect your payment and process your order.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('home') }}" 
               class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg text-center transition-colors">
                {{ __('order.cancel') }}
            </a>
            <button type="button" 
                    onclick="checkPaymentStatus()"
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg">
                Check Payment Status
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const encryptedOrderId = '{{ $encrypted_order_id }}';
    
    // Copy address to clipboard
    window.copyAddress = function(address) {
        navigator.clipboard.writeText(address).then(function() {
            alert({!! json_encode(__('seller.copied')) !!});
        }, function(err) {
            console.error('Failed to copy address:', err);
        });
    };
    
    // Check payment status
    window.checkPaymentStatus = function() {
        const statusElement = document.getElementById('payment-status');
        if (statusElement) {
            statusElement.textContent = 'Checking...';
            
            // Call API to check payment status
            fetch('{{ route("api.orders.check-crypto-payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    encrypted_order_id: encryptedOrderId 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.paid) {
                    statusElement.textContent = {!! json_encode(__('payment.confirmed_redirect')) !!};
                    statusElement.classList.remove('text-blue-600');
                    statusElement.classList.add('text-green-600');
                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard.orders") }}';
                    }, 2000);
                } else {
                    statusElement.textContent = {!! json_encode(__('payment.not_detected')) !!};
                }
            })
            .catch(error => {
                console.error('Error checking payment:', error);
                statusElement.textContent = {!! json_encode(__('payment.error_checking')) !!};
            });
        }
    };
    
    // Auto-check payment status every 30 seconds
    setInterval(checkPaymentStatus, 30000);
});
</script>
@endpush

