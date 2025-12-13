import './bootstrap';
import './slider';

// Pack Selection and Order Form Functionality
// Currency Utility Functions
window.CurrencyManager = {
    getCurrency: function() {
        return localStorage.getItem('diaszone_currency') || 'DZD';
    },
    
    formatPrice: function(priceUsd, priceDzd, discountPercentage = 0) {
        const currency = this.getCurrency();
        let price = currency === 'DZD' ? priceDzd : priceUsd;
        
        // Apply discount if provided
        if (discountPercentage > 0) {
            const discountAmount = (price * discountPercentage) / 100;
            price = price - discountAmount;
        }
        
        // Format based on currency
        if (currency === 'DZD') {
            return Math.round(price).toLocaleString() + ' DZD';
        } else {
            return '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    },
    
    getPriceValue: function(priceUsd, priceDzd, discountPercentage = 0) {
        const currency = this.getCurrency();
        let price = currency === 'DZD' ? priceDzd : priceUsd;
        
        if (discountPercentage > 0) {
            const discountAmount = (price * discountPercentage) / 100;
            price = price - discountAmount;
        }
        
        return currency === 'DZD' ? Math.round(price) : parseFloat(price).toFixed(2);
    },
    
    getCurrencySymbol: function() {
        return this.getCurrency() === 'DZD' ? 'DZD' : 'USD';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Legacy selectedPack variable for backward compatibility (not used in new multi-select flow)
    let selectedPack = null;
    
    const buyNowBtn = document.getElementById('buy-now-btn');
    const orderForm = document.getElementById('order-form');
    
    // Multi-select support: Update order form when checkboxes change
    // This is handled in diamond-packs.blade.php checkbox handlers, but we initialize here
    setTimeout(() => {
        // Update order form on page load to show any pre-selected items
        if (typeof updateOrderForm === 'function') {
            updateOrderForm();
        }
    }, 200);
    
    function updateOrderForm() {
        // Get all checked checkboxes (desktop and mobile) - deduplicate by pack_id
        const allChecked = Array.from(document.querySelectorAll('.pack-checkbox:checked, .mobile-pack-checkbox:checked'));
        
        // Deduplicate by pack_id - use a Set to track unique pack IDs
        const uniquePackIds = new Set();
        const checkedPacks = [];
        
        allChecked.forEach(checkbox => {
            const packId = parseInt(checkbox.dataset.packId);
            if (packId && !uniquePackIds.has(packId)) {
                uniquePackIds.add(packId);
                checkedPacks.push(checkbox);
            }
        });
        
        const totalPrice = document.getElementById('total-price');
        const diaszoneCredit = document.getElementById('diaszone-credit');
        const selectedPackInfo = document.getElementById('selected-pack-info');
        const selectedPacksList = document.getElementById('selected-packs-list');
        const selectedCountText = document.getElementById('selected-count-text');
        
        // Get selected currency
        let currency = localStorage.getItem('diaszone_currency');
        if (!currency && window.CurrencyManager) {
            currency = window.CurrencyManager.getCurrency();
        }
        if (!currency) {
            currency = 'DZD';
        }
        currency = currency.toUpperCase();
        
        if (checkedPacks.length === 0) {
            // Reset form if no packs selected
            if (totalPrice) {
                totalPrice.textContent = currency === 'DZD' ? '0 DZD' : 'US$ 0.00';
            }
            if (diaszoneCredit) diaszoneCredit.textContent = 'diaszone credit 0';
            if (selectedPackInfo) selectedPackInfo.classList.add('hidden');
            return;
        }
        
        // Get game type for currency text
        const orderFormWrapper = document.getElementById('order-form-wrapper');
        const gameType = orderFormWrapper ? orderFormWrapper.getAttribute('data-game-type') || 'mobilelegends' : 'mobilelegends';
        const translations = window.GameTranslations || {};
        const currencyText = gameType === 'pubgmobile' ? (translations.uc || 'UC') : (gameType === 'honorofkings' ? (translations.tokens || 'Tokens') : (gameType === 'bloodstrike' ? (translations.golds || 'Golds') : (translations.diamonds || 'Diamonds')));
        const bonusText = translations.bonus || 'Bonus';
        
        // Calculate totals across all selected packs
        let totalPriceAmount = 0;
        let totalCredits = 0;
        let totalItemsCount = 0; // Sum of all quantities
        
        const formatPrice = (price) => {
            const numPrice = parseFloat(price);
            if (isNaN(numPrice) || numPrice === null || numPrice === undefined) {
                return currency === 'DZD' ? '0 DZD' : 'US$ 0.00';
            }
            if (currency === 'DZD') {
                return `${Math.round(numPrice).toLocaleString()} DZD`;
            } else {
                return `US$ ${numPrice.toFixed(2)}`;
            }
        };
        
        // Build selected packs list HTML - checkedPacks is already deduplicated
        // First, sync quantity inputs from cart (cart is source of truth)
        const cart = window.CartManager ? window.CartManager.getCart() : [];
        checkedPacks.forEach(checkbox => {
            const packId = parseInt(checkbox.dataset.packId);
            if (!packId) return;
            
            const cartItem = cart.find(item => item.pack_id == packId);
            const quantity = cartItem ? (cartItem.quantity || 1) : 1;
            
            // Sync desktop quantity input
            const desktopInput = document.querySelector(`.quantity-input[data-pack-id="${packId}"]`);
            if (desktopInput) {
                desktopInput.value = quantity;
            }
            
            // Sync mobile quantity input
            const mobileInput = document.querySelector(`.mobile-quantity-input[data-pack-id="${packId}"]`);
            if (mobileInput) {
                mobileInput.value = quantity;
            }
        });
        
        const packsHTML = checkedPacks.map(checkbox => {
                const packId = parseInt(checkbox.dataset.packId);
                if (!packId) return ''; // Safety check
                
                // Always read quantity from cart (source of truth)
                const cartItem = cart.find(item => item.pack_id == packId);
                const quantity = cartItem ? (cartItem.quantity || 1) : 1;
            
            totalItemsCount += quantity;
            
            // Get pack data from checkbox data attributes
            const priceUsd = parseFloat(checkbox.dataset.packPriceUsd) || parseFloat(checkbox.dataset.packPrice) || 0;
            const priceDzd = parseFloat(checkbox.dataset.packPriceDzd) || (parseFloat(checkbox.dataset.packPrice) * 260) || 0;
            const discount = parseFloat(checkbox.dataset.packDiscount) || 0;
            const diamonds = parseInt(checkbox.dataset.packDiamonds) || 0;
            const bonus = parseInt(checkbox.dataset.packBonus) || 0;
            const packName = checkbox.dataset.packName || '';
            const packQuantity = parseInt(checkbox.dataset.packQuantity) || 1;
            
            // Calculate unit price (before discount)
            const unitBasePrice = currency === 'DZD' ? priceDzd : priceUsd;
            // Calculate unit price after discount
            const unitDiscountAmount = (unitBasePrice * discount) / 100;
            const unitPriceAfterDiscount = unitBasePrice - unitDiscountAmount;
            
            // Calculate total price for this item (unit price * quantity)
            const itemTotal = unitPriceAfterDiscount * quantity;
            
            // Calculate credits (based on USD)
            const usdUnitPrice = priceUsd;
            const usdUnitDiscount = (usdUnitPrice * discount) / 100;
            const usdUnitPriceAfterDiscount = usdUnitPrice - usdUnitDiscount;
            const usdItemTotal = usdUnitPriceAfterDiscount * quantity;
            const itemCredits = Math.round(usdItemTotal * 416);
            
            totalPriceAmount += itemTotal;
            totalCredits += itemCredits;
            
            // Format pack display name
            let displayName = '';
            if (packName && (packName.includes('Weekly Diamond Pass') || packName.includes('Event Topup'))) {
                const weeklyPassText = translations.weeklyDiamondPass || 'Weekly Diamond Pass';
                displayName = packQuantity > 1 ? `${packQuantity}x ${weeklyPassText}` : weeklyPassText;
            } else if (packName && packName.includes('Twilight Pass')) {
                displayName = translations.twilightPass || 'Twilight Pass';
            } else {
                if (bonus > 0) {
                    displayName = `${diamonds.toLocaleString()} ${currencyText} + ${bonus.toLocaleString()} ${bonusText}`;
                } else {
                    displayName = `${diamonds.toLocaleString()} ${currencyText}`;
                }
            }
            
            return `
                <div class="flex items-start justify-between text-xs py-2 border-b border-purple-100 last:border-0" data-pack-summary-id="${packId}">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="text-gray-700 font-medium">${displayName}</div>
                            <button type="button" 
                                    onclick="removePackFromSummary(${packId})"
                                    class="text-red-400 hover:text-red-600 transition-colors p-1 ml-2 flex-shrink-0"
                                    title="Remove pack">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-gray-500 text-[10px]">
                                ${formatPrice(unitPriceAfterDiscount)} ${discount > 0 ? `<span class="text-purple-500">(${discount}% off)</span>` : ''}
                            </div>
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-1 bg-purple-50 rounded px-1.5 py-0.5 border border-purple-200">
                                <button type="button" 
                                        onclick="decreasePackQuantity(${packId})"
                                        class="w-5 h-5 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-[10px] border border-purple-200 transition-colors"
                                        ${quantity <= 1 ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}
                                        title="Decrease">−</button>
                                <span class="w-6 text-center text-[10px] font-semibold text-gray-700">${quantity}</span>
                                <button type="button" 
                                        onclick="increasePackQuantity(${packId})"
                                        class="w-5 h-5 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-[10px] border border-purple-200 transition-colors"
                                        ${quantity >= 20 ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}
                                        title="Increase">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="text-purple-600 font-semibold ml-2 whitespace-nowrap text-right">
                        ${formatPrice(itemTotal)}
                    </div>
                </div>
            `;
        }).filter(html => html.trim() !== '').join(''); // Filter out empty strings to avoid duplicates
        
        // Update selected packs list
        if (selectedPacksList) {
            if (checkedPacks.length === 0 || packsHTML.trim() === '') {
                selectedPacksList.innerHTML = '<div class="text-xs text-gray-500 py-2 text-center">No packs selected</div>';
            } else {
                selectedPacksList.innerHTML = packsHTML;
            }
        }
        
        // Update count - show unique packs and total items
        if (selectedCountText) {
            if (checkedPacks.length === 0) {
                selectedCountText.textContent = '(0)';
            } else if (totalItemsCount > checkedPacks.length) {
                // Show both unique packs and total items if quantities > 1
                selectedCountText.textContent = `(${checkedPacks.length} pack${checkedPacks.length > 1 ? 's' : ''}, ${totalItemsCount} item${totalItemsCount > 1 ? 's' : ''})`;
            } else {
                selectedCountText.textContent = `(${checkedPacks.length} pack${checkedPacks.length > 1 ? 's' : ''})`;
            }
        }
        
        // Update total price
        if (totalPrice) {
            totalPrice.textContent = formatPrice(totalPriceAmount);
        }
        
        // Update DiasZone credit
        if (diaszoneCredit) {
            const creditText = translations.diaszoneCredit || 'diaszone credit';
            diaszoneCredit.textContent = `${creditText} ${totalCredits.toLocaleString()}`;
        }
        
        // Show/hide selected packs info
        if (selectedPackInfo) {
            if (checkedPacks.length === 0) {
                selectedPackInfo.classList.add('hidden');
            } else {
                selectedPackInfo.classList.remove('hidden');
            }
        }
    }
    
    // Make updateOrderForm globally available
    window.updateOrderForm = updateOrderForm;
    
    // Remove pack from summary (unchecks checkbox and removes from cart)
    window.removePackFromSummary = function(packId) {
        // Find and uncheck desktop checkbox
        const desktopCheckbox = document.querySelector(`#pack-checkbox-${packId}`);
        if (desktopCheckbox) {
            desktopCheckbox.checked = false;
            desktopCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Hide quantity control and reset visual state
            const desktopWrapper = desktopCheckbox.closest('.diamond-pack-item-wrapper');
            if (desktopWrapper) {
                const quantityControl = desktopWrapper.querySelector('.pack-quantity-control');
                const packCard = desktopWrapper.querySelector('.SKU_type');
                if (quantityControl) quantityControl.classList.add('hidden');
                if (packCard) {
                    packCard.classList.remove('border-purple-500', 'bg-purple-50/30');
                    packCard.classList.add('border-gray-200');
                }
            }
        }
        
        // Find and uncheck mobile checkbox
        const mobileCheckbox = document.querySelector(`#mobile-pack-checkbox-${packId}`);
        if (mobileCheckbox) {
            mobileCheckbox.checked = false;
            mobileCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Hide quantity control and reset visual state
            const mobileWrapper = mobileCheckbox.closest('.mobile-pack-item-wrapper');
            if (mobileWrapper) {
                const quantityControl = mobileWrapper.querySelector('.mobile-pack-quantity-control');
                const packItem = mobileWrapper.querySelector('.mobile-pack-item');
                const indicator = packItem?.querySelector('.mobile-pack-indicator');
                const checkIcon = packItem?.querySelector('.mobile-pack-indicator svg');
                
                if (quantityControl) quantityControl.classList.add('hidden');
                if (packItem) {
                    packItem.classList.remove('border-purple-500', 'bg-purple-50/50');
                    packItem.classList.add('border-gray-200');
                }
                if (indicator) {
                    indicator.classList.remove('bg-purple-600', 'border-purple-600');
                    indicator.classList.add('border-gray-300');
                }
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        }
        
        // Remove from cart
        const cart = window.CartManager ? window.CartManager.getCart() : [];
        const cartItem = cart.find(item => item.pack_id == packId);
        if (cartItem) {
            window.CartManager.removeFromCart(cartItem.id);
        }
        
        // Update order form
        if (typeof updateOrderForm === 'function') {
            updateOrderForm();
        }
        
        // Update mobile button text if function exists
        if (typeof updateMobileButtonText === 'function') {
            updateMobileButtonText();
        }
    };
    
    // Increase pack quantity in summary
    window.increasePackQuantity = function(packId) {
        // Find quantity input (desktop or mobile)
        let quantityInput = document.querySelector(`.quantity-input[data-pack-id="${packId}"]`);
        if (!quantityInput) {
            quantityInput = document.querySelector(`.mobile-quantity-input[data-pack-id="${packId}"]`);
        }
        
        if (!quantityInput) return;
        
        let currentQty = parseInt(quantityInput.value) || 1;
        if (currentQty >= 20) return;
        
        currentQty = Math.min(20, currentQty + 1);
        quantityInput.value = currentQty;
        
        // Update cart
        const cart = window.CartManager.getCart();
        const cartItem = cart.find(item => item.pack_id == packId);
        if (cartItem) {
            window.CartManager.updateQuantity(cartItem.id, currentQty);
        }
        
        // Update order form
        if (typeof updateOrderForm === 'function') {
            updateOrderForm();
        }
        
        // Update mobile button text if function exists
        if (typeof updateMobileButtonText === 'function') {
            updateMobileButtonText();
        }
    };
    
    // Decrease pack quantity in summary
    window.decreasePackQuantity = function(packId) {
        // Find quantity input (desktop or mobile)
        let quantityInput = document.querySelector(`.quantity-input[data-pack-id="${packId}"]`);
        if (!quantityInput) {
            quantityInput = document.querySelector(`.mobile-quantity-input[data-pack-id="${packId}"]`);
        }
        
        if (!quantityInput) return;
        
        let currentQty = parseInt(quantityInput.value) || 1;
        if (currentQty <= 1) {
            // If quantity would go to 0, remove the pack instead
            removePackFromSummary(packId);
            return;
        }
        
        currentQty = Math.max(1, currentQty - 1);
        quantityInput.value = currentQty;
        
        // Update cart
        const cart = window.CartManager.getCart();
        const cartItem = cart.find(item => item.pack_id == packId);
        if (cartItem) {
            window.CartManager.updateQuantity(cartItem.id, currentQty);
        }
        
        // Update order form
        if (typeof updateOrderForm === 'function') {
            updateOrderForm();
        }
        
        // Update mobile button text if function exists
        if (typeof updateMobileButtonText === 'function') {
            updateMobileButtonText();
        }
    };
    
    // Cart Management - Secure: Only stores pack_id, fetches data from server
    const CartManager = {
        packCache: {}, // Cache for pack data to avoid repeated API calls
        
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
            quantity = Math.max(1, Math.min(20, parseInt(quantity) || 1)); // Enforce 1-20 limit
            
            // Check if pack already exists in cart
            const existingIndex = cart.findIndex(cartItem => cartItem.pack_id === item.pack_id);
            
            if (existingIndex >= 0) {
                // Update existing item quantity
                cart[existingIndex].quantity = Math.max(1, Math.min(20, (cart[existingIndex].quantity || 1) + quantity));
                cart[existingIndex].quantity = Math.min(20, cart[existingIndex].quantity); // Cap at 20
            } else {
                // Add new item
                const cartItem = {
                    id: Date.now().toString() + '-' + Math.random().toString(36).substr(2, 9),
                    pack_id: item.pack_id,
                    quantity: quantity,
                    user_id: item.user_id || null,
                    zone_id: item.zone_id || null,
                    player_id: item.player_id || null,
                    player_id_ff: item.player_id_ff || null,
                    player_id_pubg: item.player_id_pubg || null,
                    player_id_hok: item.player_id_hok || null,
                    user_id_bs: item.user_id_bs || null,
                    server_bs: item.server_bs || null,
                    server: item.server || null,
                    timestamp: new Date().toISOString()
                };
                cart.push(cartItem);
            }
            
            localStorage.setItem('diaszone_cart', JSON.stringify(cart));
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
            this.packCache = {};
            this.updateCartUI();
        },
        
        // Fetch pack data from server
        fetchPacks: async function(packIds) {
            // Filter out already cached packs
            const uncachedIds = packIds.filter(id => !this.packCache[id]);
            
            if (uncachedIds.length === 0) {
                // All packs are cached, return them
                return packIds.map(id => this.packCache[id]).filter(Boolean);
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/api/packs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: uncachedIds })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch packs');
                }
                
                const data = await response.json();
                
                // Cache the fetched packs
                Object.keys(data.packs).forEach(id => {
                    this.packCache[id] = data.packs[id];
                });
                
                // Return all requested packs (cached + newly fetched)
                return packIds.map(id => this.packCache[id]).filter(Boolean);
            } catch (error) {
                console.error('Error fetching packs:', error);
                return [];
            }
        },
        
        updateCartUI: async function() {
            const cart = this.getCart();
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            const cartFooter = document.getElementById('cart-footer');
            
            // Update count
            if (cartCount) {
                cartCount.textContent = cart.length;
                if (cart.length === 0) {
                    cartCount.classList.add('hidden');
                } else {
                    cartCount.classList.remove('hidden');
                }
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
                    // Show skeleton loading
                    cartItems.innerHTML = cart.map(() => `
                        <div class="px-4 py-3 border-b border-gray-100 animate-pulse">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0 space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-32"></div>
                                    <div class="space-y-1">
                                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                                    </div>
                                </div>
                                <div class="w-5 h-5 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    `).join('');
                    
                    // Fetch pack data from server
                    const packIds = cart.map(item => item.pack_id).filter(Boolean);
                    const packs = await this.fetchPacks(packIds);
                    const packsMap = {};
                    packs.forEach(pack => {
                        packsMap[pack.id] = pack;
                    });
                    
                    // Get selected currency
                    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
                    
                    cartItems.innerHTML = cart.map(item => {
                        const packInfo = packsMap[item.pack_id];
                        if (!packInfo) {
                            return `
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm text-red-500">Pack not found (ID: ${item.pack_id})</p>
                                </div>
                            `;
                        }
                        
                        // Get price based on currency
                        const priceUsd = parseFloat(packInfo.price_usd || packInfo.price || 0);
                        const priceDzd = parseFloat(packInfo.price_dzd || (packInfo.price * 260) || 0);
                        const discount = parseFloat(packInfo.discount || 0);
                        
                        let price = currency === 'DZD' ? priceDzd : priceUsd;
                        if (discount > 0) {
                            const discountAmount = (price * discount) / 100;
                            price = price - discountAmount;
                        }
                        
                        // Multiply price by pack quantity (for special_quantity packs like weekly passes)
                        const packQty = packInfo.special_quantity || 1;
                        price = price * packQty;

                        // Format price
                        const formattedPrice = currency === 'DZD' 
                            ? Math.round(price).toLocaleString() + ' DZD'
                            : '$' + price.toFixed(2) + ' USD';
                        
                        // Determine pack display name
                        let packDisplayName = '';
                        if (packInfo.name) {
                            const translations = window.GameTranslations || {};
                            if (packInfo.name.includes('Weekly Diamond Pass') || packInfo.name.includes('Event Topup')) {
                                const qty = packInfo.special_quantity || 1;
                                const weeklyPassText = translations.weeklyDiamondPass || 'Weekly Diamond Pass';
                                packDisplayName = qty > 1 ? `${qty}x ${weeklyPassText}` : weeklyPassText;
                            } else if (packInfo.name.includes('Twilight Pass')) {
                                packDisplayName = translations.twilightPass || 'Twilight Pass';
                            } else {
                                packDisplayName = packInfo.name;
                            }
                        } else {
                            const gameType = packInfo.game_type || 'mobilelegends';
                            const currencyText = gameType === 'pubgmobile' ? (translations.uc || 'UC') : (gameType === 'honorofkings' ? (translations.tokens || 'Tokens') : (gameType === 'bloodstrike' ? (translations.golds || 'Golds') : (translations.diamonds || 'Diamonds')));
                            packDisplayName = `${packInfo.diamonds} ${currencyText}`;
                        }
                        
                        // Determine game type and display appropriate fields
                        const gameType = packInfo.game_type || 'mobilelegends';
                        let gameInfo = '';
                        
                        if (gameType === 'bloodstrike') {
                            // Blood Strike: User ID and Server
                            const userIdBs = item.user_id_bs || item.user_id;
                            const serverBs = item.server_bs || item.server;
                            if (userIdBs) gameInfo += `<p><span class="font-medium">User ID:</span> ${userIdBs}</p>`;
                            if (serverBs) gameInfo += `<p><span class="font-medium">Server:</span> ${serverBs}</p>`;
                        } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                            // Free Fire / PUBG Mobile / Honor of Kings: Player ID
                            const playerId = item.player_id_ff || item.player_id_pubg || item.player_id_hok || item.player_id;
                            if (playerId) gameInfo += `<p><span class="font-medium">Player ID:</span> ${playerId}</p>`;
                        } else {
                            // Mobile Legends: User ID and Zone ID
                            if (item.user_id) gameInfo += `<p><span class="font-medium">User ID:</span> ${item.user_id}</p>`;
                            if (item.zone_id) gameInfo += `<p><span class="font-medium">Zone ID:</span> ${item.zone_id}</p>`;
                        }
                        
                        return `
                            <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                            ${packDisplayName}${packInfo.bonus > 0 ? ` + ${packInfo.bonus} Bonus` : ''}
                                        </h4>
                                        <div class="text-xs text-gray-600 space-y-1">
                                            ${gameInfo}
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
    
    // Initialize cart UI
    CartManager.updateCartUI();
    
    // Cart dropdown hover functionality - DISABLED
    // Hover is disabled, cart dropdown will only show on click
    
    // Update cart dropdown prices when currency changes
    function updateCartDropdownPricesApp() {
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
        updateCartDropdownPricesApp();
        CartManager.updateCartUI(); // Refresh entire cart UI
        updateOrderForm(); // Update order form prices when currency changes
        // Also update mobile selected pack text if it exists
        const mobileSelectedPackText = document.getElementById('mobile-selected-pack-text');
        if (mobileSelectedPackText && selectedPack) {
            const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
            const basePrice = currency === 'DZD' 
                ? (selectedPack.price_dzd || (selectedPack.price * 260))
                : (selectedPack.price_usd || selectedPack.price);
            const discountAmount = (basePrice * selectedPack.discount) / 100;
            const priceAfterDiscount = basePrice - discountAmount;
            
            const orderFormWrapper = document.getElementById('order-form-wrapper');
            const gameType = orderFormWrapper ? orderFormWrapper.getAttribute('data-game-type') || 'mobilelegends' : 'mobilelegends';
            const translations = window.GameTranslations || {};
            const currencyText = gameType === 'pubgmobile' ? (translations.uc || 'UC') : (gameType === 'honorofkings' ? (translations.tokens || 'Tokens') : (gameType === 'bloodstrike' ? (translations.golds || 'Golds') : (translations.diamonds || 'Diamonds')));
            const bonusText = translations.bonus || 'Bonus';
            
            let packDisplayName = '';
            
            // Handle special pack names first
            if (selectedPack.name && (selectedPack.name.includes('Weekly Diamond Pass') || selectedPack.name.includes('Event Topup'))) {
                const packQuantity = selectedPack.quantity || 1;
                const weeklyPassText = translations.weeklyDiamondPass || 'Weekly Diamond Pass';
                packDisplayName = packQuantity > 1 ? `${packQuantity}x ${weeklyPassText}` : weeklyPassText;
            } else if (selectedPack.name && selectedPack.name.includes('Twilight Pass')) {
                packDisplayName = translations.twilightPass || 'Twilight Pass';
            } else {
                // Regular pack display
                if (selectedPack.bonus > 0) {
                    packDisplayName = `${selectedPack.diamonds} ${currencyText} + ${selectedPack.bonus} ${bonusText}`;
                } else {
                    packDisplayName = `${selectedPack.diamonds} ${currencyText}`;
                }
            }
            
            const priceText = currency === 'DZD' 
                ? `${Math.round(priceAfterDiscount).toLocaleString()} DZD`
                : `$${priceAfterDiscount.toFixed(2)} USD`;
            
            mobileSelectedPackText.innerHTML = `
                <span class="block text-sm font-semibold">${packDisplayName}</span>
                <span class="text-xs text-white/90 font-medium">${priceText}</span>
            `;
        }
    });
    
    // Make CartManager globally available
    window.CartManager = CartManager;
    
    // Show/hide "My Orders" button based on encrypted_order_ids in localStorage
    function updateMyOrdersButton() {
        const myOrdersBtn = document.getElementById('my-orders-btn');
        if (!myOrdersBtn) {
            return;
        }
        
        // Check for array of order IDs
        const orderIdsArrayStr = localStorage.getItem('diaszone_encrypted_order_ids');
        
        if (orderIdsArrayStr) {
            try {
                const parsed = JSON.parse(orderIdsArrayStr);
                
                if (Array.isArray(parsed) && parsed.length > 0) {
                    // Show button
                    myOrdersBtn.classList.remove('hidden');
                    myOrdersBtn.classList.add('flex');
                } else {
                    // Hide button if array is empty
                    myOrdersBtn.classList.add('hidden');
                    myOrdersBtn.classList.remove('flex');
                }
            } catch (e) {
                // Hide button if parsing fails
                myOrdersBtn.classList.add('hidden');
                myOrdersBtn.classList.remove('flex');
            }
        } else {
            // Hide button if no array exists
            myOrdersBtn.classList.add('hidden');
            myOrdersBtn.classList.remove('flex');
        }
    }
    
    // Make updateMyOrdersButton globally available
    window.updateMyOrdersButton = updateMyOrdersButton;
    
    // Update button on page load (with delay to ensure DOM is ready)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateMyOrdersButton);
    } else {
        // DOM is already ready
        updateMyOrdersButton();
    }
    
    // Also check after a short delay to catch any late-loading elements
    setTimeout(updateMyOrdersButton, 100);
    
    // Listen for storage changes (in case encrypted_order_ids is set/removed in another tab)
    window.addEventListener('storage', function(e) {
        if (e.key === 'diaszone_encrypted_order_ids') {
            updateMyOrdersButton();
        }
    });
    
    // Also check periodically (in case localStorage is modified by same-tab scripts)
    setInterval(updateMyOrdersButton, 1000);
    
    // Form validation and submission
    if (orderForm) {
        orderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            try {
                // Get all checked checkboxes (multi-offer support)
                const checkedPacks = Array.from(document.querySelectorAll('.pack-checkbox:checked, .mobile-pack-checkbox:checked'));
                
                if (checkedPacks.length === 0) {
                    alert('Please select at least one pack first');
                    return;
                }
                
                // Get game type from order form wrapper
                const orderFormWrapper = document.getElementById('order-form-wrapper');
                const gameType = orderFormWrapper ? orderFormWrapper.getAttribute('data-game-type') || 'mobilelegends' : 'mobilelegends';
                
                // Collect selected packs with quantities
                const selectedPacks = checkedPacks.map(checkbox => {
                    const packId = parseInt(checkbox.dataset.packId);
                    const quantityInput = document.querySelector(`.quantity-input[data-pack-id="${packId}"], .mobile-quantity-input[data-pack-id="${packId}"]`);
                    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
                    return { pack_id: packId, quantity: quantity };
                });
            
            // Flag to track if we're validating (prevents cart addition until validation succeeds)
            let isValidating = false;
            
            if (gameType === 'bloodstrike') {
                // Blood Strike - User ID and Server
                const userIdBsField = document.getElementById('user_id_bs');
                const serverBsField = document.getElementById('server_bs');
                
                if (!userIdBsField || !serverBsField) {
                    alert('Form fields not found. Please refresh the page.');
                    return;
                }
                
                const userIdBs = (userIdBsField && userIdBsField.value) ? userIdBsField.value.trim() : '';
                const serverBs = (serverBsField && serverBsField.value) ? serverBsField.value.trim() : '';
                
                if (!userIdBs || !serverBs) {
                    alert('Please enter your User ID and select a Server');
                    return;
                }
                
                if (!/^\d+$/.test(userIdBs)) {
                    alert('User ID must contain only numbers');
                    return;
                }
                
                // Store user info for all selected packs
                selectedPacks.forEach(pack => {
                    pack.user_id_bs = userIdBs;
                    pack.server_bs = serverBs;
                    pack.user_id = userIdBs;
                    pack.server = serverBs;
                });
                
                // Clear form
                userIdBsField.value = '';
                serverBsField.value = 'global';
                
            } else if (gameType === 'freefire' || gameType === 'pubgmobile' || gameType === 'honorofkings') {
                // Free Fire / PUBG Mobile / Honor of Kings - Player ID only
                const playerIdField = document.getElementById('player_id');
                
                if (!playerIdField) {
                    console.error('Player ID field not found for game type:', gameType);
                    alert('Player ID field not found. Please refresh the page.');
                    return;
                }
                
                // Safely get value
                let playerId = '';
                try {
                    playerId = playerIdField.value ? String(playerIdField.value).trim() : '';
                } catch (error) {
                    console.error('Error accessing player_id field value:', error);
                    alert('Error reading Player ID field. Please refresh the page.');
                    return;
                }
                
                if (!playerId) {
                    alert('Please enter your Player ID');
                    return;
                }
                
                if (!/^\d+$/.test(playerId)) {
                    alert('Player ID must contain only numbers');
                    return;
                }
                
                // Store player_id for all selected packs
                selectedPacks.forEach(pack => {
                    pack.player_id = playerId;
                    
                    // Store in game-specific field for clarity
                    if (gameType === 'freefire') {
                        pack.player_id_ff = playerId;
                    } else if (gameType === 'pubgmobile') {
                        pack.player_id_pubg = playerId;
                    } else if (gameType === 'honorofkings') {
                        pack.player_id_hok = playerId;
                    }
                });
                
                // Clear form
                try {
                    playerIdField.value = '';
                } catch (error) {
                    console.error('Error clearing player_id field:', error);
                }
                
            } else if (gameType === 'mobilelegends') {
                // Mobile Legends - User ID and Zone ID with nickname validation
                isValidating = true; // Set flag to prevent cart addition
                
                const userIdField = document.getElementById('user_id');
                const zoneIdField = document.getElementById('zone_id');
                const buyNowBtn = document.getElementById('buy-now-btn');
                
                if (!userIdField || !zoneIdField) {
                    alert('Form fields not found. Please refresh the page.');
                    isValidating = false;
                    return;
                }
                
                // Get form data
                const userId = userIdField.value.trim();
                const zoneId = zoneIdField.value.trim();
                
                // Basic validation
                if (!userId || !zoneId) {
                    showValidationError('Please enter both User ID and Zone ID');
                    isValidating = false;
                    return;
                }
                
                if (!/^\d+$/.test(userId) || !/^\d+$/.test(zoneId)) {
                    showValidationError('User ID and Zone ID must contain only numbers');
                    isValidating = false;
                    return;
                }
                
                // Disable button
                if (buyNowBtn) {
                    buyNowBtn.disabled = true;
                    buyNowBtn.textContent = 'Validating...';
                }
                
                // Call API to validate nickname
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch('/api/validate-nickname', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        zone_id: zoneId
                    })
                })
                .then(async (response) => {
                    let data = {};
                    try { data = await response.json(); } catch (e) { data = {}; }
                    return { ok: response.ok, status: response.status, data };
                })
                .then(({ok, status, data}) => {
                    isValidating = false;

                    if (!ok) {
                        if (buyNowBtn) {
                            buyNowBtn.disabled = false;
                            buyNowBtn.textContent = 'Buy Now';
                        }
                        const message = (data && data.message) ? data.message : 'Nickname validation failed. Please check your User ID and Zone ID.';
                        showValidationError(message);
                        return;
                    }
                    isValidating = false;
                    
                    if (buyNowBtn) {
                        buyNowBtn.disabled = false;
                        buyNowBtn.textContent = 'Buy Now';
                    }
                    
                    // Check if result is true and data contains nickname
                    if (data.result === true && data.data) {
                        const nickname = data.data;
                        
                        // Show success popup with nickname
                        showNicknameSuccess(nickname, () => {
                            // userId and zoneId are already trimmed strings from form validation above
                            // Ensure they're non-empty strings
                            if (!userId || !zoneId) {
                                console.error('userId or zoneId is missing after validation');
                                return;
                            }
                            
                            const validatedUserId = String(userId).trim();
                            const validatedZoneId = String(zoneId).trim();
                            
                            // DIRECTLY update localStorage - get fresh cart, update all matching items, save immediately
                            const cartJson = localStorage.getItem('diaszone_cart');
                            const cart = cartJson ? JSON.parse(cartJson) : [];
                            
                            // Get pack IDs from selected packs
                            const selectedPackIds = selectedPacks.map(pack => pack.pack_id);
                            
                            // Update ALL cart items that match selected pack IDs
                            let updated = false;
                            cart.forEach(cartItem => {
                                if (selectedPackIds.includes(cartItem.pack_id)) {
                                    cartItem.user_id = validatedUserId;
                                    cartItem.zone_id = validatedZoneId;
                                    updated = true;
                                }
                            });
                            
                            // Save updated cart to localStorage IMMEDIATELY
                            if (updated) {
                                localStorage.setItem('diaszone_cart', JSON.stringify(cart));
                                
                                // Verify save worked
                                const verifyCart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
                                console.log('Cart updated with user_id and zone_id:', verifyCart);
                            }
                            
                            // Update CartManager's internal state and UI
                            if (window.CartManager && window.CartManager.updateCartUI) {
                                window.CartManager.updateCartUI();
                            }
                            
                            // Clear form
                            userIdField.value = '';
                            zoneIdField.value = '';
                            
                            // Small delay to ensure localStorage save completes before redirect
                            setTimeout(() => {
                                window.location.href = '/cart';
                            }, 100);
                        });
                    } else {
                        // Validation failed - result is false
                        showValidationError('Please enter valid User ID and Zone ID to continue.');
                        // Do nothing - don't add to cart
                    }
                })
                .catch(error => {
                    isValidating = false;
                    console.error('Error validating nickname:', error);
                    
                    if (buyNowBtn) {
                        buyNowBtn.disabled = false;
                        buyNowBtn.textContent = 'Buy Now';
                    }
                    
                    showValidationError('Error validating nickname. Please try again.');
                    // Do nothing - don't add to cart
                });
                
                return; // Stop here - don't proceed to cart addition below
            } else {
                // Other games (fallback) - proceed directly without validation
                console.warn('Unknown game type:', gameType, '- proceeding without validation');
            }
            
            // Only add to cart if we're NOT validating (Mobile Legends validation happens async)
            if (!isValidating) {
                // For non-Mobile Legends games, proceed directly
                // Add all selected packs to cart (fields already added above)
                selectedPacks.forEach(pack => {
                    const cartItem = {
                        pack_id: pack.pack_id,
                        quantity: pack.quantity
                    };
                    // Copy game-specific fields that were added to pack object
                    if (pack.player_id) cartItem.player_id = pack.player_id;
                    if (pack.player_id_ff) cartItem.player_id_ff = pack.player_id_ff;
                    if (pack.player_id_pubg) cartItem.player_id_pubg = pack.player_id_pubg;
                    if (pack.player_id_hok) cartItem.player_id_hok = pack.player_id_hok;
                    if (pack.user_id_bs) {
                        cartItem.user_id_bs = pack.user_id_bs;
                        cartItem.user_id = pack.user_id;
                        cartItem.server_bs = pack.server_bs;
                        cartItem.server = pack.server;
                    }
                    CartManager.addToCart(cartItem, pack.quantity);
                });
                
                // Redirect to cart page
                window.location.href = '/cart';
            } else {
                console.log('Skipping cart addition - validation in progress');
            }
            } catch (error) {
                console.error('Error in form submission:', error);
                console.error('Error details:', {
                    message: error.message,
                    stack: error.stack
                });
                alert('An error occurred while adding to cart. Please try again or refresh the page.');
            }
        });
    }
    
    // Function to show validation error with proper UI/UX
    function showValidationError(message) {
        // Remove existing error messages
        const existingError = document.getElementById('nickname-validation-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.id = 'nickname-validation-error';
        errorDiv.className = 'mt-4 p-4 bg-red-50 border-2 border-red-200 rounded-lg animate-shake';
        errorDiv.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-red-800 mb-1">Validation Failed</p>
                    <p class="text-sm text-red-700">${message}</p>
                    <p class="text-xs text-red-600 mt-2 font-medium">Please enter valid User ID and Zone ID to continue.</p>
                </div>
                <button onclick="this.closest('#nickname-validation-error').remove()" 
                        class="text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        // Add shake animation style if not exists
        if (!document.getElementById('shake-animation-style')) {
            const style = document.createElement('style');
            style.id = 'shake-animation-style';
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
                .animate-shake {
                    animation: shake 0.5s ease-in-out;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Insert error message after a sensible container so it's visible on all pages
        // Prefer the specific storefront checkout form, then legacy order-form, then wrapper, else fallback to body
        const orderForm = document.getElementById('checkout-form') || document.getElementById('order-form') || document.getElementById('order-form-wrapper');
        if (orderForm) {
            // If the form is visible, append inside; otherwise fall back to body
            orderForm.appendChild(errorDiv);
        } else {
            document.body.appendChild(errorDiv);
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Auto-remove after 8 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 8000);
        }
    }
    
    // Function to show success popup with nickname
    function showNicknameSuccess(nickname, callback) {
        // Remove existing popups
        const existingPopup = document.getElementById('nickname-success-popup');
        if (existingPopup) {
            existingPopup.remove();
        }
        
        // Create success popup
        const popup = document.createElement('div');
        popup.id = 'nickname-success-popup';
        popup.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
        popup.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all animate-scale-in">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Nickname Verified</h3>
                    <p class="text-sm text-gray-600 mb-1">Your nickname:</p>
                    <p class="text-xl font-bold text-purple-600 mb-4">${nickname}</p>
                    <p class="text-xs text-gray-500">Adding to cart...</p>
                </div>
            </div>
        `;
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes scale-in {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            .animate-scale-in {
                animation: scale-in 0.2s ease-out;
            }
        `;
        if (!document.getElementById('nickname-popup-style')) {
            style.id = 'nickname-popup-style';
            document.head.appendChild(style);
        }
        
        // Add to body
        document.body.appendChild(popup);
        
        // Call callback after 1 second
        setTimeout(() => {
            popup.remove();
            if (callback) {
                callback();
            }
        }, 1000);
    }

// Expose helper functions to global window so inline page scripts can call them
try {
    window.showValidationError = showValidationError;
    window.showNicknameSuccess = showNicknameSuccess;
} catch (e) {
    // In environments where window is not available (e.g., SSR tests), ignore
}
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Skip if href is just "#" (invalid selector)
            if (href === '#' || !href || href.length <= 1) {
                return; // Let default behavior or other handlers take over
            }
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Debug: Check if sticky is working
    const orderWrapper = document.getElementById('order-form-wrapper');
    if (orderWrapper) {
        const styles = window.getComputedStyle(orderWrapper);
        console.log('=== STICKY DEBUG ===');
        console.log('Order form wrapper position:', styles.position);
        console.log('Order form wrapper top:', styles.top);
        
        // Check parent containers - this is critical for sticky
        let parent = orderWrapper.parentElement;
        let level = 1;
        while (parent && level < 6) {
            const parentStyles = window.getComputedStyle(parent);
            const overflow = parentStyles.overflow;
            const overflowX = parentStyles.overflowX;
            const overflowY = parentStyles.overflowY;
            const transform = parentStyles.transform;
            const position = parentStyles.position;
            
            console.log(`\nParent level ${level} (${parent.tagName}${parent.id ? '#' + parent.id : ''}${parent.className ? '.' + parent.className.split(' ').join('.') : ''}):`);
            console.log('  overflow:', overflow, overflow !== 'visible' ? '⚠️ PROBLEM!' : '✓');
            console.log('  overflowX:', overflowX, overflowX !== 'visible' ? '⚠️' : '✓');
            console.log('  overflowY:', overflowY, overflowY !== 'visible' && overflowY !== 'auto' ? '⚠️ PROBLEM!' : '✓');
            console.log('  transform:', transform, transform !== 'none' ? '⚠️ PROBLEM!' : '✓');
            console.log('  position:', position);
            console.log('  height:', parentStyles.height);
            console.log('  scrollHeight:', parent.scrollHeight);
            
            if (overflow !== 'visible' || (overflowX !== 'visible' && overflowX !== 'auto') || (overflowY !== 'visible' && overflowY !== 'auto' && overflowY !== 'scroll')) {
                console.error(`  ❌ BLOCKING STICKY: overflow is ${overflow} or overflowX/Y is not visible/auto`);
            }
            if (transform !== 'none') {
                console.error(`  ❌ BLOCKING STICKY: transform is ${transform}`);
            }
            
            parent = parent.parentElement;
            level++;
        }
        
        // Check if offers section has enough height
        const offersSection = document.getElementById('offers-section');
        if (offersSection) {
            const offersStyles = window.getComputedStyle(offersSection);
            console.log('\n=== OFFERS SECTION ===');
            console.log('Height:', offersStyles.height);
            console.log('ScrollHeight:', offersSection.scrollHeight);
            console.log('ClientHeight:', offersSection.clientHeight);
            console.log('OffsetHeight:', offersSection.offsetHeight);
        }
    }


    // Leave Review Button - Scroll to Review Form
    const leaveReviewBtn = document.getElementById('leave-review-btn');
    const reviewFormContainer = document.getElementById('review-form-container');
    const reviewListContainer = document.getElementById('review-list-container');

    if (leaveReviewBtn && reviewFormContainer) {
        leaveReviewBtn.addEventListener('click', () => {
            // Scroll the review list container to the bottom
            if (reviewListContainer) {
                reviewListContainer.scrollTo({
                    top: reviewListContainer.scrollHeight,
                    behavior: 'smooth'
                });
            } else {
                // Fallback: scroll the form into view
                reviewFormContainer.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
            
            // Focus on the name input
            const nameInput = document.getElementById('review-name');
            if (nameInput) {
                setTimeout(() => nameInput.focus(), 500);
            }
        });
    }

    // Star Rating Interaction
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('review-rating');
    let currentRating = 0;

    ratingStars.forEach((star, index) => {
        star.addEventListener('click', () => {
            currentRating = index + 1;
            if (ratingInput) {
                ratingInput.value = currentRating;
            }
            updateStarDisplay();
        });

        star.addEventListener('mouseenter', () => {
            highlightStars(index + 1);
        });
    });

    const ratingContainer = document.getElementById('rating-stars');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', () => {
            updateStarDisplay();
        });
    }

    function highlightStars(rating) {
        ratingStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    function updateStarDisplay() {
        highlightStars(currentRating);
    }

    // Review Form Submission
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const nameField = document.getElementById('review-name');
            const commentField = document.getElementById('review-comment');
            
            if (!nameField || !commentField || !ratingInput) {
                alert('Review form fields not found. Please refresh the page.');
                return;
            }
            
            const name = nameField.value ? nameField.value.trim() : '';
            const rating = ratingInput.value ? parseInt(ratingInput.value) : 0;
            const comment = commentField.value ? commentField.value.trim() : '';

            if (!name) {
                return;
            }

            if (rating === 0) {
                return;
            }

            if (!comment) {
                return;
            }

            // Here you would normally send the review to the server
            // Reset form after submission
            reviewForm.reset();
            currentRating = 0;
            ratingInput.value = '0';
            updateStarDisplay();
        });
    }

    // Header Dropdowns Functionality
    const languageDropdown = document.querySelector('.language-dropdown');
    const profileDropdown = document.querySelector('.profile-dropdown');
    const languageMenu = document.querySelector('.language-dropdown-menu');
    const profileMenu = document.querySelector('.profile-dropdown-menu');
    const languageButton = languageDropdown?.querySelector('button');
    const profileButton = profileDropdown?.querySelector('.profile-dropdown-btn');

    // Language Dropdown
    if (languageButton && languageMenu) {
        languageButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = languageMenu.classList.contains('opacity-100');
            
            // Close profile if open
            if (profileMenu) {
                profileMenu.classList.remove('opacity-100', 'visible');
                profileMenu.classList.add('opacity-0', 'invisible');
            }
            
            // Toggle language
            if (isOpen) {
                languageMenu.classList.remove('opacity-100', 'visible');
                languageMenu.classList.add('opacity-0', 'invisible');
                languageButton.classList.remove('dropdown-open');
            } else {
                languageMenu.classList.remove('opacity-0', 'invisible');
                languageMenu.classList.add('opacity-100', 'visible');
                languageButton.classList.add('dropdown-open');
            }
        });
    }

    // Profile Dropdown
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profileMenu.classList.contains('opacity-100');
            
            // Close language if open
            if (languageMenu) {
                languageMenu.classList.remove('opacity-100', 'visible');
                languageMenu.classList.add('opacity-0', 'invisible');
                languageButton.classList.remove('dropdown-open');
            }
            
            // Toggle profile
            if (isOpen) {
                profileMenu.classList.remove('opacity-100', 'visible');
                profileMenu.classList.add('opacity-0', 'invisible');
            } else {
                profileMenu.classList.remove('opacity-0', 'invisible');
                profileMenu.classList.add('opacity-100', 'visible');
            }
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (languageDropdown && !languageDropdown.contains(e.target)) {
            if (languageMenu) {
                languageMenu.classList.remove('opacity-100', 'visible');
                languageMenu.classList.add('opacity-0', 'invisible');
            }
            if (languageButton) {
                languageButton.classList.remove('dropdown-open');
            }
        }
        if (profileDropdown && !profileDropdown.contains(e.target)) {
            if (profileMenu) {
                profileMenu.classList.remove('opacity-100', 'visible');
                profileMenu.classList.add('opacity-0', 'invisible');
            }
        }
    });

    // Language dropdown items will navigate via href, no need for preventDefault
});
