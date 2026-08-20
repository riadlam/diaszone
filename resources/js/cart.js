// Cart Management - Available on all pages
const CartManager = {
    getCart: function() {
        const cart = localStorage.getItem('diaszone_cart');
        const parsed = cart ? JSON.parse(cart) : [];
        // Ensure all items have quantity field (default to 1 for backward compatibility)
        return parsed.map(item => ({
            ...item,
            quantity: item.quantity || 1
        }));
    },
    
    addToCart: function(item, quantity = 1) {
        const cart = this.getCart();
        // Use item.quantity if provided, otherwise use the quantity parameter
        const finalQuantity = (item.quantity !== undefined && item.quantity !== null) 
            ? Math.max(1, Math.min(20, parseInt(item.quantity) || 1))
            : Math.max(1, Math.min(20, parseInt(quantity) || 1)); // Enforce 1-20 limit
        
        // Check if pack already exists in cart
        const existingIndex = cart.findIndex(cartItem => cartItem.pack_id === item.pack_id);
        
        if (existingIndex >= 0) {
            // Check if this is a nickname validation update (has user_id/zone_id or player_id)
            const isValidationUpdate = (item.user_id !== undefined && item.user_id !== null && item.user_id !== '') || 
                                      (item.zone_id !== undefined && item.zone_id !== null && item.zone_id !== '') ||
                                      (item.player_id !== undefined && item.player_id !== null && item.player_id !== '') ||
                                      (item.player_id_ff !== undefined && item.player_id_ff !== null && item.player_id_ff !== '') ||
                                      (item.player_id_pubg !== undefined && item.player_id_pubg !== null && item.player_id_pubg !== '') ||
                                      (item.player_id_hok !== undefined && item.player_id_hok !== null && item.player_id_hok !== '') ||
                                      (item.user_id_bs !== undefined && item.user_id_bs !== null && item.user_id_bs !== '') ||
                                      (item.save_id !== undefined && item.save_id !== null && item.save_id !== '');
            
            if (isValidationUpdate) {
                // Replace quantity when updating with validation data (nickname validation flow)
                cart[existingIndex].quantity = finalQuantity;
            } else {
                // Add to existing quantity when just adding more (checkbox selection flow)
                cart[existingIndex].quantity = Math.max(1, Math.min(20, (cart[existingIndex].quantity || 1) + finalQuantity));
                cart[existingIndex].quantity = Math.min(20, cart[existingIndex].quantity); // Cap at 20
            }
            
            // ALWAYS update game-specific IDs if the property exists on the item object
            // This ensures validation updates work even if values were previously null
            // Process save_id FIRST so it can set user_id for other games (Aether Gazer, etc.)
            if (item.save_id !== undefined) {
                const val = item.save_id;
                cart[existingIndex].save_id = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
                // Also update user_id if save_id is set (for compatibility with other games)
                if (cart[existingIndex].save_id) {
                    cart[existingIndex].user_id = cart[existingIndex].save_id;
                }
            }
            if ('user_id' in item) {
                const val = item.user_id;
                cart[existingIndex].user_id = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('zone_id' in item) {
                const val = item.zone_id;
                cart[existingIndex].zone_id = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('player_id' in item) {
                const val = item.player_id;
                cart[existingIndex].player_id = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('player_id_ff' in item) {
                const val = item.player_id_ff;
                cart[existingIndex].player_id_ff = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('player_id_pubg' in item) {
                const val = item.player_id_pubg;
                cart[existingIndex].player_id_pubg = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('player_id_hok' in item) {
                const val = item.player_id_hok;
                cart[existingIndex].player_id_hok = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('user_id_bs' in item) {
                const val = item.user_id_bs;
                cart[existingIndex].user_id_bs = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('server_bs' in item) {
                const val = item.server_bs;
                cart[existingIndex].server_bs = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
            if ('server' in item) {
                const val = item.server;
                cart[existingIndex].server = (val !== undefined && val !== null && val !== '') ? String(val).trim() : null;
            }
        } else {
            // Add new item
        const cartItem = {
                id: Date.now().toString() + '-' + Math.random().toString(36).substr(2, 9),
                pack_id: item.pack_id,
                quantity: finalQuantity,
                user_id: item.user_id || item.save_id || null, // Use save_id as fallback for user_id
                zone_id: item.zone_id || null,
                player_id: item.player_id || null,
                player_id_ff: item.player_id_ff || null,
                player_id_pubg: item.player_id_pubg || null,
                player_id_hok: item.player_id_hok || null,
                user_id_bs: item.user_id_bs || null,
                server_bs: item.server_bs || null,
                server: item.server || null,
                save_id: item.save_id || null,
            timestamp: new Date().toISOString()
        };
            // If save_id exists but user_id doesn't, set user_id = save_id
            if (cartItem.save_id && !cartItem.user_id) {
                cartItem.user_id = cartItem.save_id;
            }
            console.log('Creating new cart item:', cartItem);
            cart.push(cartItem);
        }
        
        // Save to localStorage - key is always 'diaszone_cart'
        console.log('Saving cart to localStorage:', cart);
        localStorage.setItem('diaszone_cart', JSON.stringify(cart));
        
        // Verify it was saved correctly
        const verify = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
        console.log('Verified cart in localStorage:', verify);
        
        this.updateCartUI();
        return cart;
    },
    
    updateQuantity: function(itemId, quantity) {
        const cart = this.getCart();
        quantity = Math.max(1, Math.min(20, parseInt(quantity) || 1)); // Enforce 1-20 limit
        
        const itemIndex = cart.findIndex(item => item.id === itemId);
        if (itemIndex >= 0) {
            if (quantity <= 0) {
                // Remove item if quantity is 0 or less
                cart.splice(itemIndex, 1);
            } else {
                cart[itemIndex].quantity = quantity;
            }
            localStorage.setItem('diaszone_cart', JSON.stringify(cart));
        this.updateCartUI();
        }
        return cart;
    },
    
    removeFromCart: function(itemId) {
        const cart = this.getCart();
        const filtered = cart.filter(item => item.id !== itemId);
        localStorage.setItem('diaszone_cart', JSON.stringify(filtered));
        this.updateCartUI();
        return filtered;
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
                    const priceDzd = parseFloat(packInfo.price_dzd || 0);
                    const discount = parseFloat(packInfo.discount || packInfo.discount_percentage || 0);
                    
                    let price = currency === 'DZD' ? priceDzd : priceUsd;
                    if (discount > 0) {
                        const discountAmount = (price * discount) / 100;
                        price = price - discountAmount;
                    }
                    
                    // Multiply price by pack quantity (special_quantity for weekly pass)
                    const packQty = packInfo.special_quantity || packInfo.specialQuantity || 1;
                    price = price * packQty;
                    // Format price
                    const formattedPrice = currency === 'DZD' 
                        ? Math.round(price).toLocaleString() + ' DZD'
                        : '$' + price.toFixed(2) + ' USD';
                    
                    // Determine pack display name
                    let packDisplayName = '';
                        if (packInfo.name) {
                        if (packInfo.name.includes('Weekly Diamond Pass') || packInfo.name.includes('Event Topup')) {
                            const qty = packInfo.special_quantity || packInfo.specialQuantity || 1;
                            packDisplayName = qty > 1 ? `${qty}x Weekly Diamond Pass` : 'Weekly Diamond Pass';
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
                    
                    if (gameType === 'mobilelegends') {
                        orderInfoHTML = `
                            <p><span class="font-medium">User ID:</span> ${item.user_id || 'N/A'}</p>
                            <p><span class="font-medium">Zone ID:</span> ${item.zone_id || 'N/A'}</p>
                        `;
                    } else if (gameType === 'bloodstrike') {
                        orderInfoHTML = `
                            <p><span class="font-medium">User ID:</span> ${item.user_id_bs || 'N/A'}</p>
                            <p><span class="font-medium">Server:</span> ${item.server_bs || 'Global'}</p>
                        `;
                    } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                        const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || item.player_id || item.save_id || 'N/A';
                        orderInfoHTML = `<p><span class="font-medium">Player ID:</span> ${playerId}</p>`;
                    } else {
                        // New games - User ID (save_id) and optionally Server
                        const userId = item.save_id || item.user_id || 'N/A';
                        orderInfoHTML = `<p><span class="font-medium">User ID:</span> ${userId}</p>`;
                        if (item.server) {
                            orderInfoHTML += `<p><span class="font-medium">Server:</span> ${item.server}</p>`;
                        }
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

