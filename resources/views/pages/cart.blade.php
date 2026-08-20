@extends('layouts.app')

@section('title', 'Shopping Cart - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('cart.title') }}</h1>
            <p class="text-sm text-gray-600">{{ __('cart.subtitle') }}</p>
            <!-- Multi-Item Cart Notice -->
            <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-purple-800">
                    <span class="font-semibold">{{ __('cart.note') }}</span> You can select multiple packs from the same game. Use quantity selectors to adjust amounts.
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left Column: Cart Items -->
            <div class="flex-1">
                <!-- Empty Cart State -->
                <div id="empty-cart" class="hidden bg-white rounded-xl shadow-lg border-2 border-purple-100 p-12 text-center">
                    <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('cart.empty_cart_title') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('cart.empty_cart_message') }}</p>
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                        {{ __('common.continue_shopping') }}
                    </a>
                </div>

                <!-- Cart Items List -->
                <div id="cart-items-list" class="space-y-4">
                    <!-- Skeleton loading will be inserted here -->
                </div>
            </div>

            <!-- Right Column: Payment Details (Sticky) -->
            <div class="lg:w-96">
                <div id="payment-details" class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 sticky top-24">
                    <!-- Skeleton loading will be inserted here -->
                </div>

                <!-- Checkout Button -->
                <div id="checkout-button-container" class="hidden mt-4">
                    <a href="{{ route('select-payment') }}" 
                       id="proceed-checkout-btn"
                       class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg text-center transition-colors shadow-md hover:shadow-lg">
                        {{ __('cart.proceed_to_checkout') }}
                    </a>
                </div>

                <!-- Product Availability Error Container -->
                <div id="product-availability-errors" class="hidden mt-4">
                    <!-- Error messages will be inserted here dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Wait for CartManager to be available
