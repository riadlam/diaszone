@extends('layouts.app')

@section('title', 'Select Payment Method')

@section('content')
<div id="payment-page-content" class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-0">
    <div class="container mx-auto px-4">
        <section id="payment-section" class="flex flex-col lg:flex-row gap-6 items-start">
            <!-- Left Column: Payment Methods -->
            <div class="flex-1 lg:max-w-2xl w-full">
                <div>
                    <div class="mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Choose Payment Method</h1>
                        <p class="text-sm text-gray-600">Select your preferred payment option</p>
                    </div>
                    <div class="space-y-3">
                        @php
                            $baseUrl = request()->getSchemeAndHttpHost();
                        @endphp
                        
                        @foreach($paymentMethods as $index => $method)
                            @php
                                $isComingSoon = isset($method['coming_soon']) && $method['coming_soon'];
                                $isChecked = !$isComingSoon && $index === 1;
                            @endphp
                            <label class="payment-method-card block {{ $isComingSoon ? 'cursor-not-allowed' : 'cursor-pointer' }} group relative">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="{{ $method['id'] }}"
                                       class="hidden peer"
                                       @if($isComingSoon) disabled @endif
                                       @if($isChecked) checked @endif>
                                
                                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 {{ $isComingSoon ? 'opacity-60 grayscale pointer-events-none' : 'hover:border-purple-400 hover:shadow-xl hover:scale-[1.02]' }} transition-all duration-300 peer-checked:border-purple-600 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 peer-checked:shadow-2xl peer-checked:shadow-purple-200/50 flex items-center gap-3">
                                    <!-- Payment Icon -->
                                    <div class="flex-shrink-0 {{ $isComingSoon ? '' : 'group-hover:scale-110' }} transition-transform duration-300" style="width: 57.6px; height: 57.6px; min-width: 57.6px; min-height: 57.6px; display: flex !important; align-items: center; justify-center; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 12.8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                                        <img src="{{ $baseUrl }}/storage/images_homepage/{{ $method['icon'] }}" 
                                             alt="{{ $method['name'] }}" 
                                             style="width: 100% !important; height: 100% !important; max-width: 57.6px !important; max-height: 57.6px !important; object-fit: contain !important; display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;"
                                             loading="lazy"
                                             decoding="async">
                                    </div>
                                    
                                    <!-- Payment Info -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-gray-900 mb-1 {{ $isComingSoon ? '' : 'group-hover:text-purple-600' }} transition-colors">{{ $method['name'] }}</h3>
                                        <p class="text-xs text-gray-600 font-medium">{{ $method['description'] }}</p>
                                    </div>
                                    
                                    <!-- Coming Soon Overlay -->
                                    @if($isComingSoon)
                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-300 bg-opacity-30 rounded-xl">
                                        <span class="bg-gray-600 text-white px-3 py-1 rounded-lg text-xs font-bold">Coming Soon</span>
                                    </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Information -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <!-- Order Information (Small) -->
                <div id="order-info-section" class="bg-white rounded-lg shadow-md border border-gray-200 p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Order Details
                    </h3>
                    <!-- Cart Data Content -->
                    <div id="order-info-content" class="space-y-2 text-xs">
                        <!-- Order info will be populated by JavaScript -->
                    </div>
                    <!-- Empty Cart Message -->
                    <div id="empty-cart-message" class="text-center py-4" style="display: none;">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 font-medium mb-3">Cart is empty</p>
                        <p class="text-xs text-gray-500 mb-4">Add products to continue</p>
                        <a href="{{ route('home') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-sm">
                            Go to Home
                        </a>
                    </div>
                </div>
                
                <div id="payment-info-section" class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl shadow-lg border-2 border-purple-100 p-5">
                    <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Payment Information
                    </h2>
                    
                    <!-- Skeleton Loading -->
                    <div id="payment-info-skeleton" class="space-y-3 mb-4 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="border-t-2 border-purple-200 pt-3 space-y-3">
                            <div class="flex justify-between">
                                <div class="h-4 bg-gray-200 rounded w-16"></div>
                                <div class="h-4 bg-gray-200 rounded w-20"></div>
                            </div>
                            <div class="h-12 bg-gray-200 rounded"></div>
                            <div class="flex justify-between pt-3 border-t-2 border-purple-200">
                                <div class="h-5 bg-gray-200 rounded w-24"></div>
                                <div class="h-10 bg-gray-200 rounded w-24"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dynamic Payment Information -->
                    <div id="payment-info-content" class="space-y-3 mb-4" style="display: none;">
                        <!-- Divider -->
                        <div class="border-t-2 border-purple-200 pt-3">
                            <!-- Total Before Discounts -->
                            <div class="flex justify-between items-center mb-2" id="total-before-discount-row" style="display: none;">
                                <span class="text-xs font-semibold text-gray-600">Total Before Discounts</span>
                                <span class="text-xs font-medium text-gray-700" id="total-before-discount">USD 0.00</span>
                            </div>
                            
                            <!-- Discount -->
                            <div class="flex justify-between items-center mb-2" id="discount-row" style="display: none;">
                                <span class="text-xs font-semibold text-gray-600">Discount</span>
                                <span class="text-xs font-medium text-red-500" id="discount-amount">- USD 0.00</span>
                            </div>
                            
                            <!-- Flexy Fee -->
                            <div class="flex justify-between items-center mb-2" id="flexy-fee-row" style="display: none;">
                                <span class="text-xs font-semibold text-gray-600">Flexy Processing Fee</span>
                                <span class="text-xs font-medium text-gray-700" id="flexy-fee-amount">0 DZD</span>
                            </div>
                            
                            <!-- Total -->
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-semibold text-gray-700">Total</span>
                                <span class="text-sm font-bold text-gray-900" id="total-amount">USD 0.00</span>
                            </div>
                            
                            <!-- DiasZone Credit -->
                            <div class="mb-3">
                                <div class="text-xs text-gray-500 lowercase" id="diaszone-credit">diaszone credit 0</div>
                            </div>
                            
                            <!-- Pay with (dynamic based on selection) -->
                            <div class="mb-3 bg-purple-50 rounded-lg p-2 border border-purple-100">
                                <label class="block text-xs font-semibold text-purple-700 mb-1">Pay with</label>
                                <div class="text-xs font-bold text-purple-600" id="pay-with-text">Cryptocurrency (USD)</div>
                            </div>
                            
                            <!-- Pay Now Total -->
                            <div class="flex items-center justify-between gap-3 pt-3 border-t-2 border-purple-200">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-purple-600">Pay Now</span>
                                        <span class="text-base font-semibold text-purple-600" id="pay-now-amount">USD 0.00</span>
                                    </div>
                                </div>
                                <button type="button" 
                                        id="pay-submit-btn"
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg text-sm whitespace-nowrap">
                                    Envoyer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="border-t-2 border-purple-200 pt-3">
                        <p class="text-xs text-gray-600 leading-relaxed font-medium">
                            By proceeding, I acknowledge I have read and agreed to 
                            <a href="{{ route('terms-of-use') }}" target="_blank" class="text-purple-600 hover:text-purple-700 font-bold underline">Terms of sale</a>, 
                            <a href="{{ route('terms-of-use') }}" target="_blank" class="text-purple-600 hover:text-purple-700 font-bold underline">Terms of use</a> & 
                            <a href="{{ route('privacy-policy') }}" target="_blank" class="text-purple-600 hover:text-purple-700 font-bold underline">Privacy Policy</a>.
                        </p>
                        <p class="text-xs text-gray-500 mt-2 font-semibold">Effective {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection


@push('scripts')
<script>
// Styled alert notification function - defined first so it's available everywhere
function showStyledAlert(title, message, type = 'error') {
    try {
        // Remove any existing alerts
        const existingAlert = document.getElementById('styled-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alert = document.createElement('div');
        alert.id = 'styled-alert';
        alert.className = 'fixed top-4 right-4 z-50 max-w-md w-full';
        
        // Set colors based on type
        let bgColor = 'bg-red-50';
        let borderColor = 'border-red-200';
        let textColor = 'text-red-800';
        let iconColor = 'text-red-600';
        let icon = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        
        if (type === 'warning') {
            bgColor = 'bg-yellow-50';
            borderColor = 'border-yellow-200';
            textColor = 'text-yellow-800';
            iconColor = 'text-yellow-600';
            icon = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            `;
        } else if (type === 'success') {
            bgColor = 'bg-green-50';
            borderColor = 'border-green-200';
            textColor = 'text-green-800';
            iconColor = 'text-green-600';
            icon = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            `;
        }
        
        alert.innerHTML = `
            <div class="${bgColor} ${borderColor} border-2 rounded-lg shadow-lg p-4 flex items-start gap-3 animate-slide-in">
                <div class="${iconColor} flex-shrink-0 mt-0.5">
                    ${icon}
                </div>
                <div class="flex-1">
                    <h3 class="font-bold ${textColor} text-lg mb-1">${title}</h3>
                    <p class="${textColor} text-sm">${message}</p>
                </div>
                <button onclick="this.closest('#styled-alert').remove()" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.animation = 'slide-out 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    } catch (e) {
        // Fallback to browser alert if styled alert fails
        console.error('Error showing styled alert:', e);
        alert(title + ': ' + message);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Check if cart exists in localStorage
    const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
    
    // If cart is empty, redirect to home
    if (!cart || cart.length === 0) {
        console.log('Cart is empty, redirecting to home...');
        window.location.href = '{{ route("home") }}';
        return;
    }
    
    // Initialize page if cart exists
    async function initializePage() {
        // Fetch cart data and calculate totals
        async function loadPaymentInfo() {
        const skeleton = document.getElementById('payment-info-skeleton');
        const content = document.getElementById('payment-info-content');
        
        try {
            const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
            
            // Get pack IDs from cart
            const packIds = cart.map(item => item.pack_id).filter(Boolean);
            
            // Fetch pack data
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch('/api/packs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: packIds })
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch pack data');
            }
            
            const data = await response.json();
            const packsMap = {};
            Object.keys(data.packs).forEach(id => {
                packsMap[id] = data.packs[id];
            });
            
            // Display order information
            const orderInfoSection = document.getElementById('order-info-section');
            const orderInfoContent = document.getElementById('order-info-content');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            
            // Show/hide based on cart state
            if (cart.length > 0) {
                // Show order info, hide empty message
                if (emptyCartMessage) emptyCartMessage.style.display = 'none';
                if (orderInfoContent) orderInfoContent.style.display = 'block';
                
                if (orderInfoSection && orderInfoContent) {
                const item = cart[0]; // Only one item allowed on /select
                const pack = packsMap[item.pack_id];
                
                if (pack) {
                    const gameType = pack.game_type || 'mobilelegends';
                    let currencyText = 'Diamonds';
                    let gameName = 'Mobile Legends';
                    
                    if (gameType === 'freefire') {
                        currencyText = 'Diamonds';
                        gameName = 'Free Fire';
                    } else if (gameType === 'pubgmobile') {
                        currencyText = 'UC';
                        gameName = 'PUBG Mobile';
                    } else if (gameType === 'honorofkings') {
                        currencyText = 'Tokens';
                        gameName = 'Honor of Kings';
                    } else if (gameType === 'bloodstrike') {
                        currencyText = 'Golds';
                        gameName = 'Blood Strike';
                    }
                    
                    // Determine pack display name
                    let packDisplayName = '';
                    if (pack.name) {
                        packDisplayName = pack.name;
                    } else {
                        packDisplayName = `${pack.diamonds} ${currencyText}`;
                    }
                    
                    // Bonus display
                    const bonus = pack.bonus || pack.bonus_diamonds || 0;
                    const bonusText = bonus > 0 ? ` + ${bonus} Bonus` : '';
                    const packDisplayText = packDisplayName + bonusText;
                    
                    // Determine order information fields based on game type
                    let orderFieldsHTML = '';
                    if (gameType === 'bloodstrike') {
                        const userIdBs = item.user_id_bs || '';
                        const serverBs = item.server_bs || 'Global';
                        orderFieldsHTML = `
                            <p class="text-gray-600"><span class="font-medium text-gray-700">User ID:</span> ${userIdBs}</p>
                            <p class="text-gray-600"><span class="font-medium text-gray-700">Server:</span> ${serverBs}</p>
                        `;
                    } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                        const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || '';
                        orderFieldsHTML = `
                            <p class="text-gray-600"><span class="font-medium text-gray-700">Player ID:</span> ${playerId}</p>
                        `;
                    } else {
                        // Mobile Legends
                        const userId = item.user_id || '';
                        const zoneId = item.zone_id || '';
                        orderFieldsHTML = `
                            <p class="text-gray-600"><span class="font-medium text-gray-700">User ID:</span> ${userId}</p>
                            <p class="text-gray-600"><span class="font-medium text-gray-700">Zone ID:</span> ${zoneId}</p>
                        `;
                    }
                    
                    orderInfoContent.innerHTML = `
                        <p class="text-gray-800 font-medium">${gameName}</p>
                        <p class="text-purple-600 font-semibold">${packDisplayText}</p>
                        ${orderFieldsHTML}
                    `;
                }
                }
            } else {
                // Cart is empty - show empty message, hide order info and payment info
                if (orderInfoContent) orderInfoContent.style.display = 'none';
                if (emptyCartMessage) emptyCartMessage.style.display = 'block';
                
                // Hide payment information section when cart is empty
                const paymentInfoSection = document.getElementById('payment-info-section');
                if (paymentInfoSection) paymentInfoSection.style.display = 'none';
                
                // Hide skeleton and content
                if (skeleton) skeleton.style.display = 'none';
                if (content) content.style.display = 'none';
            }
            
            // Always show the order info section (it will show either cart data or empty message)
            if (orderInfoSection) orderInfoSection.style.display = 'block';
            
            // Show payment info section only if cart has items
            if (cart.length > 0) {
                const paymentInfoSection = document.getElementById('payment-info-section');
                if (paymentInfoSection) paymentInfoSection.style.display = 'block';
            }
            
            // Get selected currency
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            
            // Get selected payment method
            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || '';
            
            // Calculate totals
            let totalBeforeDiscount = 0;
            let totalDiscount = 0;
            let totalAmount = 0;
            let totalCredits = 0;
            let flexyFee = 0; // 50 DZD fee for Flexy
            
            cart.forEach(item => {
                const pack = packsMap[item.pack_id];
                if (!pack) return;
                
                const quantity = 1;
                // Use price_usd or price_dzd based on selected currency
                const unitPrice = currency === 'DZD' 
                    ? (parseFloat(pack.price_dzd) || parseFloat(pack.price) * 260)
                    : (parseFloat(pack.price_usd) || parseFloat(pack.price));
                const discountPercentage = parseFloat(pack.discount) || 0;
                const discountAmount = (unitPrice * discountPercentage) / 100;
                const priceAfterDiscount = unitPrice - discountAmount;
                
                const itemTotalBeforeDiscount = unitPrice * quantity;
                const itemTotalDiscount = discountAmount * quantity;
                const itemTotal = priceAfterDiscount * quantity;
                
                totalBeforeDiscount += itemTotalBeforeDiscount;
                totalDiscount += itemTotalDiscount;
                totalAmount += itemTotal;
                totalCredits += Math.round(itemTotal * 416);
            });
            
            // Add Flexy fee if Flexy is selected (50 DZD)
            if (selectedPaymentMethod === 'flexy') {
                flexyFee = 50; // Always 50 DZD
                totalAmount += flexyFee;
            }
            
            // Format prices based on currency
            const formatPrice = (price) => {
                return currency === 'DZD' 
                    ? Math.round(price).toLocaleString() + ' DZD'
                    : '$' + price.toFixed(2) + ' USD';
            };
            
            // Update UI
            if (totalBeforeDiscount > totalAmount - flexyFee) {
                document.getElementById('total-before-discount-row').style.display = 'flex';
                document.getElementById('total-before-discount').textContent = formatPrice(totalBeforeDiscount);
                document.getElementById('total-before-discount').setAttribute('data-value', totalBeforeDiscount);
            }
            
            if (totalDiscount > 0) {
                document.getElementById('discount-row').style.display = 'flex';
                document.getElementById('discount-amount').textContent = '- ' + formatPrice(totalDiscount);
                document.getElementById('discount-amount').setAttribute('data-value', totalDiscount);
            }
            
            // Show Flexy fee if applicable
            const flexyFeeRow = document.getElementById('flexy-fee-row');
            if (flexyFeeRow) {
                if (selectedPaymentMethod === 'flexy') {
                    flexyFeeRow.style.display = 'flex';
                    const flexyFeeAmount = document.getElementById('flexy-fee-amount');
                    if (flexyFeeAmount) {
                        flexyFeeAmount.textContent = formatPrice(flexyFee);
                        flexyFeeAmount.setAttribute('data-value', flexyFee);
                    }
                } else {
                    flexyFeeRow.style.display = 'none';
                }
            }
            
            document.getElementById('total-amount').textContent = formatPrice(totalAmount);
            document.getElementById('total-amount').setAttribute('data-value', totalAmount);
            document.getElementById('pay-now-amount').textContent = formatPrice(totalAmount);
            document.getElementById('pay-now-amount').setAttribute('data-value', totalAmount);
            document.getElementById('diaszone-credit').textContent = `diaszone credit ${totalCredits.toLocaleString()}`;
            
            // Update payment method text
            const payWithText = document.getElementById('pay-with-text');
            if (payWithText) {
                const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
                if (selectedMethod) {
                    const methodName = selectedMethod.closest('label')?.querySelector('h3')?.textContent || 'Payment';
                    payWithText.textContent = `${methodName} (${currency})`;
                }
            }
            
            // Hide skeleton, show content
            if (skeleton) skeleton.style.display = 'none';
            if (content) content.style.display = 'block';
            
        } catch (error) {
            console.error('Error loading payment info:', error);
            // Show error but don't redirect
        }
        }
        
        // Load payment information
        loadPaymentInfo();
        
        // Payment method selection logic
        const paymentMethods = {
            'baridimob': 'Baridimob',
            'cryptocurrency': 'Cryptocurrency (USD)',
            'flexy': 'Flexy'
        };
        
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const payWithText = document.getElementById('pay-with-text');
        
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const methodName = paymentMethods[this.value] || this.value;
                    payWithText.textContent = methodName;
                }
            });
        });
        
        // Set initial value
        const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (checkedRadio) {
            const methodName = paymentMethods[checkedRadio.value] || checkedRadio.value;
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            payWithText.textContent = `${methodName} (${currency})`;
        }
        
        // Function to update prices when currency or payment method changes
        function updatePaymentPrices() {
            // Reload payment info to recalculate with new currency and payment method
            loadPaymentInfo();
        }
        
        // Listen for currency changes
        window.addEventListener('currencyChanged', function(e) {
            updatePaymentPrices();
        });
        
        // Update payment method text when selection changes
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const methodName = paymentMethods[this.value] || this.value;
                    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
                    payWithText.textContent = `${methodName} (${currency})`;
                    // Recalculate prices when payment method changes (to add/remove Flexy fee)
                    updatePaymentPrices();
                }
            });
        });
        
        // Handle submit button
        const submitBtn = document.getElementById('pay-submit-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', async function() {
                const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
                if (!selectedMethod) {
                    return;
                }
                
                const paymentMethod = selectedMethod.value;
                
                // Get cart data
                const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
                if (cart.length === 0) {
                    alert('Your cart is empty');
                    return;
                }
                
                // If Flexy is selected, create a NEW order (even if one already exists) and navigate to flexy form
                if (paymentMethod === 'flexy') {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                    
                    try {
                    // Prepare cart items for API - include all game-specific fields
                    const cartItems = cart.map(item => {
                        const cartItem = { pack_id: item.pack_id };
                        
                        // Include all possible fields - backend will validate based on game type
                        if (item.user_id) cartItem.user_id = item.user_id;
                        if (item.zone_id) cartItem.zone_id = item.zone_id;
                        if (item.player_id_ff) cartItem.player_id_ff = item.player_id_ff;
                        if (item.player_id_pubg) cartItem.player_id_pubg = item.player_id_pubg;
                        if (item.player_id_hok) cartItem.player_id_hok = item.player_id_hok;
                        if (item.user_id_bs) cartItem.user_id_bs = item.user_id_bs;
                        if (item.server_bs) cartItem.server_bs = item.server_bs;
                        
                        return cartItem;
                    });
                    
                    // Always create a new order via API (regardless of existing encrypted_order_id)
                    // This allows users to create multiple orders that will all show in "My Orders"
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch('{{ route("api.orders.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            cart_items: cartItems,
                            payment_method: 'flexy'
                        })
                    });
                    
                    if (!response.ok) {
                        // Check for rate limiting (429)
                        if (response.status === 429) {
                            const errorData = await response.json().catch(() => ({}));
                            throw new Error('RATE_LIMIT: Too many requests. Please wait a moment before trying again.');
                        }
                        throw new Error('Failed to create order');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.orders && data.orders.length > 0) {
                        // Store the new encrypted order ID in localStorage array
                        const encryptedOrderId = data.orders[0].encrypted_id;
                        
                        // Get existing encrypted order IDs or create new array
                        const existingOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
                        let orderIdsArray = [];
                        
                        if (existingOrderIds) {
                            try {
                                const parsed = JSON.parse(existingOrderIds);
                                // Ensure it's an array
                                if (Array.isArray(parsed)) {
                                    orderIdsArray = parsed;
                                } else {
                                    // If it's not an array (old format), convert it
                                    orderIdsArray = [parsed];
                                }
                            } catch (e) {
                                // If parsing fails, start fresh
                                orderIdsArray = [];
                            }
                        }
                        
                        // Add the new encrypted order ID to the array (avoid duplicates)
                        if (!orderIdsArray.includes(encryptedOrderId)) {
                            orderIdsArray.push(encryptedOrderId);
                        }
                        
                        // Store the updated array back to localStorage
                        localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(orderIdsArray));
                        
                        // Update "My Orders" button visibility
                        if (window.updateMyOrdersButton) {
                            window.updateMyOrdersButton();
                        }
                        
                        // Clear cart from localStorage after order is created
                        localStorage.removeItem('diaszone_cart');
                        
                        // Navigate to flexy form with encrypted order ID
                        const encodedOrderId = encodeURIComponent(encryptedOrderId);
                        window.location.href = '{{ route("flexy-form") }}?order_id=' + encodedOrderId;
                    } else {
                        throw new Error('Order creation failed');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    
                    // Show styled alert based on error type
                    try {
                        if (error.message && error.message.includes('RATE_LIMIT')) {
                            showStyledAlert('Too Many Requests', 'You are making requests too quickly. Please wait a moment before trying again.', 'warning');
                        } else {
                            showStyledAlert('Order Creation Failed', 'Failed to create order. Please try again.', 'error');
                        }
                    } catch (alertError) {
                        // Fallback if showStyledAlert fails
                        console.error('Error showing alert:', alertError);
                        alert('Failed to create order. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer';
                }
                } else if (paymentMethod === 'cryptocurrency') {
                    // Cryptocurrency is coming soon - prevent selection
                    alert('Cryptocurrency payment is coming soon. Please select another payment method.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer';
                    return;
                } else if (paymentMethod === 'baridimob') {
                    // If Baridimob is selected, create order and navigate to baridimob payment page
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                    
                    try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    // Prepare cart items for API - include all game-specific fields
                    const cartItems = cart.map(item => {
                        const cartItem = { pack_id: item.pack_id };
                        
                        // Add game-specific fields based on what's available
                        if (item.user_id) cartItem.user_id = item.user_id;
                        if (item.zone_id) cartItem.zone_id = item.zone_id;
                        if (item.player_id_ff) cartItem.player_id_ff = item.player_id_ff;
                        if (item.player_id_pubg) cartItem.player_id_pubg = item.player_id_pubg;
                        if (item.player_id_hok) cartItem.player_id_hok = item.player_id_hok;
                        if (item.user_id_bs) cartItem.user_id_bs = item.user_id_bs;
                        if (item.server_bs) cartItem.server_bs = item.server_bs;
                        
                        return cartItem;
                    });
                    
                    const response = await fetch('{{ route("api.orders.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            cart_items: cartItems,
                            payment_method: 'bmccp'
                        })
                    });
                    
                    if (!response.ok) {
                        // Check for rate limiting (429)
                        if (response.status === 429) {
                            const errorData = await response.json().catch(() => ({}));
                            throw new Error('RATE_LIMIT: Too many requests. Please wait a moment before trying again.');
                        }
                        throw new Error('Failed to create order');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.orders && data.orders.length > 0) {
                        // Store the new encrypted order ID in localStorage array
                        const encryptedOrderId = data.orders[0].encrypted_id;
                        
                        // Get existing encrypted order IDs or create new array
                        const existingOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
                        let orderIdsArray = [];
                        
                        if (existingOrderIds) {
                            try {
                                const parsed = JSON.parse(existingOrderIds);
                                // Ensure it's an array
                                if (Array.isArray(parsed)) {
                                    orderIdsArray = parsed;
                                } else {
                                    // If it's not an array (old format), convert it
                                    orderIdsArray = [parsed];
                                }
                            } catch (e) {
                                // If parsing fails, start fresh
                                orderIdsArray = [];
                            }
                        }
                        
                        // Add the new encrypted order ID to the array (avoid duplicates)
                        if (!orderIdsArray.includes(encryptedOrderId)) {
                            orderIdsArray.push(encryptedOrderId);
                        }
                        
                        // Store the updated array back to localStorage
                        localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(orderIdsArray));
                        
                        // Update "My Orders" button visibility
                        if (window.updateMyOrdersButton) {
                            window.updateMyOrdersButton();
                        }
                        
                        // Clear cart from localStorage after order is created
                        localStorage.removeItem('diaszone_cart');
                        
                        // Navigate to baridimob form page with encrypted order ID
                        const encodedOrderId = encodeURIComponent(encryptedOrderId);
                        window.location.href = '/select/bmccp/' + encodedOrderId;
                    } else {
                        throw new Error('Order creation failed');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    
                    // Show styled alert based on error type
                    try {
                        if (error.message && error.message.includes('RATE_LIMIT')) {
                            showStyledAlert('Too Many Requests', 'You are making requests too quickly. Please wait a moment before trying again.', 'warning');
                        } else {
                            showStyledAlert('Order Creation Failed', 'Failed to create order. Please try again.', 'error');
                        }
                    } catch (alertError) {
                        // Fallback if showStyledAlert fails
                        console.error('Error showing alert:', alertError);
                        alert('Failed to create order. Please try again.');
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer';
                }
                } else {
                    // For other payment methods, handle accordingly
                    console.log('Selected payment method:', paymentMethod);
                    // You can add other payment method handling here
                }
        });
        }
    }
    
    // Call initializePage after it's defined
    initializePage();
});
</script>

<style>
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>
@endpush

