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
    
    updateCartUI: function() {
        const cart = this.getCart();
        const cartCount = document.getElementById('cart-count');
        const cartItems = document.getElementById('cart-items');
        const cartFooter = document.getElementById('cart-footer');
        
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
                cartItems.innerHTML = cart.map(item => {
                    const packInfo = item.pack;
                    return `
                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                        ${packInfo.diamonds} Diamonds + ${packInfo.bonus} Bonus
                                    </h4>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <p><span class="font-medium">User ID:</span> ${item.user_id}</p>
                                        <p><span class="font-medium">Zone ID:</span> ${item.zone_id}</p>
                                        <p class="text-purple-600 font-semibold">US$ ${parseFloat(packInfo.price).toFixed(2)}</p>
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

// Cart dropdown hover functionality
const cartDropdown = document.querySelector('.cart-dropdown');
const cartMenu = document.querySelector('.cart-dropdown-menu');

if (cartDropdown && cartMenu) {
    cartDropdown.addEventListener('mouseenter', () => {
        cartMenu.classList.remove('opacity-0', 'invisible');
        cartMenu.classList.add('opacity-100', 'visible');
    });
    
    cartDropdown.addEventListener('mouseleave', () => {
        cartMenu.classList.remove('opacity-100', 'visible');
        cartMenu.classList.add('opacity-0', 'invisible');
    });
}

// Make CartManager globally available
window.CartManager = CartManager;

