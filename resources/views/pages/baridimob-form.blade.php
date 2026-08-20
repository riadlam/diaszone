@extends('layouts.app')

@section('title', __('checkout.pay_with') . ' ' . __('payment.baridimob') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('checkout.pay_with') }} {{ __('payment.baridimob') }}</h1>
            <p class="text-sm text-gray-600">{{ __('checkout.review_order_subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <button id="toggle-order-summary" class="flex justify-between items-center w-full text-lg font-semibold text-gray-800 mb-4 focus:outline-none">
                <span>{{ __('checkout.order_summary') }}</span>
                <svg id="order-summary-chevron" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="order-summary-content" class="space-y-3 hidden">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.order_number') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                
                @php
                    // Load order items if available (multi-item support)
                    $order->load('orderItems.diamondPack');
                    $hasOrderItems = $order->orderItems && $order->orderItems->count() > 0;
                    
                    // Get game type from first pack/item
                    if ($hasOrderItems && $order->orderItems->first()) {
                        $gameType = $order->orderItems->first()->diamondPack->game_type ?? 'mobilelegends';
                    } else {
                        $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                    }
                    
                    // Get game name from Game model
                    $gameModel = \App\Models\Game::where('game_type', $gameType)->where('is_active', true)->first();
                    if ($gameModel) {
                        $gameName = $gameModel->name;
                        if (strpos($gameName, ' - ') !== false) {
                            $gameName = explode(' - ', $gameName)[0];
                        } elseif (preg_match('/^\d+/', $gameName) || preg_match('/\d+\s*\+?\s*\d+/', $gameName)) {
                            $gameNames = [
                                'mobilelegends' => 'Mobile Legends',
                                'freefire' => 'Free Fire',
                                'pubgmobile' => 'PUBG Mobile',
                                'honorofkings' => 'Honor of Kings',
                                'bloodstrike' => 'Blood Strike',
                            ];
                            $gameName = $gameNames[$gameType] ?? ucfirst(str_replace('_', ' ', $gameType));
                        }
                    } else {
                        $gameNames = [
                            'mobilelegends' => 'Mobile Legends',
                            'freefire' => 'Free Fire',
                            'pubgmobile' => 'PUBG Mobile',
                            'honorofkings' => 'Honor of Kings',
                            'bloodstrike' => 'Blood Strike',
                        ];
                        $gameName = $gameNames[$gameType] ?? ucfirst(str_replace('_', ' ', $gameType));
                    }
                    
                    $currencyText = 'Diamonds';
                    if ($gameType === 'pubgmobile') {
                        $currencyText = 'UC';
                    } elseif ($gameType === 'honorofkings') {
                        $currencyText = 'Tokens';
                    } elseif ($gameType === 'bloodstrike') {
                        $currencyText = 'Golds';
                    }
                @endphp
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.game') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $gameName }}</span>
                </div>
                
                @if($hasOrderItems)
                    {{-- Multi-item order: show all items --}}
                    @foreach($order->orderItems as $index => $orderItem)
                        @php
                            $pack = $orderItem->diamondPack;
                            $quantity = $orderItem->quantity ?? 1;
                            
                            $packDisplayName = '';
                            if ($pack->name) {
                                $packDisplayName = $pack->name;
                            } else {
                                $packDisplayName = $pack->diamonds . ' ' . $currencyText;
                            }
                            
                            $bonus = $pack->bonus_diamonds ?? 0;
                            $bonusText = $bonus > 0 ? ' + ' . $bonus . ' Bonus' : '';
                            $packDisplayText = $packDisplayName . $bonusText;
                        @endphp
                        <div class="flex justify-between items-center {{ $index > 0 ? 'mt-2 pt-2 border-t border-gray-200' : '' }}">
                            <span class="text-sm text-gray-600">{{ $currencyText }}</span>
                            <span class="text-sm font-semibold text-purple-600">
                                {{ $packDisplayText }}{{ $quantity > 1 ? ' × ' . $quantity : '' }}
                            </span>
                        </div>
                    @endforeach
                @else
                    {{-- Legacy single-pack order --}}
                    @php
                        $packDisplayName = '';
                        if ($order->diamondPack->name) {
                            $packDisplayName = $order->diamondPack->name;
                        } else {
                            $packDisplayName = $order->diamondPack->diamonds . ' ' . $currencyText;
                        }
                        
                        $bonus = $order->diamondPack->bonus_diamonds ?? 0;
                        $bonusText = $bonus > 0 ? ' + ' . $bonus . ' Bonus' : '';
                        $packDisplayText = $packDisplayName . $bonusText;
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ $currencyText }}</span>
                        <span class="text-sm font-semibold text-purple-600">
                            {{ $packDisplayText }}
                        </span>
                    </div>
                @endif
                
                @if($gameType === 'bloodstrike')
                    @if($order->user_id_bs)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.user_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->user_id_bs }}</span>
                    </div>
                    @endif
                    @if($order->server_bs)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.server') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->server_bs }}</span>
                    </div>
                    @endif
                @elseif($gameType === 'freefire')
                    @if($order->player_id_ff)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.player_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_ff }}</span>
                    </div>
                    @endif
                @elseif($gameType === 'pubgmobile')
                    @if($order->player_id_pubg)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.player_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_pubg }}</span>
                    </div>
                    @endif
                @elseif($gameType === 'honorofkings')
                    @if($order->player_id_hok)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.player_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_hok }}</span>
                    </div>
                    @endif
                @elseif($gameType === 'mobilelegends')
                    {{-- Mobile Legends: User ID and Zone ID --}}
                    @if($order->user_id_ml)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.user_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->user_id_ml }}</span>
                    </div>
                    @endif
                    @if($order->zone_id_ml)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.zone_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->zone_id_ml }}</span>
                    </div>
                    @endif
                @else
                    {{-- New games: save_id (User ID) and optionally server --}}
                    @if($order->save_id)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.user_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->save_id }}</span>
                    </div>
                    @endif
                    @if($order->server)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.server') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->server }}</span>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Price Breakdown Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('checkout.price_breakdown') }}</h2>
            <div class="space-y-3">
                @php
                    // Use final_price from order if available (supports multi-item orders with quantities)
                    // Otherwise calculate from single pack (legacy support)
                    if ($order->final_price && $order->final_price > 0) {
                        // Multi-item order: use stored final_price
                        $finalDzdPrice = (float) $order->final_price;
                        $finalUsdPrice = $finalDzdPrice / 260; // Convert DZD to USD
                        
                        // Calculate discount for display (if original_price is available)
                        $originalPrice = (float) ($order->original_price ?? $finalDzdPrice);
                        $discountAmountDzd = max(0, $originalPrice - $finalDzdPrice);
                        $discountPercentage = $originalPrice > 0
                            ? (int) round(($discountAmountDzd / $originalPrice) * 100)
                            : 0;
                        $discountAmountUsd = $discountAmountDzd / 260;
                    } else {
                        // Legacy single-pack order: calculate from pack
                        $usdPrice = (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price);
                        $dzdPrice = (float) ($order->diamondPack->price_dzd ?? 0);
                        $discountPercentage = (int) round((float) ($order->diamondPack->discount_percentage ?? 0));
                        $discountAmountUsd = ($usdPrice * $discountPercentage) / 100;
                        $discountAmountDzd = ($dzdPrice * $discountPercentage) / 100;
                        $finalUsdPrice = $usdPrice - $discountAmountUsd;
                        $finalDzdPrice = $dzdPrice - $discountAmountDzd;
                    }
                @endphp
                @if($discountPercentage > 0)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.discount') }}</span>
                    <span class="text-sm font-semibold text-green-600">-{{ $discountPercentage }}%</span>
                </div>
                @endif
                <div class="border-t-2 border-purple-200 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">{{ __('checkout.total') }}</span>
                        <span class="text-lg font-bold text-purple-600" 
                              id="baridimob-total-price"
                              data-price-usd="{{ $finalUsdPrice }}"
                              data-price-dzd="{{ $finalDzdPrice }}">
                            {{ number_format($finalDzdPrice, 0) }} DZD
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Proceed Button -->
                <button id="proceed-btn" 
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                    {{ __('checkout.proceed_with_baridimob') }}
                </button>
                
                <!-- Change Payment Method Button -->
                <button id="change-payment-btn" 
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                    {{ __('payment.change_gateway') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Localized JS strings
    const tClose = {!! json_encode(__('common.close')) !!};
    const tTryAgain = {!! json_encode(__('common.try_again')) !!};
    const tErrorCodeLabel = {!! json_encode(__('seller.error_code_label')) !!};
    const tConnectionErrorShort = {!! json_encode(__('seller.connection_error_short')) !!};
    const tConnectionError = {!! json_encode(__('seller.connection_error')) !!};
    // Order Summary toggle functionality
    const toggleButton = document.getElementById('toggle-order-summary');
    const content = document.getElementById('order-summary-content');
    const chevron = document.getElementById('order-summary-chevron');

    if (toggleButton && content && chevron) {
        toggleButton.addEventListener('click', function() {
            content.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        });
    }
    
    // Proceed Button - Process Baridimob Payment
    const proceedBtn = document.getElementById('proceed-btn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', async function() {
            const encryptedOrderId = '{{ $encrypted_order_id }}';
            
            if (!encryptedOrderId) {
                alert({!! json_encode(__('seller.invalid_order_id')) !!});
                return;
            }
            
            // Disable button
            proceedBtn.disabled = true;
            proceedBtn.textContent = {!! json_encode(__('common.processing_dots')) !!};
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('{{ route("api.baridimob.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        encrypted_order_id: encryptedOrderId
                    })
                });
                
                const data = await response.json();
                
                console.log('Baridimob response:', {
                    success: data.success,
                    error_code: data.error_code,
                    status: response.status,
                    is_timeout: data.is_timeout,
                    full_data: data
                });
                
                if (data.success && data.checkout_url) {
                    // Redirect to Chargily checkout
                    window.location.href = data.checkout_url;
                } else {
                    // ALWAYS show modal for any error - never show raw error to user
                    const errorModal = document.createElement('div');
                    errorModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
                    errorModal.id = 'error-modal-payment';
                    
                    const isArabic = document.documentElement.dir === 'rtl';
                    
                    // Check for specific known server-side errors to show friendlier guidance
                    let friendlyMessage = isArabic 
                        ? 'عذراً، خدمة الدفع غير متوفرة حالياً. يرجى إعادة المحاولة خلال 10 دقائق. شكراً لصبرك.'
                        : {!! json_encode(__('seller.payment_service_unavailable')) !!};

                    let shortMessage = {!! json_encode(__('checkout.payment_service_unavailable')) !!};

                    // If the backend returned a 400/404 about invalid or expired order, show a clearer message
                    const serverMsg = (data && data.message) ? data.message : '';
                    let errorCode = data.error_code || 'ERR-' + response.status;
                    if (response.status === 400 || response.status === 404) {
                        if (serverMsg.includes('Invalid order ID') || serverMsg.includes('Order not found')) {
                            friendlyMessage = {!! json_encode(__('seller.invalid_order_id')) !!};
                            shortMessage = {!! json_encode(__('seller.invalid_order_id')) !!};
                            errorCode = 'ERR-INVALID-ORDER';
                        }
                    }
                    
                    errorModal.innerHTML = `
                        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 text-center" dir="${isArabic ? 'rtl' : 'ltr'}">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-4">${shortMessage}</h2>
                            <p class="text-gray-600 mb-6 leading-relaxed">
                                ${friendlyMessage}
                            </p>
                            
                            <!-- Simple Error Code Box -->
                            <div class="bg-gray-50 rounded-lg py-2 px-4 mb-6 inline-block">
                                <span class="text-xs text-gray-500 uppercase">${tErrorCodeLabel}:</span>
                                <span class="text-sm font-mono font-bold text-gray-700 ml-2">${errorCode}</span>
                            </div>
                            
                            <div class="flex gap-3">
                                <button onclick="document.getElementById('error-modal-payment').remove();" 
                                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors">
                                    ${tClose}
                                </button>
                                <button onclick="location.reload();" 
                                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                    {!! json_encode(__('common.try_again')) !!}
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(errorModal);
                    
                    proceedBtn.disabled = false;
                    proceedBtn.textContent = {!! json_encode(__('checkout.proceed_with_baridimob')) !!};
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                
                // Network/connection error - show friendly modal
                const isArabic = document.documentElement.dir === 'rtl';
                const errorModal = document.createElement('div');
                errorModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
                errorModal.id = 'error-modal-network';
                
                const friendlyMessage = tConnectionError;
                const shortMessage = tConnectionErrorShort;
                
                errorModal.innerHTML = `
                    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 text-center" dir="${isArabic ? 'rtl' : 'ltr'}">
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-4">${shortMessage}</h2>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            ${friendlyMessage}
                        </p>
                        
                        <!-- Simple Error Code Box -->
                        <div class="bg-gray-50 rounded-lg py-2 px-4 mb-6 inline-block">
                            <span class="text-xs text-gray-500 uppercase">${tErrorCodeLabel}:</span>
                            <span class="text-sm font-mono font-bold text-gray-700 ml-2">ERR-NET</span>
                        </div>
                        
                        <div class="flex gap-3">
                            <button onclick="document.getElementById('error-modal-network').remove();" 
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors">
                                ${tClose}
                            </button>
                            <button onclick="location.reload();" 
                                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                ${tTryAgain}
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(errorModal);
                
                proceedBtn.disabled = false;
                    proceedBtn.textContent = {!! json_encode(__('checkout.proceed_with_baridimob')) !!};
            }
        });
    }
    
    // Handle Change Payment Gateway button
    const changePaymentBtn = document.getElementById('change-payment-btn');
    if (changePaymentBtn) {
        changePaymentBtn.addEventListener('click', async function() {
            const encryptedOrderId = '{{ $encrypted_order_id }}';
            const isFlashSale = @json(!empty($is_flash_sale) || (bool) ($order->flash_sale_offer_id ?? false));
            
            if (!encryptedOrderId) {
                window.location.href = '{{ route("select-payment") }}';
                return;
            }

            // Flash sale: keep the order and return to select with flash context
            if (isFlashSale) {
                window.location.href = @json(route('select-payment', ['order_id' => $encrypted_order_id, 'flash' => 1]));
                return;
            }
            
            // Disable button to prevent double clicks
            changePaymentBtn.disabled = true;
            changePaymentBtn.textContent = {!! json_encode(__('common.processing_dots')) !!};
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                // Delete the order via API
                const response = await fetch('{{ route("api.orders.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        encrypted_order_id: encryptedOrderId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Use the encrypted_order_id returned from backend (exact match)
                    const orderIdToRemove = data.encrypted_order_id || encryptedOrderId;
                    
                    // Remove encrypted order ID from localStorage array
                    const existingOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
                    if (existingOrderIds) {
                        try {
                            let orderIdsArray = JSON.parse(existingOrderIds);
                            if (!Array.isArray(orderIdsArray)) {
                                orderIdsArray = [];
                            }
                            
                            // Create all possible variations of the order ID to match
                            // Include variations from backend response if available
                            const backendVariations = data.encrypted_order_id_variations || {};
                            const variations = [
                                orderIdToRemove,
                                encryptedOrderId,
                                backendVariations.original,
                                backendVariations.url_encoded,
                                backendVariations.url_decoded,
                                decodeURIComponent(orderIdToRemove),
                                decodeURIComponent(encryptedOrderId),
                                encodeURIComponent(orderIdToRemove),
                                encodeURIComponent(encryptedOrderId)
                            ].filter((v, i, arr) => v && v !== null && v !== undefined && arr.indexOf(v) === i); // Remove duplicates, nulls, and undefined
                            
                            // Remove the encrypted order ID from the array (try all variations)
                            const originalLength = orderIdsArray.length;
                            let removedCount = 0;
                            const filteredArray = orderIdsArray.filter(id => {
                                if (!id || typeof id !== 'string') return true; // Skip null/undefined/non-string
                                
                                const trimmedId = id.trim();
                                
                                // Try exact match with all variations
                                for (const variation of variations) {
                                    if (!variation || typeof variation !== 'string') continue;
                                    const trimmedVariation = variation.trim();
                                    
                                    // Exact match
                                    if (trimmedId === trimmedVariation) {
                                        console.log('Matched (exact) and removing:', { id: trimmedId, variation: trimmedVariation });
                                        removedCount++;
                                        return false; // Remove this item
                                    }
                                    
                                    // Try with URL decoding
                                    try {
                                        const decodedId = decodeURIComponent(trimmedId);
                                        const decodedVariation = decodeURIComponent(trimmedVariation);
                                        if (decodedId === trimmedVariation || trimmedId === decodedVariation || decodedId === decodedVariation) {
                                            console.log('Matched (decoded) and removing:', { id: trimmedId, variation: trimmedVariation, decodedId, decodedVariation });
                                            removedCount++;
                                            return false; // Remove this item
                                        }
                                    } catch (e) {
                                        // If decoding fails, continue
                                    }
                                    
                                    // Try case-insensitive match (though encrypted IDs shouldn't have case issues)
                                    if (trimmedId.toLowerCase() === trimmedVariation.toLowerCase()) {
                                        console.log('Matched (case-insensitive) and removing:', { id: trimmedId, variation: trimmedVariation });
                                        removedCount++;
                                        return false; // Remove this item
                                    }
                                }
                                return true; // Keep this item
                            });
                            
                            // If no match found but we have order_id from backend, try one more time with all IDs
                            if (removedCount === 0 && data.order_id && orderIdsArray.length > 0) {
                                console.warn('No match found with variations, trying alternative approach...', {
                                    orderIdToRemove,
                                    variations,
                                    allIds: orderIdsArray
                                });
                            }
                            
                            // Log for debugging
                            console.log('Removing order ID from localStorage', {
                                orderIdToRemove: orderIdToRemove,
                                originalInput: encryptedOrderId,
                                variations: variations,
                                arrayBefore: orderIdsArray,
                                arrayAfter: filteredArray,
                                removed: removedCount,
                                originalLength: originalLength,
                                finalLength: filteredArray.length
                            });
                            
                            if (filteredArray.length > 0) {
                                localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(filteredArray));
                            } else {
                                // If array is empty, remove the key entirely
                                localStorage.removeItem('diaszone_encrypted_order_ids');
                            }
                            
                            // Update "My Orders" button visibility
                            if (window.updateMyOrdersButton) {
                                window.updateMyOrdersButton();
                            }
                        } catch (e) {
                            console.error('Error parsing order IDs:', e);
                        }
                    }
                    
                    // Restore order to cart before redirecting
                    if (data.cart_item) {
                        const cartItem = {
                            id: Date.now().toString(),
                            pack_id: data.cart_item.pack_id,
                            user_id: data.cart_item.user_id || null,
                            zone_id: data.cart_item.zone_id || null,
                            player_id_ff: data.cart_item.player_id_ff || null,
                            player_id_pubg: data.cart_item.player_id_pubg || null,
                            player_id_hok: data.cart_item.player_id_hok || null,
                            user_id_bs: data.cart_item.user_id_bs || null,
                            server_bs: data.cart_item.server_bs || null,
                            timestamp: new Date().toISOString()
                        };
                        
                        // Single item limit: replace entire cart with new item
                        const newCart = [cartItem];
                        localStorage.setItem('diaszone_cart', JSON.stringify(newCart));
                    }
                    
                    // Update "My Orders" button visibility
                    if (window.updateMyOrdersButton) {
                        window.updateMyOrdersButton();
                    }
                    
                    // Redirect to select payment page
                    window.location.href = '{{ route("select-payment") }}';
                } else {
                    // If deletion fails, still redirect (order might not exist)
                    console.error('Failed to delete order:', data.message);
                    window.location.href = '{{ route("select-payment") }}';
                }
            } catch (error) {
                console.error('Error deleting order:', error);
                // Still redirect even if there's an error
                window.location.href = '{{ route("select-payment") }}';
            }
        });
    }
    
    // Update price based on selected currency
    function updateBaridimobPrice() {
        const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
        const totalPriceEl = document.getElementById('baridimob-total-price');
        
        if (totalPriceEl) {
            const priceUsd = parseFloat(totalPriceEl.getAttribute('data-price-usd')) || 0;
            const priceDzd = parseFloat(totalPriceEl.getAttribute('data-price-dzd')) || 0;
            const price = currency === 'DZD' ? priceDzd : priceUsd;
            
            totalPriceEl.textContent = currency === 'DZD' 
                ? Math.round(price).toLocaleString() + ' DZD'
                : '$' + price.toFixed(2) + ' USD';
        }
    }
    
    // Listen for currency changes
    window.addEventListener('currencyChanged', function(e) {
        updateBaridimobPrice();
    });
    
    // Update on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateBaridimobPrice();
        
        // Check for success/failure query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const failed = urlParams.get('failed');
        
        if (success === '1') {
            // Payment was successful
            // Clear cart
            localStorage.removeItem('diaszone_cart');
            
            // Show success message
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
            successMessage.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 text-center">
                    <div class="mb-4">
                        <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{!! json_encode(__('checkout.payment_successful_title')) !!}</h2>
                    <p class="text-gray-600 mb-6">{!! json_encode(__('checkout.payment_successful_message')) !!}</p>
                    <p class="text-sm text-gray-500 mb-4">{!! json_encode(__('checkout.redirecting_to_orders')) !!} <span id="redirect-countdown">5</span> {!! json_encode(__('common.seconds')) !!}...</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button onclick="window.location.href='{{ route('dashboard.orders') }}'" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-5 rounded-lg transition-colors">
                            {!! json_encode(__('checkout.go_to_my_orders')) !!}
                        </button>
                        <button onclick="window.location.href='{{ route('event.show', 'mobilelegends') }}'" 
                                class="w-full bg-amber-500 hover:bg-amber-600 text-gray-950 font-semibold py-3 px-5 rounded-lg transition-colors">
                            {!! json_encode(__('checkout.spin_lucky_wheel')) !!}
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(successMessage);
            
            // Countdown and redirect
            let countdown = 5;
            const countdownElement = document.getElementById('redirect-countdown');
            const countdownInterval = setInterval(function() {
                countdown--;
                if (countdownElement) {
                    countdownElement.textContent = countdown;
                }
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '{{ route('dashboard.orders') }}';
                }
            }, 1000);
            
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (failed === '1') {
            // Payment failed
            const errorMessage = document.createElement('div');
            errorMessage.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
            errorMessage.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 text-center">
                    <div class="mb-4">
                        <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{!! json_encode(__('checkout.payment_failed_title')) !!}</h2>
                    <p class="text-gray-600 mb-6">{!! json_encode(__('checkout.payment_failed_message')) !!}</p>
                    <div class="flex gap-3">
                        <button onclick="this.closest('.fixed').remove(); window.history.replaceState({}, document.title, window.location.pathname);" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">
                            {!! json_encode(__('common.close')) !!}
                        </button>
                        <button onclick="window.location.href='{{ route('select-payment') }}'" 
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            {!! json_encode(__('common.try_again')) !!}
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(errorMessage);
            
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
});
</script>
@endpush

