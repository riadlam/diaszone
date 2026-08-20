@extends('layouts.app')

@section('title', __('checkout.pay_with') . ' ' . __('payment.crypto') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('checkout.pay_with') }} {{ __('payment.crypto') }}</h1>
            <p class="text-sm text-gray-600">{{ __('checkout.review_order_subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <button id="order-summary-toggle" class="w-full flex items-center justify-between text-left mb-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('checkout.order_summary') }}</h2>
                <svg id="order-summary-icon" class="w-5 h-5 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="order-summary-content" class="space-y-3 hidden">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.order_number') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                @php
                    $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                    
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
                    
                    $packDisplayName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
                    $bonus = $order->diamondPack->bonus_diamonds ?? 0;
                    $bonusText = $bonus > 0 ? ' + ' . $bonus . ' Bonus ' . $currencyText : '';
                @endphp
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.game') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $gameName }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.pack') }}</span>
                    <span class="text-sm font-semibold text-purple-600">{{ $packDisplayName }}{{ $bonusText }}</span>
                </div>
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
                    if (!empty($is_flash_sale) || $order->flash_sale_offer_id) {
                        $finalDzdPrice = (float) ($order->final_price ?? 0);
                        $originalDzdPrice = (float) ($order->original_price ?? $finalDzdPrice);
                        $unitPriceUsd = $originalDzdPrice / 260;
                        $unitPriceDzd = $originalDzdPrice;
                        $totalAmountUsd = $finalDzdPrice / 260;
                        $totalAmountDzd = $finalDzdPrice;
                        $discountAmountUsd = max(0, $unitPriceUsd - $totalAmountUsd);
                        $discountAmountDzd = max(0, $unitPriceDzd - $totalAmountDzd);
                        $discount_percentage = $originalDzdPrice > 0
                            ? (int) round(($discountAmountDzd / $originalDzdPrice) * 100)
                            : 0;
                    } else {
                        $unitPriceUsd = (float) ($order->diamondPack->price_usd ?? $unit_price);
                        $unitPriceDzd = (float) ($order->diamondPack->price_dzd ?? 0);
                        $discount_percentage = (int) round((float) ($discount_percentage ?? 0));
                        $discountAmountUsd = ($unitPriceUsd * $discount_percentage) / 100;
                        $discountAmountDzd = ($unitPriceDzd * $discount_percentage) / 100;
                        $totalAmountUsd = $unitPriceUsd - $discountAmountUsd;
                        $totalAmountDzd = $unitPriceDzd - $discountAmountDzd;
                    }
                @endphp
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.unit_price') }}</span>
                    <span class="text-sm font-semibold text-gray-900" 
                          id="crypto-unit-price"
                          data-price-usd="{{ $unitPriceUsd }}"
                          data-price-dzd="{{ $unitPriceDzd }}">
                        US$ {{ number_format($unitPriceUsd, 2) }}
                    </span>
                </div>
                @if($discount_percentage > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('checkout.discount') }} ({{ $discount_percentage }}%)</span>
                        <span class="text-sm font-semibold text-red-500" 
                              id="crypto-discount"
                              data-discount-usd="{{ $discountAmountUsd }}"
                              data-discount-dzd="{{ $discountAmountDzd }}">
                            - US$ {{ number_format($discountAmountUsd, 2) }}
                        </span>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-semibold text-gray-900">{{ __('checkout.total') }}</span>
                        <span class="text-lg font-bold text-purple-600" 
                              id="crypto-total-price"
                              data-price-usd="{{ $totalAmountUsd }}"
                              data-price-dzd="{{ $totalAmountDzd }}">
                            US$ {{ number_format($totalAmountUsd, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <button id="change-payment-btn" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg text-center transition-colors">
                        {{ __('payment.change_gateway') }}
                </button>
                <a href="{{ route('crypto-payment', ['encrypted_order_id' => $encrypted_order_id]) }}" 
                   class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ __('checkout.pay_with') }} {{ __('payment.crypto') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('order-summary-toggle');
    const content = document.getElementById('order-summary-content');
    const icon = document.getElementById('order-summary-icon');
    
    if (toggle && content && icon) {
        toggle.addEventListener('click', function() {
            const isHidden = content.classList.contains('hidden');
            
            if (isHidden) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });
    }
    
    // Handle Change Payment Method button
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
    function updateCryptoPrice() {
        const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
        
        const unitPriceEl = document.getElementById('crypto-unit-price');
        const discountEl = document.getElementById('crypto-discount');
        const totalPriceEl = document.getElementById('crypto-total-price');
        
        if (unitPriceEl) {
            const priceUsd = parseFloat(unitPriceEl.getAttribute('data-price-usd')) || 0;
            const priceDzd = parseFloat(unitPriceEl.getAttribute('data-price-dzd')) || 0;
            const price = currency === 'DZD' ? priceDzd : priceUsd;
            unitPriceEl.textContent = currency === 'DZD' 
                ? Math.round(price).toLocaleString() + ' DZD'
                : 'US$ ' + price.toFixed(2);
        }
        
        if (discountEl) {
            const discountUsd = parseFloat(discountEl.getAttribute('data-discount-usd')) || 0;
            const discountDzd = parseFloat(discountEl.getAttribute('data-discount-dzd')) || 0;
            const discount = currency === 'DZD' ? discountDzd : discountUsd;
            discountEl.textContent = '- ' + (currency === 'DZD' 
                ? Math.round(discount).toLocaleString() + ' DZD'
                : 'US$ ' + discount.toFixed(2));
        }
        
        if (totalPriceEl) {
            const priceUsd = parseFloat(totalPriceEl.getAttribute('data-price-usd')) || 0;
            const priceDzd = parseFloat(totalPriceEl.getAttribute('data-price-dzd')) || 0;
            const price = currency === 'DZD' ? priceDzd : priceUsd;
            totalPriceEl.textContent = currency === 'DZD' 
                ? Math.round(price).toLocaleString() + ' DZD'
                : 'US$ ' + price.toFixed(2);
        }
    }
    
    // Listen for currency changes
    window.addEventListener('currencyChanged', function(e) {
        updateCryptoPrice();
    });
    
    // Update on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCryptoPrice();
    });
});
</script>
@endpush
@endsection

