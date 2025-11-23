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
    let selectedPack = null;
    
    // Pack selection using radio buttons
    const packRadios = document.querySelectorAll('input[name="diamond_pack"]');
    const buyNowBtn = document.getElementById('buy-now-btn');
    const orderForm = document.getElementById('order-form');
    
    packRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                // Get pack data from radio button
                selectedPack = {
                    id: parseInt(radio.dataset.packId),
                    diamonds: parseInt(radio.dataset.packDiamonds),
                    bonus: parseInt(radio.dataset.packBonus),
                    price: parseFloat(radio.dataset.packPrice),
                    discount: parseFloat(radio.dataset.packDiscount) || 0
                };
                
                // Update order form
                updateOrderForm();
                
                // Enable buy button
                if (buyNowBtn) {
                    buyNowBtn.disabled = false;
                }
            }
        });
    });
    
    // Initialize with first selected radio
    const firstRadio = document.querySelector('input[name="diamond_pack"]:checked');
    if (firstRadio) {
        firstRadio.dispatchEvent(new Event('change'));
    }
    
    function updateOrderForm() {
        if (!selectedPack) {
            // Reset form if no pack selected
            const totalPrice = document.getElementById('total-price');
            const diaszoneCredit = document.getElementById('diaszone-credit');
            const selectedPackInfo = document.getElementById('selected-pack-info');
            
            if (totalPrice) totalPrice.textContent = 'US$ 0.00';
            if (diaszoneCredit) diaszoneCredit.textContent = 'diaszone credit 0';
            if (selectedPackInfo) selectedPackInfo.classList.add('hidden');
            return;
        }
        
        const totalPrice = document.getElementById('total-price');
        const diaszoneCredit = document.getElementById('diaszone-credit');
        const packName = document.getElementById('pack-name');
        const packPrice = document.getElementById('pack-price');
        const selectedPackInfo = document.getElementById('selected-pack-info');
        
        // Calculate price after discount
        const discountAmount = (selectedPack.price * selectedPack.discount) / 100;
        const priceAfterDiscount = selectedPack.price - discountAmount;
        
        // Calculate DiasZone Credits (1 USD = ~416 credits)
        const creditsMultiplier = 416;
        const calculatedCredits = Math.round(priceAfterDiscount * creditsMultiplier);
        
        // Update total price
        if (totalPrice) {
            totalPrice.textContent = `US$ ${priceAfterDiscount.toFixed(2)}`;
        }
        
        // Update DiasZone credit
        if (diaszoneCredit) {
            diaszoneCredit.textContent = `diaszone credit ${calculatedCredits.toLocaleString()}`;
        }
        
        // Update selected pack info
        if (packName) {
            // Get game type from order form wrapper
            const orderFormWrapper = document.getElementById('order-form-wrapper');
            const gameType = orderFormWrapper ? orderFormWrapper.getAttribute('data-game-type') || 'mobilelegends' : 'mobilelegends';
            const currencyText = gameType === 'pubgmobile' ? 'UC' : (gameType === 'honorofkings' ? 'Tokens' : (gameType === 'bloodstrike' ? 'Golds' : 'Diamonds'));
            
            if (selectedPack.bonus > 0) {
                packName.textContent = `${selectedPack.diamonds} ${currencyText} + ${selectedPack.bonus} Bonus`;
            } else {
                packName.textContent = `${selectedPack.diamonds} ${currencyText}`;
            }
        }
        
        if (packPrice) {
            packPrice.textContent = `US$ ${priceAfterDiscount.toFixed(2)}`;
        }
        
        if (selectedPackInfo) {
            selectedPackInfo.classList.remove('hidden');
        }
    }
    
    // Cart Management - Secure: Only stores pack_id, fetches data from server
    const CartManager = {
        packCache: {}, // Cache for pack data to avoid repeated API calls
        
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
                user_id: item.user_id || null,
                zone_id: item.zone_id || null,
                player_id: item.player_id || null,
                player_id_ff: item.player_id_ff || null,
                player_id_pubg: item.player_id_pubg || null,
                player_id_hok: item.player_id_hok || null,
                user_id_bs: item.user_id_bs || null,
                server_bs: item.server_bs || null,
                server: item.server || null,
                pack_id: item.pack_id, // Only store pack ID, not full pack data
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
                // Check if pack is selected first
                if (!selectedPack || !selectedPack.id) {
                    alert('Please select a pack first');
                    return;
                }
                
                // Get game type from order form wrapper
                const orderFormWrapper = document.getElementById('order-form-wrapper');
                const gameType = orderFormWrapper ? orderFormWrapper.getAttribute('data-game-type') || 'mobilelegends' : 'mobilelegends';
                
                // Handle different game types - get the correct fields
                let cartData = {
                    pack_id: selectedPack.id
                };
            
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
                
                cartData.user_id_bs = userIdBs;
                cartData.server_bs = serverBs;
                // Also store as user_id and server for backend compatibility
                cartData.user_id = userIdBs;
                cartData.server = serverBs;
                
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
                
                cartData.player_id = playerId;
                
                // Store in game-specific field for clarity
                if (gameType === 'freefire') {
                    cartData.player_id_ff = playerId;
                } else if (gameType === 'pubgmobile') {
                    cartData.player_id_pubg = playerId;
                } else if (gameType === 'honorofkings') {
                    cartData.player_id_hok = playerId;
                }
                
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
                .then(response => response.json())
                .then(data => {
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
                            // Add to cart
                            cartData.user_id = userId;
                            cartData.zone_id = zoneId;
                            
                            // Clear form
                            userIdField.value = '';
                            zoneIdField.value = '';
                            
                            // Add to cart and redirect
                            CartManager.addToCart(cartData);
                            window.location.href = '/cart';
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
                // Add to cart
                const cartItem = CartManager.addToCart(cartData);
                
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
        
        // Insert error message after the form
        const orderForm = document.getElementById('order-form');
        if (orderForm) {
            orderForm.appendChild(errorDiv);
            
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

    // Handle dropdown item clicks
    if (languageMenu) {
        languageMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const flag = link.querySelector('span').textContent;
                const code = link.querySelector('.ml-auto').textContent;
                languageButton.querySelector('span').textContent = flag;
                languageButton.querySelectorAll('span')[1].textContent = code;
                languageMenu.classList.remove('opacity-100', 'visible');
                languageMenu.classList.add('opacity-0', 'invisible');
            });
        });
    }
});
