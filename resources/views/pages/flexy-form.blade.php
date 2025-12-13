@extends('layouts.app')

@section('title', __('checkout.upload_flexy_receipt') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('checkout.upload_flexy_receipt') }}</h1>
            <p class="text-sm text-gray-600">{{ __('seller.please_upload_receipt') }}</p>
        </div>

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
                        <span class="text-sm text-gray-600">{{ __('checkout.order_number') }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                
                @php
                    $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                    $currencyText = __('game.diamonds');
                    $gameName = __('home.game_mobile_legends');
                    
                    if ($gameType === 'freefire') {
                        $currencyText = __('game.diamonds');
                        $gameName = __('home.game_free_fire');
                    } elseif ($gameType === 'pubgmobile') {
                        $currencyText = __('game.uc');
                        $gameName = __('home.game_pubg_mobile');
                    } elseif ($gameType === 'honorofkings') {
                        $currencyText = __('game.tokens');
                        $gameName = __('home.game_honor_of_kings');
                    } elseif ($gameType === 'bloodstrike') {
                        $currencyText = __('game.golds');
                        $gameName = __('home.game_blood_strike');
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
                    $bonusText = $bonus > 0 ? ' + ' . $bonus . ' ' . __('game.bonus') : '';
                    $packDisplayText = $packDisplayName . $bonusText;
                @endphp
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.game') }}</span>
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
                        <span class="text-sm text-gray-600">{{ __('game.server') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->server_bs ?? __('game.global') }}</span>
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
                        <span class="text-sm text-gray-600">{{ __('game.zone_id') }}</span>
                        <span class="text-sm font-mono text-gray-900">{{ $order->zone_id_ml ?? 'N/A' }}</span>
                    </div>
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
                        $discountAmountDzd = $originalPrice - $finalDzdPrice;
                        $discountPercentage = $originalPrice > 0 ? ($discountAmountDzd / $originalPrice) * 100 : 0;
                        $discountAmountUsd = $discountAmountDzd / 260;
                    } else {
                        // Legacy single-pack order: calculate from pack
                        $usdPrice = (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price);
                        $dzdPrice = (float) ($order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260));
                        $discountPercentage = (float) ($order->diamondPack->discount_percentage ?? 0);
                        $discountAmountUsd = ($usdPrice * $discountPercentage) / 100;
                        $discountAmountDzd = ($dzdPrice * $discountPercentage) / 100;
                        $finalUsdPrice = $usdPrice - $discountAmountUsd;
                        $finalDzdPrice = $dzdPrice - $discountAmountDzd;
                    }
                    
                    $flexyFee = 50; // 50 DZD processing fee (added to final price)
                    $totalWithFee = $finalDzdPrice + $flexyFee;
                @endphp
                @if($discountPercentage > 0)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.discount') }}</span>
                    <span class="text-sm font-semibold text-green-600">-{{ $discountPercentage }}%</span>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('checkout.flexy_processing_fee') }}</span>
                    <span class="text-sm font-semibold text-gray-700">{{ number_format($flexyFee, 0) }} DZD</span>
                </div>
                <div class="border-t-2 border-purple-200 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">{{ __('checkout.total') }}</span>
                        <span class="text-lg font-bold text-purple-600" 
                              id="flexy-total-price"
                              data-price-usd="{{ $finalUsdPrice }}"
                              data-price-dzd="{{ $finalDzdPrice }}"
                              data-fee="{{ $flexyFee }}">
                            {{ number_format($totalWithFee, 0) }} DZD
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flexy Phone Number Notice - Very Prominent -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 rounded-xl shadow-2xl border-4 border-purple-400 p-6 mb-6 transform hover:scale-[1.01] transition-all duration-300 ring-4 ring-purple-300 ring-opacity-50">
            <div class="flex items-center justify-center gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <div class="text-center flex-1">
                    <p class="text-white text-sm md:text-base font-semibold mb-2 uppercase tracking-wide">
                        {{ __('checkout.send_flexy_payment_to') }}
                    </p>
                    <button type="button" 
                            id="copy-phone-btn"
                            onclick="copyPhoneNumber()"
                            class="inline-block cursor-pointer group">
                        <p id="phone-number" class="text-white text-3xl md:text-4xl font-black tracking-wider mb-1 group-hover:text-yellow-300 transition-colors duration-200">
                            0673771763
                        </p>
                    </button>
                    <p id="copy-feedback" class="text-purple-200 text-xs md:text-sm font-medium mt-1">
                        {{ __('uploader.click_number_to_copy_or_call') }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Flexy Upload Form -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
            <form id="flexy-form" action="{{ route('flexy-submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="encrypted_order_id" value="{{ Crypt::encryptString($order->id) }}">
                
                <!-- Receipt Image Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('checkout.receipt_image') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <label for="receipt_image" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">{{ __('uploader.click_to_upload') }}</span> or {{ __('uploader.click_to_upload_or_drag') }}
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF or WEBP (MAX. 5MB)</p>
                            </div>
                            <input id="receipt_image" name="receipt_image" type="file" class="hidden" accept="image/*" required>
                        </label>
                        <div id="image-preview" class="mt-4 hidden">
                            <img id="preview-img" src="" alt="{{ __('checkout.receipt_preview') }}" class="max-w-full h-48 object-contain rounded-lg border-2 border-purple-200">
                            <button type="button" id="remove-image" class="mt-2 text-sm text-red-600 hover:text-red-700 font-medium">
                                {{ __('checkout.remove_image') }}
                            </button>
                        </div>
                        @error('receipt_image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Notes Field -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('checkout.additional_notes') }}
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4" 
                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                              placeholder="{{ __('checkout.additional_notes_placeholder') }}"></textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- reCAPTCHA -->
                <div class="mb-6">
                    <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}"></div>
                    @error('recaptcha')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <div class="mb-6">
                    <button type="submit" 
                            id="submit-btn"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                        {{ __('checkout.send_receipt') }}
                    </button>
                </div>
            </form>
            
            <!-- Change Payment Gateway Link -->
            <div class="border-t-2 border-purple-200 pt-4 text-center">
                <button id="change-payment-btn" 
                        class="text-sm text-purple-600 hover:text-purple-700 font-semibold underline bg-transparent border-none cursor-pointer">
                {{ __('payment.change_gateway') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Google reCAPTCHA v2 -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Image preview functionality
    const receiptInput = document.getElementById('receipt_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImageBtn = document.getElementById('remove-image');
    
    if (receiptInput) {
        receiptInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            receiptInput.value = '';
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        });
    }
    
    // Form submission
    const flexyForm = document.getElementById('flexy-form');
    if (flexyForm) {
        flexyForm.addEventListener('submit', function(e) {
            // Check if reCAPTCHA is completed
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                alert({!! json_encode(__('seller.recaptcha_required')) !!});
                return false;
            }
            
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = {!! json_encode(__('checkout.uploading')) !!};
            }
            
            // Clear cart after successful submission
            // The form will submit normally, and cart will be cleared on redirect
        });
    }
    
    // Clear cart if redirected from successful submission (but keep encrypted_order_id)
    @if(session('clear_cart'))
        // Only clear cart, keep encrypted_order_id for future reference
        localStorage.removeItem('diaszone_cart');
        // Note: We keep diaszone_encrypted_order_id so user can view their order later
    @endif
    
    // Handle Change Payment Gateway button
    const changePaymentBtn = document.getElementById('change-payment-btn');
    if (changePaymentBtn) {
        changePaymentBtn.addEventListener('click', async function() {
            const encryptedOrderIdInput = document.querySelector('input[name="encrypted_order_id"]');
            const encryptedOrderId = encryptedOrderIdInput ? encryptedOrderIdInput.value : null;
            
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
    
    // Copy phone number to clipboard
    window.copyPhoneNumber = function() {
        const phoneNumber = '0673771763';
        const feedback = document.getElementById('copy-feedback');
        
        // Copy to clipboard
        navigator.clipboard.writeText(phoneNumber).then(function() {
            // Show success feedback
            if (feedback) {
                const originalText = feedback.innerHTML;
                feedback.innerHTML = '<span class="text-yellow-300 font-bold">{{ __('checkout.copied_to_clipboard') }}</span>';
                feedback.classList.remove('text-purple-200');
                feedback.classList.add('text-yellow-300');
                
                // Reset after 2 seconds
                setTimeout(function() {
                    feedback.innerHTML = originalText;
                    feedback.classList.remove('text-yellow-300');
                    feedback.classList.add('text-purple-200');
                }, 2000);
            }
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = phoneNumber;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                if (feedback) {
                    feedback.innerHTML = '<span class="text-yellow-300 font-bold">{{ __('checkout.copied_to_clipboard') }}</span>';
                    setTimeout(function() {
                        feedback.innerHTML = {!! json_encode(__('uploader.click_number_to_copy_or_call')) !!} + ' <a href="tel:0673771763" class="underline hover:text-yellow-300">' + {!! json_encode(__('uploader.call_directly')) !!} + '</a>';
                    }, 2000);
                }
            } catch (err) {
                console.error('Failed to copy:', err);
            }
            document.body.removeChild(textArea);
        });
    };
    
    // Update price based on selected currency (default to DZD for Flexy)
    function updateFlexyPrice() {
        // Flexy always uses DZD, but we'll support currency changes if needed
        const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
        const totalPriceEl = document.getElementById('flexy-total-price');
        
        if (totalPriceEl) {
            const priceUsd = parseFloat(totalPriceEl.getAttribute('data-price-usd')) || 0;
            const priceDzd = parseFloat(totalPriceEl.getAttribute('data-price-dzd')) || 0;
            const fee = parseFloat(totalPriceEl.getAttribute('data-fee')) || 50; // 50 DZD fee
            // Always use DZD for Flexy payments, add fee
            const price = priceDzd + fee;
            
            totalPriceEl.textContent = Math.round(price).toLocaleString() + ' DZD';
        }
    }
    
    // Listen for currency changes (though Flexy always uses DZD)
    window.addEventListener('currencyChanged', function(e) {
        updateFlexyPrice();
    });
    
    // Update on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateFlexyPrice();
    });
});
</script>
@endpush

