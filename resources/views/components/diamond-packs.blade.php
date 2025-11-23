<div class="space-y-4" id="diamond-packs-wrapper">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 hidden lg:block">{{ $gameTitle ?? 'Diamond Packs' }}</h2>
    
    <!-- Desktop: Grid Layout (hidden on mobile) -->
    <div class="hidden lg:block" id="desktop-grid-wrapper">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($packs as $index => $pack)
            <label class="diamond-pack-item cursor-pointer">
                <input type="radio" 
                       name="diamond_pack" 
                       value="{{ $pack->id }}" 
                       class="hidden pack-radio"
                       {{ $index === 0 ? 'checked' : '' }}
                       data-pack-id="{{ $pack->id }}"
                       data-pack-diamonds="{{ $pack->diamonds }}"
                       data-pack-bonus="{{ $pack->bonus_diamonds }}"
                       data-pack-price="{{ $pack->price }}"
                       data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                       data-pack-price-dzd="{{ $pack->price_dzd ?? ($pack->price * 260) }}"
                       data-pack-name="{{ $pack->name }}"
                       data-pack-discount="{{ $pack->discount_percentage }}">
                
                <div class="SKU_type bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-purple-500 transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Image (empty space for PUBG Mobile, Honor of Kings, Blood Strike) -->
                        @if(($gameType ?? 'mobilelegends') === 'pubgmobile' || ($gameType ?? 'mobilelegends') === 'bloodstrike')
                            <!-- PUBG Mobile / Blood Strike: Empty space to maintain layout -->
                            <div class="flex-shrink-0 w-12 h-12"></div>
                        @elseif(($gameType ?? 'mobilelegends') === 'honorofkings')
                            <!-- Honor of Kings: Images from honorofkings folder (empty for 0 token packs) -->
                            @if($pack->diamonds == 0)
                                <!-- Empty space for packs with 0 tokens -->
                                <div class="flex-shrink-0 w-12 h-12"></div>
                            @else
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg">
                                    @php
                                        // Honor of Kings image selection based on token quantity
                                        // Available images: bigtoken.webp, laargetoken.webp, midtokne.webp, smalltoken.webp, weeklycard.webp, weeklycardplus.webp
                                        if ($pack->price == 2.99 && $pack->diamonds <= 2) {
                                            // Weekly Card Plus
                                            $imageName = 'honorofkings/weeklycardplus.webp';
                                        } elseif ($pack->price == 0.96 && $pack->diamonds <= 2) {
                                            // Weekly Card
                                            $imageName = 'honorofkings/weeklycard.webp';
                                        } elseif ($pack->diamonds >= 4000) {
                                            $imageName = 'honorofkings/bigtoken.webp';
                                        } elseif ($pack->diamonds >= 1200) {
                                            $imageName = 'honorofkings/laargetoken.webp';
                                        } elseif ($pack->diamonds >= 400) {
                                            $imageName = 'honorofkings/midtokne.webp';
                                        } elseif ($pack->diamonds >= 16) {
                                            $imageName = 'honorofkings/smalltoken.webp';
                                        } else {
                                            // Default for other special packs
                                            $imageName = 'honorofkings/weeklycard.webp';
                                        }
                                    @endphp
                                    <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                         alt="{{ $pack->diamonds }} Tokens" 
                                         class="w-full h-full object-contain"
                                         style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                                </div>
                            @endif
                        @else
                            <!-- Other Games: Diamond Image -->
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg">
                                @php
                                    $gameType = $gameType ?? 'mobilelegends';
                                    if ($gameType === 'freefire') {
                                        // Free Fire diamond images
                                        if ($pack->diamonds >= 5000) {
                                            $imageName = 'freefirelaaargediamonds.webp';
                                        } elseif ($pack->diamonds >= 2000) {
                                            $imageName = 'bigfreefirediamonds.webp';
                                        } elseif ($pack->diamonds >= 500) {
                                            $imageName = 'diamondslargefreefire.webp';
                                        } elseif ($pack->diamonds >= 100) {
                                            $imageName = 'diamondsmidfreefire.webp';
                                        } else {
                                            $imageName = 'diamondssmallfreefire.webp';
                                        }
                                    } else {
                                        // Mobile Legends (default)
                                        // Check for special passes first
                                        if (stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false) {
                                            $imageName = 'weeklymlbb.webp';
                                        } elseif (stripos($pack->name, 'Twilight Pass') !== false) {
                                            $imageName = 'twlilightpass.jpg';
                                        } else {
                                            // Regular diamond packs
                                            $imageName = 'diaslow.webp';
                                            if ($pack->diamonds >= 2000) {
                                                $imageName = 'diasbigbig.webp';
                                            } elseif ($pack->diamonds >= 500) {
                                                $imageName = 'diaslarge.webp';
                                            } elseif ($pack->diamonds >= 100) {
                                                $imageName = 'diasmid.webp';
                                            }
                                        }
                                    }
                                @endphp
                                <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                     alt="{{ $pack->diamonds }} Diamonds" 
                                     class="w-full h-full object-contain"
                                     style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                            </div>
                        @endif
                        
                        <!-- Pack Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    @if(($gameType ?? 'mobilelegends') === 'honorofkings')
                                        @if($pack->diamonds == 0)
                                            @if($pack->price == 0.32 && $pack->sort_order == 130)
                                                Double Token Lucky Bag
                                            @elseif($pack->price == 0.32 && $pack->sort_order == 140)
                                                Standard Purchase Rebate Pack
                                            @elseif($pack->price == 0.32 && $pack->sort_order == 160)
                                                Honor Point Value Pack
                                            @elseif($pack->price == 1.18)
                                                Premium Purchase Rebate Pack
                                            @else
                                                Special Pack
                                            @endif
                                        @elseif($pack->diamonds == 1 && $pack->price == 0.96)
                                            Weekly Card
                                        @elseif($pack->diamonds == 2 && $pack->price == 2.99)
                                            Weekly Card Plus
                                        @else
                                            {{ $pack->diamonds }} Tokens
                                        @endif
                                    @else
                                        @if(stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false)
                                            1x Weekly Diamond Pass
                                        @elseif(stripos($pack->name, 'Twilight Pass') !== false)
                                            Twilight Pass
                                        @else
                                            {{ $pack->diamonds }} {{ ($gameType ?? 'mobilelegends') === 'pubgmobile' ? 'UC' : (($gameType ?? 'mobilelegends') === 'honorofkings' ? 'Tokens' : (($gameType ?? 'mobilelegends') === 'bloodstrike' ? 'Golds' : 'Diamonds')) }}
                                        @endif
                                    @endif
                                </h3>
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded">{{ $pack->discount_percentage }}% OFF</span>
                                @endif
                            </div>
                            @if($pack->bonus_diamonds > 0)
                                <p class="text-xs text-gray-600 mb-2">+ {{ $pack->bonus_diamonds }} Bonus {{ ($gameType ?? 'mobilelegends') === 'pubgmobile' ? 'UC' : (($gameType ?? 'mobilelegends') === 'honorofkings' ? 'Tokens' : (($gameType ?? 'mobilelegends') === 'bloodstrike' ? 'Golds' : 'Diamonds')) }}</p>
                            @endif
                            <div class="flex items-center justify-between">
                                @php
                                    $priceUsd = $pack->price_usd ?? $pack->price;
                                    $priceDzd = $pack->price_dzd ?? ($pack->price * 260);
                                    $discount = $pack->discount_percentage ?? 0;
                                    $finalPriceUsd = $priceUsd * (1 - $discount / 100);
                                    $finalPriceDzd = $priceDzd * (1 - $discount / 100);
                                @endphp
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs text-gray-400 line-through pack-original-price" data-price-usd="{{ $priceUsd }}" data-price-dzd="{{ $priceDzd }}">{{ number_format($priceDzd, 0) }} DZD</span>
                                @endif
                                <span class="text-sm font-bold text-purple-600 pack-final-price" data-price-usd="{{ $priceUsd }}" data-price-dzd="{{ $priceDzd }}" data-discount="{{ $discount }}">{{ number_format($finalPriceDzd, 0) }} DZD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        @endforeach
        </div>
    </div>
