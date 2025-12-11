<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('seller.payment_successful') }} - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .success-animation {
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <!-- Success Icon -->
        <div class="success-animation w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-white mb-2">{{ __('seller.payment_successful') }}</h1>
        <p class="text-gray-400 mb-8">{{ __('seller.order_placed_processing') }}</p>
        
        <!-- Order Details -->
        <div class="bg-slate-800 rounded-xl p-6 mb-6 text-left">
            <h3 class="text-lg font-bold text-white mb-4 text-center">{{ __('seller.order_details') }}</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('checkout.order_number') }}</span>
                    <span class="text-white font-mono">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('seller.store') }}</span>
                    <span class="text-white">{{ $order->seller->store_name ?? $order->seller->name }}</span>
                </div>
                <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('seller.pack') }}</span>
                    <span class="text-white">{{ $order->diamondPack->name ?? __('seller.na') }}</span>
                </div>
                <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('checkout.price') }}</span>
                    <span class="text-cyan-400 font-bold">{{ number_format($order->final_price, 2) }} DZD</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">{{ __('seller.status_label') }}</span>
                    <span class="px-3 py-1 rounded-full text-sm bg-yellow-500/20 text-yellow-400">{{ __('seller.processing') }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-600/20 border border-blue-500/30 rounded-lg p-4 mb-6">
                <p class="text-blue-300 text-sm">
                    🎮 {{ __('order.diamonds_sent_successfully') }}
                    {{ __('seller.please_allow_processing_minutes') }}
                </p>
        </div>
        
        <a href="{{ route('seller.store.home', ['username' => $order->seller->username]) }}" 
           class="inline-block w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
            {{ __('seller.back_to_store') }}
        </a>
    </div>
</body>
</html>
