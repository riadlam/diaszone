@extends('layouts.app')

@section('title', 'Receipt Submitted Successfully - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12 flex items-center justify-center">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-8 md:p-12 text-center">
            <!-- Success Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Success Message -->
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Receipt Submitted Successfully!</h1>
            <p class="text-lg text-gray-600 mb-2">Thank you for your payment.</p>
            <p class="text-base text-gray-600 mb-6">We will review your receipt and get back to you shortly. Please stay tuned and watch for updates from the dashboard.</p>
            
            <!-- Redirect Notice -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-700">
                    You will be redirected to <strong>My Orders</strong> in <span id="countdown">5</span> seconds...
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard.orders') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Go to My Orders
                </a>
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear cart from localStorage (but keep encrypted_order_id)
    localStorage.removeItem('diaszone_cart');
    
    // Countdown and redirect
    let countdown = 5;
    const countdownElement = document.getElementById('countdown');
    const redirectUrl = '{{ route("dashboard.orders") }}';
    
    const countdownInterval = setInterval(function() {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            window.location.href = redirectUrl;
        }
    }, 1000);
});
</script>
@endpush