</div>

<script>
// Currency price update function
window.updatePricesOnPage = function() {
    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
    
    // Update all pack prices
    document.querySelectorAll('.pack-final-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const discount = parseFloat(element.getAttribute('data-discount')) || 0;
        
        let price = currency === 'DZD' ? priceDzd : priceUsd;
        if (discount > 0) {
            const discountAmount = (price * discount) / 100;
            price = price - discountAmount;
        }
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    // Update original prices (strikethrough)
    document.querySelectorAll('.pack-original-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        
        const price = currency === 'DZD' ? priceDzd : priceUsd;
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    // Update mobile bottom sheet prices
    const selectedPackText = document.querySelector('#selected-pack-text');
    if (selectedPackText) {
        const packCard = document.querySelector('input[name="diamond_pack"]:checked');
        if (packCard) {
            const packPriceUsd = parseFloat(packCard.getAttribute('data-pack-price-usd')) || 0;
            const packPriceDzd = parseFloat(packCard.getAttribute('data-pack-price-dzd')) || 0;
            const packDiscount = parseFloat(packCard.getAttribute('data-pack-discount')) || 0;
            const packDiamonds = packCard.getAttribute('data-pack-diamonds');
            const packBonus = packCard.getAttribute('data-pack-bonus');
            const packName = packCard.closest('.SKU_type')?.querySelector('h3')?.textContent || '';
            
            let price = currency === 'DZD' ? packPriceDzd : packPriceUsd;
            if (packDiscount > 0) {
                const discountAmount = (price * packDiscount) / 100;
                price = price - discountAmount;
            }
            
            let packDisplayName = '';
            if (packName && (packName.includes('Weekly Diamond Pass') || packName.includes('Event Topup'))) {
                packDisplayName = '1x Weekly Diamond Pass';
            } else if (packName && packName.includes('Twilight Pass')) {
                packDisplayName = 'Twilight Pass';
            } else {
                const gameType = '{{ $gameType ?? "mobilelegends" }}';
                const currencyText = gameType === 'pubgmobile' ? 'UC' : 'Diamonds';
                const bonusText = parseInt(packBonus) > 0 ? ` + ${parseInt(packBonus).toLocaleString()} Bonus` : '';
                packDisplayName = `${parseInt(packDiamonds).toLocaleString()} ${currencyText}${bonusText}`;
            }
            
            const priceText = currency === 'DZD' 
                ? `${Math.round(price).toLocaleString()} DZD`
                : `$${parseFloat(price).toFixed(2)} USD`;
            
            selectedPackText.innerHTML = `
                <span class="block text-sm font-semibold">${packDisplayName}</span>
                <span class="text-xs text-white/90 font-medium">${priceText}</span>
            `;
        }
    }
    
    // Update mobile bottom sheet prices
    document.querySelectorAll('.mobile-pack-original-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        
        const price = currency === 'DZD' ? priceDzd : priceUsd;
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    document.querySelectorAll('.mobile-pack-final-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const discount = parseFloat(element.getAttribute('data-discount')) || 0;
        
        let price = currency === 'DZD' ? priceDzd : priceUsd;
        if (discount > 0) {
            const discountAmount = (price * discount) / 100;
            price = price - discountAmount;
        }
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
};

// Listen for currency changes
window.addEventListener('currencyChanged', function(e) {
    if (typeof updatePricesOnPage === 'function') {
        updatePricesOnPage();
    }
});

// Update prices on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updatePricesOnPage === 'function') {
        updatePricesOnPage();
    }
});

// Ensure desktop grid visibility (runs on all screens)
(function() {
    'use strict';
    
    function ensureDesktopGrid() {
        const desktopGrid = document.getElementById('desktop-grid-wrapper');
        if (desktopGrid) {
            // Let Tailwind classes handle it - just ensure no inline display style interferes
            if (window.innerWidth >= 1024) {
                desktopGrid.style.display = '';
            } else {
                desktopGrid.style.display = 'none';
            }
        }
    }
    
    window.addEventListener('load', ensureDesktopGrid);
    window.addEventListener('resize', ensureDesktopGrid);
    // Run immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureDesktopGrid);
    } else {
        ensureDesktopGrid();
    }
})();

