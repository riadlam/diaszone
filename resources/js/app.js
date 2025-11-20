import './bootstrap';
import './slider';

// Pack Selection and Order Form Functionality
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
            packName.textContent = `${selectedPack.diamonds} Diamonds + ${selectedPack.bonus} Bonus`;
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
            return cart ? JSON.parse(cart) : [];
        },
        
        addToCart: function(item) {
            const cart = this.getCart();
            const cartItem = {
                id: Date.now().toString(),
                user_id: item.user_id,
                zone_id: item.zone_id,
                pack_id: item.pack_id, // Only store pack ID, not full pack data
                timestamp: new Date().toISOString()
            };
            cart.push(cartItem);
            localStorage.setItem('diaszone_cart', JSON.stringify(cart));
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
                    
                    cartItems.innerHTML = cart.map(item => {
                        const packInfo = packsMap[item.pack_id];
                        if (!packInfo) {
                            return `
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm text-red-500">Pack not found (ID: ${item.pack_id})</p>
                                </div>
                            `;
                        }
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
    
    // Initialize cart UI
    CartManager.updateCartUI();
    
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
            
            const userId = document.getElementById('user_id').value.trim();
            const zoneId = document.getElementById('zone_id').value.trim();
            
            if (!userId || !zoneId) {
                return;
            }
            
            if (!selectedPack) {
                return;
            }
            
            // Validate User ID and Zone ID format (basic validation)
            if (!/^\d+$/.test(userId)) {
                return;
            }
            
            if (!/^\d+$/.test(zoneId)) {
                return;
            }
            
            // Add to cart - Only store pack_id for security
            const cartItem = CartManager.addToCart({
                user_id: userId,
                zone_id: zoneId,
                pack_id: selectedPack.id // Only store ID, not full pack data
            });
            
            // Reset form after adding to cart
            document.getElementById('user_id').value = '';
            document.getElementById('zone_id').value = '';
            
            // Optionally redirect to checkout
            // window.location.href = '/cart/order_checkout';
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
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

            const name = document.getElementById('review-name').value.trim();
            const rating = parseInt(ratingInput.value);
            const comment = document.getElementById('review-comment').value.trim();

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
