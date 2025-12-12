@extends('layouts.app')

@section('title', __('checkout.pay_with') . ' ' . __('payment.baridimob') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('checkout.pay_with') }} {{ __('payment.baridimob') }}</h1>
            <p class="text-sm text-gray-600">Review your order and proceed to payment</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <button id="toggle-order-summary" class="flex justify-between items-center w-full text-lg font-semibold text-gray-800 mb-4 focus:outline-none">
                <span>Order Summary</span>
                <svg id="order-summary-chevron" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="order-summary-content" class="space-y-3 hidden">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Order Number</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                
                @php
                    $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                    $currencyText = 'Diamonds';
                    $gameName = 'Mobile Legends';
                    
                    if ($gameType === 'freefire') {
                        $currencyText = 'Diamonds';
                        $gameName = 'Free Fire';
                    } elseif ($gameType === 'pubgmobile') {
                        $currencyText = 'UC';
                        $gameName = 'PUBG Mobile';
                    } elseif ($gameType === 'honorofkings') {
                        $currencyText = 'Tokens';
                        $gameName = 'Honor of Kings';
                    } elseif ($gameType === 'bloodstrike') {
                        $currencyText = 'Golds';
                        $gameName = 'Blood Strike';
                    }
                    
                    // Determine pack display name
                    $packDisplayName = '';
                    if ($order->diamondPack->name) {
                        $packDisplayName = $order->diamondPack->name;
                    } else {
                        $packDisplayName = $order->diamondPack->diamonds . ' ' . $currencyText;
                    }
                    
                    // Bonus display
                    $bonus = $order->diamondPack->bonus_diamonds ?? 0;
                    $bonusText = $bonus > 0 ? ' + ' . $bonus . ' Bonus' : '';
                    $packDisplayText = $packDisplayName . $bonusText;
                @endphp
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Game</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $gameName }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ $currencyText }}</span>
                    <span class="text-sm font-semibold text-purple-600">
                        {{ $packDisplayText }}
                    </span>
                </div>
                
                @if($gameType === 'bloodstrike')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">User ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->user_id_bs ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Server</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->server_bs ?? 'Global' }}</span>
                    </div>
                @elseif($gameType === 'freefire')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Player ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_ff ?? 'N/A' }}</span>
                    </div>
                @elseif($gameType === 'pubgmobile')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Player ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_pubg ?? 'N/A' }}</span>
                    </div>
                @elseif($gameType === 'honorofkings')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Player ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->player_id_hok ?? 'N/A' }}</span>
                    </div>
                @else
                    {{-- Mobile Legends (default) --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">User ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->user_id_ml ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Zone ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->zone_id_ml ?? 'N/A' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Price Breakdown Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Price Breakdown</h2>
            <div class="space-y-3">
                @php
                    $usdPrice = (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price);
                    $dzdPrice = (float) ($order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260));
                    $discountPercentage = (float) ($order->diamondPack->discount_percentage ?? 0);
                    $discountAmountUsd = ($usdPrice * $discountPercentage) / 100;
                    $discountAmountDzd = ($dzdPrice * $discountPercentage) / 100;
                    $finalUsdPrice = $usdPrice - $discountAmountUsd;
                    $finalDzdPrice = $dzdPrice - $discountAmountDzd;
                @endphp
                @if($discountPercentage > 0)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Discount</span>
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
                    Proceed with Baridimob
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
            proceedBtn.textContent = 'Processing...';
            
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

                    let shortMessage = isArabic 
                        ? 'خدمة الدفع غير متوفرة مؤقتاً'
                        : 'Payment Service Unavailable';

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
                                    ${tTryAgain}
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(errorModal);
                    
                    proceedBtn.disabled = false;
                    proceedBtn.textContent = 'Proceed with Baridimob';
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
                proceedBtn.textContent = 'Proceed with Baridimob';
            }
        });
    }
    
    // Handle Change Payment Gateway button
    const changePaymentBtn = document.getElementById('change-payment-btn');
    if (changePaymentBtn) {
        changePaymentBtn.addEventListener('click', async function() {
            const encryptedOrderId = '{{ $encrypted_order_id }}';
            
            if (!encryptedOrderId) {
                window.location.href = '{{ route("select-payment") }}';
                return;
            }
            
            // Disable button to prevent double clicks
            changePaymentBtn.disabled = true;
            changePaymentBtn.textContent = 'Processing...';
            
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
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
                    <p class="text-gray-600 mb-6">Your payment has been processed successfully. Your order will be completed shortly.</p>
                    <p class="text-sm text-gray-500 mb-4">Redirecting to your orders in <span id="redirect-countdown">5</span> seconds...</p>
                    <button onclick="window.location.href='{{ route('dashboard.orders') }}'" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        View My Orders
                    </button>
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
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h2>
                    <p class="text-gray-600 mb-6">Your payment could not be processed. Please try again or choose a different payment method.</p>
                    <div class="flex gap-3">
                        <button onclick="this.closest('.fixed').remove(); window.history.replaceState({}, document.title, window.location.pathname);" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">
                            Close
                        </button>
                        <button onclick="window.location.href='{{ route('select-payment') }}'" 
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            Try Again
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