function waitForCartManager(callback, maxAttempts = 50) {
    if (window.CartManager) {
        callback();
    } else if (maxAttempts > 0) {
        setTimeout(() => waitForCartManager(callback, maxAttempts - 1), 100);
    } else {
        console.error('CartManager not available after waiting');
        // Fallback: try to read directly from localStorage
        if (typeof Storage !== 'undefined') {
            callback();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Localized strings used in JS templates
    const tTotalBeforeDiscounts = {!! json_encode(__('checkout.total_before_discounts')) !!};
    const tDiscountLabel = {!! json_encode(__('coupons.discount')) !!};
    const tTotalLabel = {!! json_encode(__('checkout.total')) !!};
            // Multi-item cart is now supported - no limit enforcement needed
    
    waitForCartManager(() => {
        const cartItemsList = document.getElementById('cart-items-list');
        const emptyCart = document.getElementById('empty-cart');
        const paymentDetails = document.getElementById('payment-details');
        const checkoutButton = document.getElementById('checkout-button-container');
        
        // Fallback CartManager if not available globally
        const CartManager = window.CartManager || {
            getCart: function() {
                const cart = localStorage.getItem('diaszone_cart');
                const parsed = cart ? JSON.parse(cart) : [];
                // Ensure all items have quantity field
                return parsed.map(item => ({
                    ...item,
                    quantity: item.quantity || 1
                }));
            },
            
            updateQuantity: function(itemId, quantity) {
                const cart = this.getCart();
                quantity = Math.max(1, Math.min(20, parseInt(quantity) || 1));
                const itemIndex = cart.findIndex(item => item.id === itemId);
                if (itemIndex >= 0) {
                    if (quantity <= 0) {
                        cart.splice(itemIndex, 1);
                    } else {
                        cart[itemIndex].quantity = quantity;
                    }
                    localStorage.setItem('diaszone_cart', JSON.stringify(cart));
                    if (window.CartManager && window.CartManager.updateCartUI) {
                        window.CartManager.updateCartUI();
                    }
                }
                return cart;
            },
            removeFromCart: function(itemId) {
                const cart = this.getCart();
                const filtered = cart.filter(item => item.id !== itemId);
                localStorage.setItem('diaszone_cart', JSON.stringify(filtered));
                if (window.CartManager && window.CartManager.updateCartUI) {
                    window.CartManager.updateCartUI();
                }
            },
            fetchPacks: async function(packIds) {
                if (!packIds || packIds.length === 0) return [];
                try {
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
                    if (!response.ok) throw new Error({!! json_encode(__('seller.failed_to_fetch_packs')) !!});
                    const data = await response.json();
                    return Object.values(data.packs);
                } catch (error) {
                    console.error('Error fetching packs:', error);
                    return [];
                }
            }
        };
        
        // Skeleton loading HTML
        function getCartSkeleton(count = 1) {
            return Array(count).fill(0).map(() => `
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 animate-pulse">
                    <div class="h-5 bg-gray-200 rounded w-32 mb-6"></div>
                    
                    <!-- Row 1: Product with Image -->
                    <div class="flex items-start gap-4 pb-5 border-b border-gray-100 mb-5">
                        <div class="flex-shrink-0 w-14 h-14 bg-gray-200 rounded-lg"></div>
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                        </div>
                        <div class="w-5 h-5 bg-gray-200 rounded"></div>
                    </div>
                    
                    <!-- Row 2: Discount, Quantity, and Price -->
                    <div class="flex items-center justify-between pb-5 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-4">
                            <div class="h-3 bg-gray-200 rounded w-20"></div>
                            <div class="h-3 bg-gray-200 rounded w-8"></div>
                        </div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                    </div>
                    
                    <!-- Row 3: User ID and Zone ID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="h-3 bg-gray-200 rounded w-16 mb-2"></div>
                            <div class="h-8 bg-gray-200 rounded"></div>
                        </div>
                        <div>
                            <div class="h-3 bg-gray-200 rounded w-16 mb-2"></div>
                            <div class="h-8 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function getPaymentSkeleton() {
            return `
                <div class="space-y-3 mb-5 animate-pulse">
                    <div class="flex justify-between items-center">
                        <div class="h-3 bg-gray-200 rounded w-32"></div>
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="h-3 bg-gray-200 rounded w-20"></div>
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex flex-col gap-2">
                                <div class="h-4 bg-gray-200 rounded w-24"></div>
                                <div class="h-3 bg-gray-200 rounded w-32"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        async function loadCart() {
            // Get selected currency at the start
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            
            // Format prices based on currency
            const formatPrice = (price) => {
                return currency === 'DZD' 
                    ? Math.round(price).toLocaleString() + ' DZD'
                    : '$' + price.toFixed(2) + ' USD';
            };
            
            let cart = CartManager.getCart();
            
            // Debug: Log cart items to verify user_id/zone_id are present
            console.log('Cart items loaded:', cart);
            cart.forEach((item, idx) => {
                console.log(`Cart item ${idx}:`, {
                    pack_id: item.pack_id,
                    user_id: item.user_id,
                    zone_id: item.zone_id,
                    player_id_ff: item.player_id_ff,
                    player_id_pubg: item.player_id_pubg,
                    player_id_hok: item.player_id_hok,
                    user_id_bs: item.user_id_bs,
                    server_bs: item.server_bs
                });
            });
            
            // Multi-item cart is supported - no limit enforcement
            
            if (cart.length === 0) {
                emptyCart.classList.remove('hidden');
                paymentDetails.innerHTML = '<div class="text-center py-8 text-gray-500"><p class="text-sm">Add items to see payment details</p></div>';
                checkoutButton.classList.add('hidden');
                return;
            }
            
            emptyCart.classList.add('hidden');
            
            // Show skeleton loading
            cartItemsList.innerHTML = getCartSkeleton(cart.length);
            paymentDetails.innerHTML = getPaymentSkeleton();
            checkoutButton.classList.add('hidden');
            
            // Fetch pack data from server
            const packIds = cart.map(item => item.pack_id).filter(Boolean);
            const packs = await CartManager.fetchPacks(packIds);
            const packsMap = {};
            packs.forEach(pack => {
                packsMap[pack.id] = pack;
            });
            
            // Calculate totals
            let totalBeforeDiscount = 0;
            let totalDiscount = 0;
            let totalAmount = 0;
            let totalCredits = 0;
            
            // Display all cart items with quantity controls
            cartItemsList.innerHTML = cart.map((item, index) => {
                const pack = packsMap[item.pack_id];
                
                if (!pack) {
                    return `
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-red-500">Pack not found (ID: ${item.pack_id})</p>
                        </div>
                    `;
                }
                const quantity = item.quantity || 1;
                // Use price_usd or price_dzd based on selected currency
                const unitPriceBase = currency === 'DZD' 
                    ? (parseFloat(pack.price_dzd) || 0)
                    : (parseFloat(pack.price_usd) || parseFloat(pack.price));
                const discountPercentage = parseFloat(pack.discount) || 0;
                const discountAmount = (unitPriceBase * discountPercentage) / 100;
                const priceAfterDiscount = unitPriceBase - discountAmount;
                
                const itemTotalBeforeDiscount = unitPriceBase * quantity;
                const itemTotalDiscount = discountAmount * quantity;
                const itemTotal = priceAfterDiscount * quantity;
                
                totalBeforeDiscount += itemTotalBeforeDiscount;
                totalDiscount += itemTotalDiscount;
                totalAmount += itemTotal;
                totalCredits += Math.round(itemTotal * 416);
                
                // Determine game type and currency
                const gameType = pack.game_type || 'mobilelegends';
                let currencyText = 'Diamonds';
                
                // Use game_display_name from pack data (from API) - dynamic, not static
                const gameName = pack.game_display_name || 'Mobile Legends';
                
                // Determine currency based on game type
                if (gameType === 'freefire') {
                    currencyText = 'Diamonds';
                } else if (gameType === 'pubgmobile') {
                    currencyText = 'UC';
                } else if (gameType === 'honorofkings') {
                    currencyText = 'Tokens';
                } else if (gameType === 'bloodstrike') {
                    currencyText = 'Golds';
                }
                
                // Determine pack display name
                let packDisplayName = '';
                if (pack.name) {
                    packDisplayName = pack.name;
                } else {
                    packDisplayName = `${pack.diamonds} ${currencyText}`;
                }
                
                // Use game thumbnail from pack data
                let imageUrl = '';
                let showImage = true;
                
                // Get game image from pack data (set by API)
                const gameImage = pack.game_image || null;
                if (gameImage) {
                    const baseUrl = window.location.origin;
                    imageUrl = baseUrl + '/' + gameImage;
                    showImage = true;
                } else {
                    // Fallback: No image available
                    showImage = false;
                }
                
                // Determine order information fields based on game type
                // These values are static (read-only) - they come from what the user filled in the order form before adding to cart
                let orderInfoHTML = '';
                if (gameType === 'bloodstrike') {
                    // Blood Strike: User ID and Server (static values from form)
                    const userIdBs = item.user_id_bs || '';
                    const serverBs = item.server_bs || 'Global';
                    orderInfoHTML = `
                        <div>
                            <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Order Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">User ID</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                        ${userIdBs || 'N/A'}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Server</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                        ${serverBs}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                    // Free Fire / PUBG Mobile / Honor of Kings: Player ID (static value from form)
                    const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || item.player_id || item.save_id || '';
                    orderInfoHTML = `
                        <div>
                            <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Order Information</h4>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Player ID</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                    ${playerId || 'N/A'}
                                </div>
                            </div>
                        </div>
                    `;
                } else if (gameType === 'mobilelegends') {
                    // Mobile Legends: User ID and Zone ID (static values from form)
                    orderInfoHTML = `
                        <div>
                            <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Order Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">User ID</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                        ${item.user_id || 'N/A'}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Zone ID</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                        ${item.zone_id || 'N/A'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Other games (arena_breakout, naruto_shippuden, heroes_evolved, etc.): User ID only (from save_id or user_id)
                    const userId = item.save_id || item.user_id || '';
                    if (userId) {
                        orderInfoHTML = `
                            <div>
                                <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Order Information</h4>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">User ID</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 font-mono text-xs cursor-default" readonly>
                                        ${userId}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    // If no user_id/save_id, don't show order information section
                }
                
                // Bonus display
                const bonus = pack.bonus || pack.bonus_diamonds || 0;
                const bonusText = bonus > 0 ? ` + ${bonus} Bonus ${currencyText}` : '';
                const packDisplayText = packDisplayName + bonusText;
                
                return `
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Order Details ${cart.length > 1 ? `(${index + 1})` : ''}</h2>
                    
                    <!-- Row 1: Product with Image -->
                    <div class="flex items-start gap-4 pb-5 border-b border-gray-100 mb-5">
                        <div class="flex-shrink-0" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; display: flex !important; align-items: center; justify-content: center; background-color: #f9fafb; border-radius: 10px; overflow: hidden;">
                            ${showImage && imageUrl ? `
                                <img src="${imageUrl}" 
                                     alt="${pack.game_display_name || gameName}" 
                                     style="width: 100% !important; height: 100% !important; max-width: 56px !important; max-height: 56px !important; object-fit: cover !important; display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;"
                                     loading="lazy"
                                     decoding="async">
                            ` : `
                                <div style="width: 100%; height: 100%; background-color: #f9fafb;"></div>
                            `}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-medium text-gray-800 mb-1">${pack.game_display_name || gameName}</h3>
                            <p class="text-sm text-gray-600 mb-0.5">${packDisplayText}</p>
                        </div>
                        <button onclick="removeCartItem('${item.id}')" 
                                class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0 p-1"
                                title="Remove from cart">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Row 2: Discount, Quantity, and Price -->
                    <div class="flex items-center justify-between pb-5 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-4">
                            ${discountPercentage > 0 ? `<span class="text-xs font-medium text-purple-500">Discount: ${discountPercentage.toFixed(1)}%</span>` : ''}
                            <span class="text-xs text-gray-500">× ${quantity}</span>
                        </div>
                        <div class="text-base font-semibold text-purple-600 cart-item-price" 
                             data-price-usd="${pack.price_usd || pack.price}" 
                             data-price-dzd="${pack.price_dzd || 0}" 
                             data-discount="${discountPercentage}">
                            ${currency === 'DZD' 
                                ? Math.round(unitPriceBase).toLocaleString() + ' DZD'
                                : '$' + unitPriceBase.toFixed(2) + ' USD'}
                        </div>
                    </div>
                    
                    <!-- Row 3: Order Information (Dynamic based on game type) -->
                    ${orderInfoHTML}
                </div>
                `;
            }).join('');
            
            // Update payment details (same format as checkout)
            paymentDetails.innerHTML = `
            <div class="space-y-3 mb-5" id="payment-details-content">
                <!-- Total Before Discounts -->
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">${tTotalBeforeDiscounts}</span>
                    <span class="text-xs font-medium text-gray-700 cart-total-before" data-value="${totalBeforeDiscount}">${formatPrice(totalBeforeDiscount)}</span>
                </div>
                
                <!-- Discount -->
                ${totalDiscount > 0 ? `
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-600">${tDiscountLabel}</span>
                        <span class="text-xs font-medium text-red-500 cart-discount" data-value="${totalDiscount}">- ${formatPrice(totalDiscount)}</span>
                    </div>
                ` : ''}
                
                <!-- Divider -->
                <div class="border-t border-gray-100 pt-3">
                    <!-- Total and Pay Now on same row -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-purple-600">${tTotalLabel}</span>
                                <span class="text-base font-semibold text-purple-600 cart-total" data-value="${totalAmount}">${formatPrice(totalAmount)}</span>
                            </div>
                            <!-- DiasZone Credit - Under Total, as part of the same cell -->
                            <div class="text-xs text-gray-500 lowercase mt-1 ml-0">
                                diaszone credit ${totalCredits.toLocaleString()}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
            
            checkoutButton.classList.remove('hidden');
        }
        
        // Function to update prices when currency changes
        function updateCartPrices() {
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            
            // Update item prices (with quantity)
            document.querySelectorAll('.cart-item-price').forEach(element => {
                const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
                const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
                const discount = parseFloat(element.getAttribute('data-discount')) || 0;
                const quantity = parseInt(element.getAttribute('data-quantity')) || 1;
                
                let unitPrice = currency === 'DZD' ? priceDzd : priceUsd;
                if (discount > 0) {
                    const discountAmount = (unitPrice * discount) / 100;
                    unitPrice = unitPrice - discountAmount;
                }
                const totalPrice = unitPrice * quantity;
                
                const formatPrice = (price) => {
                    return currency === 'DZD' 
                    ? Math.round(price).toLocaleString() + ' DZD'
                    : '$' + price.toFixed(2) + ' USD';
                };
                
                element.textContent = formatPrice(totalPrice);
            });
            
            // Recalculate totals based on new currency
            const cart = CartManager.getCart();
            if (cart.length > 0) {
                // Get pack IDs and fetch fresh data
                const packIds = cart.map(item => item.pack_id).filter(Boolean);
                CartManager.fetchPacks(packIds).then(packs => {
                    const packsMap = {};
                    packs.forEach(pack => {
                        packsMap[pack.id] = pack;
                    });
                    
                    // Recalculate totals
                    let totalBeforeDiscount = 0;
                    let totalDiscount = 0;
                    let totalAmount = 0;
                    
                    cart.forEach(item => {
                        const pack = packsMap[item.pack_id];
                        if (!pack) return;
                        
                        const quantity = item.quantity || 1; // Use actual item quantity
                        const unitPriceBase = currency === 'DZD' 
                            ? (parseFloat(pack.price_dzd) || 0)
                            : (parseFloat(pack.price_usd) || parseFloat(pack.price));
                        const discountPercentage = parseFloat(pack.discount) || 0;
                        const discountAmount = (unitPriceBase * discountPercentage) / 100;
                        const priceAfterDiscount = unitPriceBase - discountAmount;
                        
                        totalBeforeDiscount += unitPriceBase * quantity;
                        totalDiscount += discountAmount * quantity;
                        totalAmount += priceAfterDiscount * quantity;
                    });
                    
                    // Format prices based on currency
                    const formatPrice = (price) => {
                        return currency === 'DZD' 
                            ? Math.round(price).toLocaleString() + ' DZD'
                            : '$' + price.toFixed(2) + ' USD';
                    };
                    
                    // Update totals
                    const totalBeforeEl = document.querySelector('.cart-total-before');
                    const discountEl = document.querySelector('.cart-discount');
                    const totalEl = document.querySelector('.cart-total');
                    
                    if (totalBeforeEl) {
                        totalBeforeEl.setAttribute('data-value', totalBeforeDiscount);
                        totalBeforeEl.textContent = formatPrice(totalBeforeDiscount);
                    }
                    
                    if (discountEl && totalDiscount > 0) {
                        discountEl.setAttribute('data-value', totalDiscount);
                        discountEl.textContent = '- ' + formatPrice(totalDiscount);
                    }
                    
                    if (totalEl) {
                        totalEl.setAttribute('data-value', totalAmount);
                        totalEl.textContent = formatPrice(totalAmount);
                    }
                });
            }
        }
        
        // Listen for currency changes - reload cart to recalculate everything
        window.addEventListener('currencyChanged', function(e) {
            // Reload the entire cart to recalculate all prices with new currency
            loadCart();
        });
        
        // Update prices on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateCartPrices, 100);
        });
        
        function removeCartItem(itemId) {
            CartManager.removeFromCart(itemId);
            loadCart();
        }
        
        // Make removeCartItem globally available
        window.removeCartItem = removeCartItem;
        
                // Handle "Pay Now" button click - redirect to payment selection
                const proceedCheckoutBtn = document.getElementById('proceed-checkout-btn');
                if (proceedCheckoutBtn) {
                    proceedCheckoutBtn.addEventListener('click', async (e) => {
                        e.preventDefault(); // Always prevent default to validate first
                        
                        // Show loading state
                        const originalText = proceedCheckoutBtn.innerHTML;
                        proceedCheckoutBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Validating...';
                        proceedCheckoutBtn.classList.add('opacity-75', 'cursor-not-allowed');
                        proceedCheckoutBtn.style.pointerEvents = 'none';
                        
                        // Hide previous errors
                        const errorContainer = document.getElementById('product-availability-errors');
                        if (errorContainer) {
                            errorContainer.classList.add('hidden');
                            errorContainer.innerHTML = '';
                        }

                        // Validate cart before redirecting
                        const cart = CartManager.getCart();
                        if (cart.length === 0) {
                            proceedCheckoutBtn.innerHTML = originalText;
                            proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                            proceedCheckoutBtn.style.pointerEvents = '';
                            alert({!! json_encode(__('seller.cart_empty')) !!} + '. ' + {!! json_encode(__('seller.cart_empty_info')) !!});
                            window.location.href = '{{ route("home") }}';
                            return;
                        }
                        
                        // Validate each item has required fields based on game type
                        const packIds = cart.map(item => item.pack_id).filter(Boolean);
                        const packs = await CartManager.fetchPacks(packIds);
                        const packsMap = {};
                        packs.forEach(pack => {
                            packsMap[pack.id] = pack;
                        });
                        
                        for (const item of cart) {
                            if (!item.pack_id) {
                                proceedCheckoutBtn.innerHTML = originalText;
                                proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                proceedCheckoutBtn.style.pointerEvents = '';
                                alert({!! json_encode(__('seller.cart_items_missing_pack_info')) !!});
                                return;
                            }
                            
                            const pack = packsMap[item.pack_id];
                            if (!pack) {
                                proceedCheckoutBtn.innerHTML = originalText;
                                proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                proceedCheckoutBtn.style.pointerEvents = '';
                                alert({!! json_encode(__('seller.cart_items_invalid_pack_info')) !!});
                                return;
                            }
                            
                            const gameType = pack.game_type || 'mobilelegends';
                            
                            // Validate based on game type
                            if (gameType === 'bloodstrike') {
                                if (!item.user_id_bs || !item.server_bs) {
                                    proceedCheckoutBtn.innerHTML = originalText;
                                    proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                    proceedCheckoutBtn.style.pointerEvents = '';
                                    alert({!! json_encode(__('seller.cart_items_missing_userid_server')) !!});
                                    return;
                                }
                            } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                                const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || item.player_id || item.save_id;
                                if (!playerId) {
                                    proceedCheckoutBtn.innerHTML = originalText;
                                    proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                    proceedCheckoutBtn.style.pointerEvents = '';
                                    alert({!! json_encode(__('seller.cart_items_missing_player_id')) !!});
                                    return;
                                }
                            } else if (gameType === 'mobilelegends') {
                                // Mobile Legends - User ID and Zone ID
                                if (!item.user_id || !item.zone_id) {
                                    proceedCheckoutBtn.innerHTML = originalText;
                                    proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                    proceedCheckoutBtn.style.pointerEvents = '';
                                    alert({!! json_encode(__('seller.cart_items_missing_userid_zone_id')) !!});
                                    return;
                                }
                            } else {
                                // Other games (Aether Gazer, Heroes Evolved, etc.) - User ID only (save_id)
                                const userId = item.save_id || item.user_id;
                                if (!userId) {
                                    proceedCheckoutBtn.innerHTML = originalText;
                                    proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                    proceedCheckoutBtn.style.pointerEvents = '';
                                    alert({!! json_encode(__('seller.cart_items_missing_user_id')) !!});
                                    return;
                                }
                            }
                        }

                        // Validate product availability with Digiflazz
                        try {
                            const cartItemsForValidation = cart.map(item => ({
                                pack_id: parseInt(item.pack_id),
                                quantity: parseInt(item.quantity) || 1,
                            })).filter(item => item.pack_id); // Filter out items without pack_id

                            if (cartItemsForValidation.length === 0) {
                                proceedCheckoutBtn.innerHTML = originalText;
                                proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                proceedCheckoutBtn.style.pointerEvents = '';
                                alert('Cart items are missing product codes. Please refresh and try again.');
                                return;
                            }

                            const response = await fetch('{{ route("api.cart.validate") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({
                                    cart_items: cartItemsForValidation
                                })
                            });

                            const result = await response.json();

                            if (!result.valid) {
                                // Show error UI
                                proceedCheckoutBtn.innerHTML = originalText;
                                proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                                proceedCheckoutBtn.style.pointerEvents = '';
                                
                                showProductAvailabilityErrors(result.unavailable_items || [], cart, packsMap);
                                return;
                            }

                            // All validations passed - proceed to checkout
                            window.location.href = '{{ route("select-payment") }}';
                            
                        } catch (error) {
                            console.error('Error validating cart:', error);
                            proceedCheckoutBtn.innerHTML = originalText;
                            proceedCheckoutBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                            proceedCheckoutBtn.style.pointerEvents = '';
                            alert('Error validating products. Please try again.');
                        }
                    });
                }

                // Function to show product availability errors
                function showProductAvailabilityErrors(unavailableItems, cart, packsMap) {
                    const errorContainer = document.getElementById('product-availability-errors');
                    if (!errorContainer || unavailableItems.length === 0) {
                        return;
                    }

                    let errorHTML = `
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-semibold text-red-800 mb-3">
                                        ⚠️ Some products are not available
                                    </h3>
                                    <div class="space-y-3">
                    `;

                    unavailableItems.forEach((unavailableItem) => {
                        // Find cart item by pack_id (there might be multiple items with same pack_id, get first match)
                        const cartItem = cart.find(item => item.pack_id === unavailableItem.pack_id);
                        const pack = packsMap[unavailableItem.pack_id];
                        const packName = pack?.name || unavailableItem.product_name || 'Unknown Product';
                        const itemId = cartItem?.id || `${unavailableItem.pack_id}-${Date.now()}`;

                        errorHTML += `
                            <div class="bg-white border border-red-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">${escapeHtml(packName)}</p>
                                        <p class="text-sm text-red-600 mt-1">${escapeHtml(unavailableItem.reason)}</p>
                                        ${unavailableItem.can_retry ? `
                                            <p class="text-xs text-gray-500 mt-2">
                                                💡 This offer may become available again in a few minutes. You can remove it now or wait and try again.
                                            </p>
                                        ` : `
                                            <p class="text-xs text-gray-500 mt-2">
                                                ⚠️ This product doesn't support the selected quantity. Please remove it to continue.
                                            </p>
                                        `}
                                    </div>
                                    <button 
                                        onclick="removeUnavailableItem('${itemId}', ${unavailableItem.pack_id})"
                                        class="ml-4 flex-shrink-0 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    errorHTML += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    errorContainer.innerHTML = errorHTML;
                    errorContainer.classList.remove('hidden');
                    
                    // Scroll to error container
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                // Helper function to escape HTML
                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                // Function to remove unavailable item from cart
                window.removeUnavailableItem = function(itemId, packId) {
                    if (typeof CartManager !== 'undefined' && CartManager.removeFromCart) {
                        // Remove by item ID if it exists, otherwise try to find by pack_id
                        if (itemId && CartManager.getCart().some(item => item.id === itemId)) {
                            CartManager.removeFromCart(itemId);
                        } else {
                            // Find all items with this pack_id and remove them
                            const cart = CartManager.getCart();
                            cart.filter(item => item.pack_id === packId).forEach(item => {
                                if (item.id) {
                                    CartManager.removeFromCart(item.id);
                                }
                            });
                        }
                        loadCart(); // Reload cart to update UI
                        
                        // Hide error container after removal
                        const errorContainer = document.getElementById('product-availability-errors');
                        if (errorContainer) {
                            errorContainer.classList.add('hidden');
                        }
                    }
                };
        
        // Load cart on page load
        loadCart();
    });
});
</script>
@endpush