// Mobile-only bottom sheet logic - completely separate from desktop
(function() {
    'use strict';
    
    // Check if we're on mobile (only run if mobile elements exist and screen is mobile)
    function isMobile() {
        const mobileBtn = document.getElementById('mobile-select-pack-btn');
        if (!mobileBtn) return false;
        // Check if mobile button is visible (not hidden by lg:hidden)
        const styles = window.getComputedStyle(mobileBtn);
        return styles.display !== 'none';
    }
    
    // Wait for DOM to be ready
    function initBottomSheet() {
        // Double check we're still on mobile
        if (!isMobile()) {
            return;
        }
        
        const selectPackBtn = document.getElementById('mobile-select-pack-btn');
        const closeBottomSheetBtn = document.getElementById('close-bottom-sheet-btn');
        const bottomSheet = document.getElementById('mobile-pack-bottom-sheet');
        const bottomSheetOverlay = document.getElementById('bottom-sheet-overlay');
        const mobilePackItems = document.querySelectorAll('.mobile-pack-item');
        const selectedPackText = document.getElementById('mobile-selected-pack-text');
        
        if (!selectPackBtn || !bottomSheet) {
            console.log('Bottom sheet elements not found, retrying...', {
                selectPackBtn: !!selectPackBtn,
                bottomSheet: !!bottomSheet
            });
            // Try again after a short delay in case elements load late
            setTimeout(initBottomSheet, 100);
            return;
        }
        
        console.log('Bottom sheet initialized successfully');
        
        // Ensure bottom sheet starts closed
        if (bottomSheet) {
            bottomSheet.style.transform = 'translateY(100%)';
            bottomSheet.style.display = 'none'; // Keep it hidden until button is clicked
            bottomSheet.style.zIndex = '9999';
            bottomSheet.style.backgroundColor = '#ffffff';
            bottomSheet.style.opacity = '1';
            bottomSheet.style.visibility = 'visible';
        }
        
        // Ensure scrollable content can scroll
        const scrollableContent = bottomSheet ? bottomSheet.querySelector('.overflow-y-auto') : null;
        if (scrollableContent) {
            // Prevent event propagation that might block scrolling
            scrollableContent.addEventListener('touchmove', function(e) {
                // Allow scrolling within the content area
                e.stopPropagation();
            }, { passive: true });
            
            scrollableContent.addEventListener('wheel', function(e) {
                // Allow wheel scrolling
                e.stopPropagation();
            }, { passive: true });
        }
        if (bottomSheetOverlay) {
            bottomSheetOverlay.style.display = 'none';
            bottomSheetOverlay.classList.add('hidden');
        }
        
        // Open bottom sheet
        function openBottomSheet() {
            console.log('Opening bottom sheet...', {
                bottomSheet: !!bottomSheet,
                bottomSheetOverlay: !!bottomSheetOverlay,
                bottomSheetElement: bottomSheet
            });
            
            if (!bottomSheet) {
                console.error('Bottom sheet element not found!');
                return;
            }
            
            // Show overlay first
            if (bottomSheetOverlay) {
                bottomSheetOverlay.style.display = 'block';
                bottomSheetOverlay.style.zIndex = '9998';
                bottomSheetOverlay.classList.remove('hidden');
            }
            
            // Force display to flex and remove any hidden classes
            bottomSheet.style.display = 'flex';
            bottomSheet.style.zIndex = '9999';
            bottomSheet.style.backgroundColor = '#ffffff';
            bottomSheet.style.opacity = '1';
            bottomSheet.style.visibility = 'visible';
            bottomSheet.style.pointerEvents = 'auto';
            bottomSheet.style.position = 'fixed';
            bottomSheet.style.width = '100%';
            bottomSheet.style.left = '0';
            bottomSheet.style.right = '0';
            bottomSheet.style.bottom = '0';
            // Remove any classes that might hide it
            bottomSheet.classList.remove('hidden', 'lg:hidden');
            
            // Use requestAnimationFrame to ensure the display change is applied before transform
            requestAnimationFrame(() => {
                // Force visibility with multiple methods BEFORE transform
                bottomSheet.style.display = 'flex';
                bottomSheet.style.visibility = 'visible';
                bottomSheet.style.opacity = '1';
                bottomSheet.style.pointerEvents = 'auto';
                
                // Small delay to ensure display is applied
                setTimeout(() => {
                    // Force transform to 0 using !important via setProperty
                    bottomSheet.style.setProperty('transform', 'translateY(0)', 'important');
                    bottomSheet.style.setProperty('-webkit-transform', 'translateY(0)', 'important');
                    
                    // Also remove the translate-y-full class if it exists
                    bottomSheet.classList.remove('translate-y-full');
                    
                    // Check computed styles
                    const computedStyle = window.getComputedStyle(bottomSheet);
                    const rect = bottomSheet.getBoundingClientRect();
                    console.log('Bottom sheet opened', {
                        display: bottomSheet.style.display,
                        computedDisplay: computedStyle.display,
                        transform: bottomSheet.style.transform,
                        computedTransform: computedStyle.transform,
                        zIndex: bottomSheet.style.zIndex,
                        computedZIndex: computedStyle.zIndex,
                        backgroundColor: bottomSheet.style.backgroundColor,
                        computedBackgroundColor: computedStyle.backgroundColor,
                        visibility: bottomSheet.style.visibility,
                        computedVisibility: computedStyle.visibility,
                        opacity: bottomSheet.style.opacity,
                        computedOpacity: computedStyle.opacity,
                        height: computedStyle.height,
                        width: computedStyle.width,
                        top: computedStyle.top,
                        bottom: computedStyle.bottom,
                        left: computedStyle.left,
                        right: computedStyle.right,
                        position: computedStyle.position,
                        rect: rect,
                        viewportHeight: window.innerHeight,
                        isVisible: rect.top < window.innerHeight && rect.bottom > 0
                    });
                    
                    // If still not visible or transform is wrong, force correct position
                    if (rect.top >= window.innerHeight || rect.bottom <= 0 || computedStyle.transform.includes('737')) {
                        console.warn('Bottom sheet transform issue detected, forcing correct position...', {
                            computedTransform: computedStyle.transform,
                            rectTop: rect.top,
                            viewportHeight: window.innerHeight
                        });
                        // Force the transform to be exactly 0px
                        bottomSheet.style.setProperty('transform', 'translateY(0px)', 'important');
                        bottomSheet.style.setProperty('-webkit-transform', 'translateY(0px)', 'important');
                        // Also ensure bottom is 0
                        bottomSheet.style.setProperty('bottom', '0', 'important');
                        bottomSheet.style.setProperty('top', 'auto', 'important');
                        // Force a reflow
                        void bottomSheet.offsetHeight;
                        // Check again
                        const newRect = bottomSheet.getBoundingClientRect();
                        const newComputed = window.getComputedStyle(bottomSheet);
                        console.log('After fix attempt', {
                            newTransform: newComputed.transform,
                            newRect: newRect,
                            isNowVisible: newRect.top < window.innerHeight && newRect.bottom > 0
                        });
                    }
                    
                    // Check if content is visible
                    const content = bottomSheet.querySelector('.overflow-y-auto');
                    if (content) {
                        console.log('Bottom sheet content found', {
                            contentHeight: content.offsetHeight,
                            contentScrollHeight: content.scrollHeight,
                            contentDisplay: window.getComputedStyle(content).display,
                            packItems: document.querySelectorAll('.mobile-pack-item').length,
                            contentRect: content.getBoundingClientRect()
                        });
                    } else {
                        console.error('Bottom sheet content not found!');
                    }
                }, 10);
            });
            
            // Prevent body scroll (but allow bottom sheet to scroll)
            const scrollY = window.scrollY;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.top = `-${scrollY}px`;
            
            // Ensure scrollable area can scroll
            const scrollableContent = bottomSheet.querySelector('.overflow-y-auto');
            if (scrollableContent) {
                scrollableContent.style.overflowY = 'auto';
                scrollableContent.style.webkitOverflowScrolling = 'touch';
            } else {
                console.warn('Scrollable content not found in bottom sheet');
            }
        }
        
        // Close bottom sheet
        function closeBottomSheet() {
            if (bottomSheet) {
                bottomSheet.style.transform = 'translateY(100%)';
                // Hide after transition completes
                setTimeout(() => {
                    if (bottomSheet.style.transform === 'translateY(100%)' || bottomSheet.style.transform.includes('100%')) {
                        bottomSheet.style.display = 'none';
                    }
                }, 300); // Match transition duration
            }
            if (bottomSheetOverlay) {
                bottomSheetOverlay.style.display = 'none';
                bottomSheetOverlay.classList.add('hidden');
            }
            // Restore body scroll
            const scrollY = document.body.style.top;
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.top = '';
            if (scrollY) {
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }
        
        // Event listeners
        if (selectPackBtn) {
            selectPackBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Select Pack button clicked');
                openBottomSheet();
            });
        } else {
            console.error('Select Pack button not found!');
        }
        
        if (closeBottomSheetBtn) {
            closeBottomSheetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeBottomSheet();
            });
        }
        
        if (bottomSheetOverlay) {
            bottomSheetOverlay.addEventListener('click', function(e) {
                // Only close if clicking directly on overlay, not if event came from bottom sheet
                if (e.target === bottomSheetOverlay) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeBottomSheet();
                }
            });
            
            // Prevent overlay from blocking touch events on bottom sheet
            bottomSheetOverlay.addEventListener('touchmove', function(e) {
                // If touch is on bottom sheet, don't prevent default
                const touch = e.touches[0];
                if (touch && bottomSheet) {
                    const rect = bottomSheet.getBoundingClientRect();
                    if (touch.clientY >= rect.top) {
                        e.stopPropagation();
                    }
                }
            }, { passive: true });
        }
        
        // Handle pack selection from mobile bottom sheet
        mobilePackItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const packId = this.dataset.packId;
                const packDiamonds = this.dataset.packDiamonds;
                const packBonus = this.dataset.packBonus;
                const packPrice = this.dataset.packPrice;
                const packPriceUsd = this.dataset.packPriceUsd || packPrice;
                const packPriceDzd = this.dataset.packPriceDzd || (packPrice * 260);
                const packName = this.dataset.packName || '';
                const packDiscount = this.dataset.packDiscount || 0;
                
                // Remove previous selection indicator
                mobilePackItems.forEach(packItem => {
                    const indicator = packItem.querySelector('.mobile-pack-indicator');
                    const checkIcon = packItem.querySelector('.mobile-pack-indicator svg');
                    if (indicator) {
                        indicator.classList.remove('bg-purple-600', 'border-purple-600');
                        indicator.classList.add('border-gray-300');
                    }
                    if (checkIcon) {
                        checkIcon.classList.add('hidden');
                    }
                    packItem.classList.remove('border-purple-500', 'bg-purple-50');
                    packItem.classList.add('border-gray-200');
                });
                
                // Add selection indicator to clicked item
                const indicator = this.querySelector('.mobile-pack-indicator');
                const checkIcon = this.querySelector('.mobile-pack-indicator svg');
                if (indicator) {
                    indicator.classList.remove('border-gray-300');
                    indicator.classList.add('bg-purple-600', 'border-purple-600');
                }
                if (checkIcon) {
                    checkIcon.classList.remove('hidden');
                }
                this.classList.remove('border-gray-200');
                this.classList.add('border-purple-500', 'bg-purple-50/50');
                
                // Update mobile button text
                const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
                let price = currency === 'DZD' ? parseFloat(packPriceDzd) : parseFloat(packPriceUsd);
                const discountAmount = (price * parseFloat(packDiscount)) / 100;
                const priceAfterDiscount = price - discountAmount;
                
                if (selectedPackText) {
                    let packDisplayName = '';
                    if (packName && (packName.includes('Weekly Diamond Pass') || packName.includes('Event Topup'))) {
                        packDisplayName = '1x Weekly Diamond Pass';
                    } else if (packName && packName.includes('Twilight Pass')) {
                        packDisplayName = 'Twilight Pass';
                    } else {
                        const gameType = '{{ $gameType ?? "mobilelegends" }}';
                        const currencyText = gameType === 'pubgmobile' ? 'UC' : 'Diamonds';
                        const bonusText = parseInt(packBonus) > 0 ? ` + ${parseInt(packBonus).toLocaleString()} Bonus` : '';
                        packDisplayName = `${parseInt(packDiamonds).toLocaleString()} ${currencyText}${bonusText}`;
                    }
                    
                    const priceText = currency === 'DZD' 
                        ? `${Math.round(priceAfterDiscount).toLocaleString()} DZD`
                        : `$${priceAfterDiscount.toFixed(2)} USD`;
                    
                    selectedPackText.innerHTML = `
                        <span class="block text-sm font-semibold">${packDisplayName}</span>
                        <span class="text-xs text-white/90 font-medium">${priceText}</span>
                    `;
                }
                
                // Update the hidden radio button (for form submission and desktop JS compatibility)
                // This triggers the existing app.js logic to update the order form
                const radio = document.querySelector(`input[name="diamond_pack"][value="${packId}"]`);
                if (radio) {
                    radio.checked = true;
                    // Trigger change event to update order form via existing app.js
                    const changeEvent = new Event('change', { bubbles: true });
                    radio.dispatchEvent(changeEvent);
                }
                
                // Close bottom sheet with slight delay for visual feedback
                setTimeout(() => {
                    closeBottomSheet();
                }, 200);
            });
        });
    }
    
    // Initialize when DOM is ready - try multiple times to catch late-loading elements
    function startInit() {
        if (isMobile()) {
            initBottomSheet();
        } else {
            // If not mobile yet, try again after a delay
            setTimeout(startInit, 200);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInit);
    } else {
        startInit();
    }
    
    // Also try after delays in case elements load late (button is in different file)
    setTimeout(startInit, 200);
    setTimeout(startInit, 500);
    setTimeout(startInit, 1000);
})();
</script>

