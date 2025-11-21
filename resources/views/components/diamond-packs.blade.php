<div class="space-y-4" id="diamond-packs-wrapper">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 hidden lg:block">Diamond Packs</h2>
    
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
                       data-pack-discount="{{ $pack->discount_percentage }}">
                
                <div class="SKU_type bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-purple-500 transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Diamond Image -->
                        <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg">
                            @php
                                $imageName = 'diaslow.webp';
                                if ($pack->diamonds >= 2000) {
                                    $imageName = 'diasbigbig.webp';
                                } elseif ($pack->diamonds >= 500) {
                                    $imageName = 'diaslarge.webp';
                                } elseif ($pack->diamonds >= 100) {
                                    $imageName = 'diasmid.webp';
                                }
                            @endphp
                            <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                 alt="{{ $pack->diamonds }} Diamonds" 
                                 class="w-full h-full object-contain"
                                 style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                        </div>
                        
                        <!-- Pack Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $pack->diamonds }} Diamonds</h3>
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded">{{ $pack->discount_percentage }}% OFF</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mb-2">+ {{ $pack->bonus_diamonds }} Bonus Diamonds</p>
                            <div class="flex items-center justify-between">
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs text-gray-400 line-through">US$ {{ number_format($pack->price, 2) }}</span>
                                @endif
                                <span class="text-sm font-bold text-purple-600">US$ {{ number_format($pack->price * (1 - $pack->discount_percentage / 100), 2) }}</span>
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
            if (bottomSheet) {
                bottomSheet.style.display = 'flex'; // Ensure it's visible as flex
                bottomSheet.style.transform = 'translateY(0)';
            }
            if (bottomSheetOverlay) {
                bottomSheetOverlay.style.display = 'block';
                bottomSheetOverlay.classList.remove('hidden');
            }
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
            }
        }
        
        // Close bottom sheet
        function closeBottomSheet() {
            if (bottomSheet) {
                bottomSheet.style.transform = 'translateY(100%)';
                // Keep display: block so transition works
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
                const discountAmount = (parseFloat(packPrice) * parseFloat(packDiscount)) / 100;
                const priceAfterDiscount = parseFloat(packPrice) - discountAmount;
                
                if (selectedPackText) {
                    selectedPackText.innerHTML = `
                        <span class="block text-sm font-semibold">${parseInt(packDiamonds).toLocaleString()} Diamonds + ${parseInt(packBonus).toLocaleString()} Bonus</span>
                        <span class="text-xs text-white/90 font-medium">US$ ${priceAfterDiscount.toFixed(2)}</span>
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

