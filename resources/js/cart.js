// Cart Management - Available on all pages
const CartManager = {
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
    
    addToCart: function(item) {
        // Single item limit: replace existing item if cart already has one
        const cartItem = {
            id: Date.now().toString(),
            user_id: item.user_id,
            zone_id: item.zone_id,
            pack: item.pack,
            timestamp: new Date().toISOString()
        };
        
        // Replace entire cart with single item
        const newCart = [cartItem];
        localStorage.setItem('diaszone_cart', JSON.stringify(newCart));
        this.updateCartUI();
        return cartItem;
    },
    
    removeFromCart: function(itemId) {
        const cart = this.getCart();
        const filtered = cart.filter(item => item.id !== itemId);
        localStorage.setItem('diaszone_cart', JSON.stringify(filtered));
        this.updateCartUI();
    },
    
    clearCart: function() {
        localStorage.removeItem('diaszone_cart');
        this.updateCartUI();
    },
    
    updateCartUI: async function() {
        const cart = this.getCart();
        const cartCount = document.getElementById('cart-count');
        const cartItems = document.getElementById('cart-items');
        const cartFooter = document.getElementById('cart-footer');
        
        // Get selected currency
        const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
        
        // Update count
        if (cartCount) {
            cartCount.textContent = cart.length;
            cartCount.classList.toggle('hidden', cart.length === 0);
        }
        
        // Update cart items display
        if (cartItems) {
            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="px-4 py-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">Your cart is empty</p>
                    </div>
                `;
                if (cartFooter) cartFooter.classList.add('hidden');
            } else {
                // Show loading state
                cartItems.innerHTML = cart.map(() => `
                    <div class="px-4 py-3 border-b border-gray-100 animate-pulse">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-32"></div>
                                <div class="space-y-1">
                                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                                    <div class="h-3 bg-gray-200 rounded w-16"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // Fetch pack data from server
                const packIds = cart.map(item => item.pack_id).filter(Boolean);
                let packsMap = {};
                
                if (packIds.length > 0) {
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
                        
                        if (response.ok) {
                            const data = await response.json();
                            packsMap = data.packs || {};
                        }
                    } catch (error) {
                        console.error('Error fetching packs for cart dropdown:', error);
                    }
                }
                
                cartItems.innerHTML = cart.map(item => {
                    const packInfo = packsMap[item.pack_id] || item.pack || {};
                    
                    // Get price based on currency
                    const priceUsd = parseFloat(packInfo.price_usd || packInfo.price || 0);
                    const priceDzd = parseFloat(packInfo.price_dzd || (packInfo.price * 260) || 0);
                    const discount = parseFloat(packInfo.discount || packInfo.discount_percentage || 0);
                    
                    let price = currency === 'DZD' ? priceDzd : priceUsd;
                    if (discount > 0) {
                        const discountAmount = (price * discount) / 100;
                        price = price - discountAmount;
                    }
                    
                    // Format price
                    const formattedPrice = currency === 'DZD' 
                        ? Math.round(price).toLocaleString() + ' DZD'
                        : '$' + price.toFixed(2) + ' USD';
                    
                    // Determine pack display name
                    let packDisplayName = '';
                    if (packInfo.name) {
                        if (packInfo.name.includes('Weekly Diamond Pass') || packInfo.name.includes('Event Topup')) {
                            packDisplayName = '1x Weekly Diamond Pass';
                        } else if (packInfo.name.includes('Twilight Pass')) {
                            packDisplayName = 'Twilight Pass';
                        } else {
                            packDisplayName = packInfo.name;
                        }
                    } else {
                        const gameType = packInfo.game_type || 'mobilelegends';
                        const currencyText = gameType === 'pubgmobile' ? 'UC' : (gameType === 'honorofkings' ? 'Tokens' : (gameType === 'bloodstrike' ? 'Golds' : 'Diamonds'));
                        const diamonds = packInfo.diamonds || 0;
                        packDisplayName = `${diamonds} ${currencyText}`;
                    }
                    
                    // Get game-specific fields
                    const gameType = packInfo.game_type || 'mobilelegends';
                    let orderInfoHTML = '';
                    
                    if (gameType === 'bloodstrike') {
                        orderInfoHTML = `
                            <p><span class="font-medium">User ID:</span> ${item.user_id_bs || 'N/A'}</p>
                            <p><span class="font-medium">Server:</span> ${item.server_bs || 'Global'}</p>
                        `;
                    } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                        const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || 'N/A';
                        orderInfoHTML = `<p><span class="font-medium">Player ID:</span> ${playerId}</p>`;
                    } else {
                        orderInfoHTML = `
                            <p><span class="font-medium">User ID:</span> ${item.user_id || 'N/A'}</p>
                            <p><span class="font-medium">Zone ID:</span> ${item.zone_id || 'N/A'}</p>
                        `;
                    }
                    
                    const bonus = packInfo.bonus || packInfo.bonus_diamonds || 0;
                    
                    return `
                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                        ${packDisplayName}${bonus > 0 ? ` + ${bonus} Bonus` : ''}
                                    </h4>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        ${orderInfoHTML}
                                        <p class="text-purple-600 font-semibold cart-item-price-dropdown" 
                                           data-price-usd="${priceUsd}" 
                                           data-price-dzd="${priceDzd}" 
                                           data-discount="${discount}">${formattedPrice}</p>
                                    </div>
                                </div>
                                <button onclick="CartManager.removeFromCart('${item.id}')" 
                                        class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
                if (cartFooter) cartFooter.classList.remove('hidden');
            }
        }
    }
};

// Initialize cart UI on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        CartManager.updateCartUI();
    });
} else {
    CartManager.updateCartUI();
}

// Cart dropdown hover functionality - DISABLED
// Hover is disabled, cart dropdown will only show on click

// Update cart prices when currency changes
function updateCartDropdownPrices() {
    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
    
    document.querySelectorAll('.cart-item-price-dropdown').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const discount = parseFloat(element.getAttribute('data-discount')) || 0;
        
        let price = currency === 'DZD' ? priceDzd : priceUsd;
        if (discount > 0) {
            const discountAmount = (price * discount) / 100;
            price = price - discountAmount;
        }
        
        element.textContent = currency === 'DZD' 
            ? Math.round(price).toLocaleString() + ' DZD'
            : '$' + price.toFixed(2) + ' USD';
    });
}

// Listen for currency changes
window.addEventListener('currencyChanged', function(e) {
    updateCartDropdownPrices();
    CartManager.updateCartUI(); // Refresh entire cart UI
});

// Make CartManager globally available
window.CartManager = CartManager;

