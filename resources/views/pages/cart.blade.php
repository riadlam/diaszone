@extends('layouts.app')

@section('title', 'Shopping Cart - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Shopping Cart</h1>
            <p class="text-sm text-gray-600">Review your order before checkout</p>
            <!-- Single Item Limit Notice -->
            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-blue-800">
                    <span class="font-semibold">Note:</span> Only one item can be in your cart at a time. Adding a new item will replace the current one.
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-6">Add items to your cart to continue</p>
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                        Continue Shopping
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
                        Proceed to Checkout
                    </a>
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
    // Enforce single-item cart limit - cleanup if multiple items exist
    (function enforceSingleItemCart() {
        const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
        if (Array.isArray(cart) && cart.length > 1) {
            console.log('Multiple items detected in cart, keeping only the newest item');
            // Sort by timestamp (newest first) or by id (higher = newer) and keep the newest
            const sorted = cart.sort((a, b) => {
                if (a.timestamp && b.timestamp) {
                    return new Date(b.timestamp) - new Date(a.timestamp);
                }
                // Fallback: use id (higher number = newer)
                return parseInt(b.id || 0) - parseInt(a.id || 0);
            });
            const newestItem = [sorted[0]];
            localStorage.setItem('diaszone_cart', JSON.stringify(newestItem));
        }
    })();
    
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
                // Enforce single-item limit: if multiple items, keep only the newest (last added)
                if (Array.isArray(parsed) && parsed.length > 1) {
                    // Sort by timestamp (newest first) or by id (higher = newer) and keep the newest
                    const sorted = parsed.sort((a, b) => {
                        if (a.timestamp && b.timestamp) {
                            return new Date(b.timestamp) - new Date(a.timestamp);
                        }
                        // Fallback: use id (higher number = newer)
                        return parseInt(b.id || 0) - parseInt(a.id || 0);
                    });
                    const newestItem = [sorted[0]];
                    localStorage.setItem('diaszone_cart', JSON.stringify(newestItem));
                    return newestItem;
                }
                return parsed;
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
                    if (!response.ok) throw new Error('Failed to fetch packs');
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
            let cart = CartManager.getCart();
            
            // Enforce single-item limit: if multiple items exist, keep only the newest (last added)
            if (Array.isArray(cart) && cart.length > 1) {
                console.log('Multiple items in cart detected, keeping only the newest item');
                // Sort by timestamp (newest first) or by id (higher = newer) and keep the newest
                const sorted = cart.sort((a, b) => {
                    if (a.timestamp && b.timestamp) {
                        return new Date(b.timestamp) - new Date(a.timestamp);
                    }
                    // Fallback: use id (higher number = newer)
                    return parseInt(b.id || 0) - parseInt(a.id || 0);
                });
                cart = [sorted[0]];
                localStorage.setItem('diaszone_cart', JSON.stringify(cart));
            }
            
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
            
            // Display cart items in same format as checkout
            // Only show first item if multiple exist (shouldn't happen, but safety check)
            const itemsToShow = cart.slice(0, 1);
            cartItemsList.innerHTML = itemsToShow.map((item, index) => {
                const pack = packsMap[item.pack_id];
                
                if (!pack) {
                    return `
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-red-500">Pack not found (ID: ${item.pack_id})</p>
                        </div>
                    `;
                }
                const quantity = 1;
                const unitPrice = parseFloat(pack.price);
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
                
                // Determine game type and currency
                const gameType = pack.game_type || 'mobilelegends';
                let currencyText = 'Diamonds';
                let gameName = 'Mobile Legends';
                let gameTitle = 'Mobile Legends Diamonds';
                
                if (gameType === 'freefire') {
                    currencyText = 'Diamonds';
                    gameName = 'Free Fire';
                    gameTitle = 'Free Fire Diamonds';
                } else if (gameType === 'pubgmobile') {
                    currencyText = 'UC';
                    gameName = 'PUBG Mobile';
                    gameTitle = 'PUBG Mobile UC';
                } else if (gameType === 'honorofkings') {
                    currencyText = 'Tokens';
                    gameName = 'Honor of Kings';
                    gameTitle = 'Honor of Kings Tokens';
                } else if (gameType === 'bloodstrike') {
                    currencyText = 'Golds';
                    gameName = 'Blood Strike';
                    gameTitle = 'Blood Strike Golds';
                }
                
                // Determine pack display name
                let packDisplayName = '';
                if (pack.name) {
                    packDisplayName = pack.name;
                } else {
                    packDisplayName = `${pack.diamonds} ${currencyText}`;
                }
                
                // Determine image
                let imageName = 'diaslow.webp';
                let imageUrl = '';
                let showImage = true;
                
                if (gameType === 'freefire') {
                    imageName = 'diamondslargefreefire.webp';
                } else if (gameType === 'pubgmobile') {
                    // No image for PUBG Mobile
                    showImage = false;
                    imageUrl = '';
                } else if (gameType === 'honorofkings' && pack.diamonds === 0) {
                    // No image for 0-token Honor of Kings packs
                    showImage = false;
                    imageUrl = '';
                } else if (gameType === 'honorofkings') {
                    // Honor of Kings specific images
                    if (pack.price === 0.96) {
                        imageName = 'weeklycard.webp';
                    } else if (pack.price === 2.99) {
                        imageName = 'weeklycardplus.webp';
                    } else if (pack.diamonds >= 800) {
                        imageName = 'diasbigbig.webp';
                    } else if (pack.diamonds >= 400) {
                        imageName = 'diaslarge.webp';
                    } else if (pack.diamonds >= 80) {
                        imageName = 'diasmid.webp';
                    } else {
                        imageName = 'diaslow.webp';
                    }
                } else if (gameType === 'bloodstrike') {
                    // Blood Strike - no specific images, use default logic or no image
                    showImage = false;
                    imageUrl = '';
                } else {
                    // Mobile Legends default logic
                    if (pack.diamonds >= 2000) {
                        imageName = 'diasbigbig.webp';
                    } else if (pack.diamonds >= 500) {
                        imageName = 'diaslarge.webp';
                    } else if (pack.diamonds >= 100) {
                        imageName = 'diasmid.webp';
                    }
                }
                
                if (showImage && !imageUrl) {
                    const baseUrl = window.location.origin;
                    imageUrl = baseUrl + '/storage/images_homepage/' + imageName;
                }
                
                // Determine order information fields based on game type
                let orderInfoHTML = '';
                if (gameType === 'bloodstrike') {
                    // Blood Strike: User ID and Server
                    const userIdBs = item.user_id_bs || '';
                    const serverBs = item.server_bs || 'Global';
                    orderInfoHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">User ID</label>
                                <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-gray-700 font-mono text-xs">
                                    ${userIdBs || 'N/A'}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Server</label>
                                <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-gray-700 font-mono text-xs">
                                    ${serverBs}
                                </div>
                            </div>
                        </div>
                    `;
                } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                    // Free Fire / PUBG Mobile / Honor of Kings: Player ID
                    const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || '';
                    orderInfoHTML = `
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Player ID</label>
                            <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-gray-700 font-mono text-xs">
                                ${playerId || 'N/A'}
                            </div>
                        </div>
                    `;
                } else {
                    // Mobile Legends: User ID and Zone ID
                    orderInfoHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">User ID</label>
                                <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-gray-700 font-mono text-xs">
                                    ${item.user_id || 'N/A'}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Zone ID</label>
                                <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-gray-700 font-mono text-xs">
                                    ${item.zone_id || 'N/A'}
                                </div>
                            </div>
                        </div>
                    `;
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
                        <div class="flex-shrink-0" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; display: flex !important; align-items: center; justify-content: center; background-color: #f9fafb; border-radius: 10px;">
                            ${showImage && imageUrl ? `
                                <img src="${imageUrl}" 
                                     alt="${packDisplayName}" 
                                     style="width: 100% !important; height: 100% !important; max-width: 56px !important; max-height: 56px !important; object-fit: contain !important; display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;"
                                     loading="lazy"
                                     decoding="async">
                            ` : `
                                <div style="width: 100%; height: 100%; background-color: #f9fafb;"></div>
                            `}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-medium text-gray-800 mb-1">${gameTitle}</h3>
                            <p class="text-sm text-gray-600 mb-0.5">${gameName} <span class="text-purple-600 font-medium">${packDisplayText}</span></p>
                            <p class="text-xs text-gray-500"><span class="text-purple-600">${packDisplayText}</span></p>
                        </div>
                        <button onclick="removeCartItem('${item.id}')" 
                                class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0"
                                title="Remove from cart">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Row 2: Discount, Quantity, and Price -->
                    <div class="flex items-center justify-between pb-5 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-4">
                            ${discountPercentage > 0 ? `<span class="text-xs font-medium text-purple-500">Discount: ${discountPercentage.toFixed(1)}%</span>` : ''}
                            <span class="text-xs text-gray-500">× ${quantity}</span>
                        </div>
                        <div class="text-base font-semibold text-purple-600">
                            US$ ${unitPrice.toFixed(2)}
                        </div>
                    </div>
                    
                    <!-- Row 3: Order Information (Dynamic based on game type) -->
                    ${orderInfoHTML}
                </div>
                `;
            }).join('');
            
            // Update payment details (same format as checkout)
            paymentDetails.innerHTML = `
            <div class="space-y-3 mb-5">
                <!-- Total Before Discounts -->
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">Total Before Discounts</span>
                    <span class="text-xs font-medium text-gray-700">US$ ${totalBeforeDiscount.toFixed(2)}</span>
                </div>
                
                <!-- Discount -->
                ${totalDiscount > 0 ? `
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-600">Discount</span>
                        <span class="text-xs font-medium text-red-500">- US$ ${totalDiscount.toFixed(2)}</span>
                    </div>
                ` : ''}
                
                <!-- Divider -->
                <div class="border-t border-gray-100 pt-3">
                    <!-- Total and Pay Now on same row -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-purple-600">Total</span>
                                <span class="text-base font-semibold text-purple-600">US$ ${totalAmount.toFixed(2)}</span>
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
                // Validate cart before redirecting
                const cart = CartManager.getCart();
                if (cart.length === 0) {
                    e.preventDefault();
                    alert('Your cart is empty. Please add items to your cart first.');
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
                        e.preventDefault();
                        alert('Some cart items are missing pack information. Please check your cart.');
                        return;
                    }
                    
                    const pack = packsMap[item.pack_id];
                    if (!pack) {
                        e.preventDefault();
                        alert('Some cart items have invalid pack information. Please check your cart.');
                        return;
                    }
                    
                    const gameType = pack.game_type || 'mobilelegends';
                    
                    // Validate based on game type
                    if (gameType === 'bloodstrike') {
                        if (!item.user_id_bs || !item.server_bs) {
                            e.preventDefault();
                            alert('Some cart items are missing required information (User ID or Server). Please check your cart.');
                            return;
                        }
                    } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                        const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok;
                        if (!playerId) {
                            e.preventDefault();
                            alert('Some cart items are missing required information (Player ID). Please check your cart.');
                            return;
                        }
                    } else {
                        // Mobile Legends
                        if (!item.user_id || !item.zone_id) {
                            e.preventDefault();
                            alert('Some cart items are missing required information (User ID or Zone ID). Please check your cart.');
                            return;
                        }
                    }
                }
                
                // Allow default link behavior (redirect to payment selection)
            });
        }
        
        // Load cart on page load
        loadCart();
    });
});
</script>
@endpush


