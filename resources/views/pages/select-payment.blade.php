@extends('layouts.app')

@section('title', 'Select Payment Method')

@section('content')
<!-- Immediate cart validation - runs before page renders -->
<script>
(function() {
    'use strict';
    try {
        const cart = localStorage.getItem('diaszone_cart');
        if (!cart) {
            window.location.replace('{{ route("home") }}');
            return;
        }
        
        const cartItems = JSON.parse(cart);
        
        if (!Array.isArray(cartItems) || cartItems.length === 0) {
            window.location.replace('{{ route("home") }}');
            return;
        }
        
        // Check that there is exactly one cart item
        if (cartItems.length !== 1) {
            window.location.replace('{{ route("home") }}');
            return;
        }
        
        for (const item of cartItems) {
            if (!item.pack_id || !item.user_id || !item.zone_id) {
                window.location.replace('{{ route("home") }}');
                return;
            }
        }
    } catch (error) {
        window.location.replace('{{ route("home") }}');
        return;
    }
})();
</script>

<div id="payment-page-content" class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-0" style="display: none;">
    <div class="container mx-auto px-4">
        <section id="payment-section" class="flex flex-col lg:flex-row gap-6 items-start">
            <!-- Left Column: Payment Methods -->
            <div class="flex-1 lg:max-w-2xl w-full">
                <div>
                    <div class="mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Choose Payment Method</h1>
                        <p class="text-sm text-gray-600">Select your preferred payment option</p>
                    </div>
                    <div class="space-y-3">
                        @php
                            $baseUrl = request()->getSchemeAndHttpHost();
                        @endphp
                        
                        @foreach($paymentMethods as $index => $method)
                            <label class="payment-method-card block cursor-pointer group">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="{{ $method['id'] }}"
                                       class="hidden peer"
                                       @if($index === 1) checked @endif>
                                
                                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-purple-400 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 peer-checked:border-purple-600 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 peer-checked:shadow-2xl peer-checked:shadow-purple-200/50 flex items-center gap-3">
                                    <!-- Payment Icon -->
                                    <div class="flex-shrink-0 group-hover:scale-110 transition-transform duration-300" style="width: 57.6px; height: 57.6px; min-width: 57.6px; min-height: 57.6px; display: flex !important; align-items: center; justify-center; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 12.8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                                        <img src="{{ $baseUrl }}/storage/images_homepage/{{ $method['icon'] }}" 
                                             alt="{{ $method['name'] }}" 
                                             style="width: 100% !important; height: 100% !important; max-width: 57.6px !important; max-height: 57.6px !important; object-fit: contain !important; display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 1 !important;"
                                             loading="lazy"
                                             decoding="async">
                                    </div>
                                    
                                    <!-- Payment Info -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-gray-900 mb-1 group-hover:text-purple-600 transition-colors">{{ $method['name'] }}</h3>
                                        <p class="text-xs text-gray-600 font-medium">{{ $method['description'] }}</p>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Information -->
            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl shadow-lg border-2 border-purple-100 p-5">
                    <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Payment Information
                    </h2>
                    
                    <!-- Skeleton Loading -->
                    <div id="payment-info-skeleton" class="space-y-3 mb-4 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="border-t-2 border-purple-200 pt-3 space-y-3">
                            <div class="flex justify-between">
                                <div class="h-4 bg-gray-200 rounded w-16"></div>
                                <div class="h-4 bg-gray-200 rounded w-20"></div>
                            </div>
                            <div class="h-12 bg-gray-200 rounded"></div>
                            <div class="flex justify-between pt-3 border-t-2 border-purple-200">
                                <div class="h-5 bg-gray-200 rounded w-24"></div>
                                <div class="h-10 bg-gray-200 rounded w-24"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dynamic Payment Information -->
                    <div id="payment-info-content" class="space-y-3 mb-4" style="display: none;">
                        <!-- Divider -->
                        <div class="border-t-2 border-purple-200 pt-3">
                            <!-- Total Before Discounts -->
                            <div class="flex justify-between items-center mb-2" id="total-before-discount-row" style="display: none;">
                                <span class="text-xs font-semibold text-gray-600">Total Before Discounts</span>
                                <span class="text-xs font-medium text-gray-700" id="total-before-discount">USD 0.00</span>
                            </div>
                            
                            <!-- Discount -->
                            <div class="flex justify-between items-center mb-2" id="discount-row" style="display: none;">
                                <span class="text-xs font-semibold text-gray-600">Discount</span>
                                <span class="text-xs font-medium text-red-500" id="discount-amount">- USD 0.00</span>
                            </div>
                            
                            <!-- Total -->
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-semibold text-gray-700">Total</span>
                                <span class="text-sm font-bold text-gray-900" id="total-amount">USD 0.00</span>
                            </div>
                            
                            <!-- DiasZone Credit -->
                            <div class="mb-3">
                                <div class="text-xs text-gray-500 lowercase" id="diaszone-credit">diaszone credit 0</div>
                            </div>
                            
                            <!-- Pay with (dynamic based on selection) -->
                            <div class="mb-3 bg-purple-50 rounded-lg p-2 border border-purple-100">
                                <label class="block text-xs font-semibold text-purple-700 mb-1">Pay with</label>
                                <div class="text-xs font-bold text-purple-600" id="pay-with-text">Cryptocurrency (USD)</div>
                            </div>
                            
                            <!-- Pay Now Total -->
                            <div class="flex items-center justify-between gap-3 pt-3 border-t-2 border-purple-200">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-purple-600">Pay Now</span>
                                        <span class="text-base font-semibold text-purple-600" id="pay-now-amount">USD 0.00</span>
                                    </div>
                                </div>
                                <button type="button" 
                                        id="pay-submit-btn"
                                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg text-sm whitespace-nowrap">
                                    Envoyer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="border-t-2 border-purple-200 pt-3">
                        <p class="text-xs text-gray-600 leading-relaxed font-medium">
                            By proceeding, I acknowledge I have read and agreed to 
                            <a href="#" class="text-purple-600 hover:text-purple-700 font-bold underline">Terms of sale</a>, 
                            <a href="{{ route('terms-of-use') }}" class="text-purple-600 hover:text-purple-700 font-bold underline">Terms of use</a> & 
                            <a href="{{ route('privacy-policy') }}" class="text-purple-600 hover:text-purple-700 font-bold underline">Privacy Policy</a>.
                        </p>
                        <p class="text-xs text-gray-500 mt-2 font-semibold">Effective November 1, 2023</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Double-check validation and show content
    function validateAndShow() {
        try {
            const cart = localStorage.getItem('diaszone_cart');
            if (!cart) {
                window.location.replace('{{ route("home") }}');
                return false;
            }
            
            const cartItems = JSON.parse(cart);
            
            if (!Array.isArray(cartItems) || cartItems.length === 0) {
                window.location.replace('{{ route("home") }}');
                return false;
            }
            
            for (const item of cartItems) {
                if (!item.pack_id || !item.user_id || !item.zone_id) {
                    window.location.replace('{{ route("home") }}');
                    return false;
                }
            }
            
            // Validation passed - show content
            const content = document.getElementById('payment-page-content');
            if (content) {
                content.style.display = 'block';
            }
            
            return true;
        } catch (error) {
            window.location.replace('{{ route("home") }}');
            return false;
        }
    }
    
    // Validate and show content
    if (!validateAndShow()) {
        return;
    }
    
    // Fetch cart data and calculate totals
    async function loadPaymentInfo() {
        const skeleton = document.getElementById('payment-info-skeleton');
        const content = document.getElementById('payment-info-content');
        
        try {
            const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
            if (cart.length === 0) {
                window.location.replace('{{ route("home") }}');
                return;
            }
            
            // Get pack IDs from cart
            const packIds = cart.map(item => item.pack_id).filter(Boolean);
            
            // Fetch pack data
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
            
            if (!response.ok) {
                throw new Error('Failed to fetch pack data');
            }
            
            const data = await response.json();
            const packsMap = {};
            Object.keys(data.packs).forEach(id => {
                packsMap[id] = data.packs[id];
            });
            
            // Calculate totals
            let totalBeforeDiscount = 0;
            let totalDiscount = 0;
            let totalAmount = 0;
            let totalCredits = 0;
            
            cart.forEach(item => {
                const pack = packsMap[item.pack_id];
                if (!pack) return;
                
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
            });
            
            // Update UI
            if (totalBeforeDiscount > totalAmount) {
                document.getElementById('total-before-discount-row').style.display = 'flex';
                document.getElementById('total-before-discount').textContent = `USD ${totalBeforeDiscount.toFixed(2)}`;
            }
            
            if (totalDiscount > 0) {
                document.getElementById('discount-row').style.display = 'flex';
                document.getElementById('discount-amount').textContent = `- USD ${totalDiscount.toFixed(2)}`;
            }
            
            document.getElementById('total-amount').textContent = `USD ${totalAmount.toFixed(2)}`;
            document.getElementById('pay-now-amount').textContent = `USD ${totalAmount.toFixed(2)}`;
            document.getElementById('diaszone-credit').textContent = `diaszone credit ${totalCredits.toLocaleString()}`;
            
            // Hide skeleton, show content
            if (skeleton) skeleton.style.display = 'none';
            if (content) content.style.display = 'block';
            
        } catch (error) {
            console.error('Error loading payment info:', error);
            window.location.replace('{{ route("home") }}');
        }
    }
    
    // Load payment information
    loadPaymentInfo();
    
    // Payment method selection logic
    const paymentMethods = {
        'baridimob': 'Baridimob',
        'cryptocurrency': 'Cryptocurrency (USD)',
        'flexy': 'Flexy'
    };
    
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const payWithText = document.getElementById('pay-with-text');
    
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const methodName = paymentMethods[this.value] || this.value;
                payWithText.textContent = methodName;
            }
        });
    });
    
    // Set initial value
    const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (checkedRadio) {
        const methodName = paymentMethods[checkedRadio.value] || checkedRadio.value;
        payWithText.textContent = methodName;
    }
    
    // Handle submit button
    const submitBtn = document.getElementById('pay-submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', async function() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                return;
            }
            
            const paymentMethod = selectedMethod.value;
            
            // Get cart data
            const cart = JSON.parse(localStorage.getItem('diaszone_cart') || '[]');
            if (cart.length === 0) {
                alert('Your cart is empty');
                window.location.replace('{{ route("home") }}');
                return;
            }
            
            // If Flexy is selected, create a NEW order (even if one already exists) and navigate to flexy form
            if (paymentMethod === 'flexy') {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                try {
                    // Prepare cart items for API
                    const cartItems = cart.map(item => ({
                        pack_id: item.pack_id,
                        user_id: item.user_id,
                        zone_id: item.zone_id
                    }));
                    
                    // Always create a new order via API (regardless of existing encrypted_order_id)
                    // This allows users to create multiple orders that will all show in "My Orders"
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch('{{ route("api.orders.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ cart_items: cartItems })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to create order');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.orders && data.orders.length > 0) {
                        // Store the new encrypted order ID in localStorage array
                        const encryptedOrderId = data.orders[0].encrypted_id;
                        
                        // Get existing encrypted order IDs or create new array
                        const existingOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
                        let orderIdsArray = [];
                        
                        if (existingOrderIds) {
                            try {
                                const parsed = JSON.parse(existingOrderIds);
                                // Ensure it's an array
                                if (Array.isArray(parsed)) {
                                    orderIdsArray = parsed;
                                } else {
                                    // If it's not an array (old format), convert it
                                    orderIdsArray = [parsed];
                                }
                            } catch (e) {
                                // If parsing fails, start fresh
                                orderIdsArray = [];
                            }
                        }
                        
                        // Add the new encrypted order ID to the array (avoid duplicates)
                        if (!orderIdsArray.includes(encryptedOrderId)) {
                            orderIdsArray.push(encryptedOrderId);
                        }
                        
                        // Store the updated array back to localStorage
                        localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(orderIdsArray));
                        
                        // Update "My Orders" button visibility
                        if (window.updateMyOrdersButton) {
                            window.updateMyOrdersButton();
                        }
                        
                        // Clear cart from localStorage after order is created
                        localStorage.removeItem('diaszone_cart');
                        
                        // Navigate to flexy form with encrypted order ID
                        const encodedOrderId = encodeURIComponent(encryptedOrderId);
                        window.location.href = '{{ route("flexy-form") }}?order_id=' + encodedOrderId;
                    } else {
                        throw new Error('Order creation failed');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    alert('Failed to create order. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer';
                }
            } else if (paymentMethod === 'cryptocurrency') {
                // If Cryptocurrency is selected, create order and navigate to crypto payment page
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                try {
                    // Prepare cart items for API
                    const cartItems = cart.map(item => ({
                        pack_id: item.pack_id,
                        user_id: item.user_id,
                        zone_id: item.zone_id
                    }));
                    
                    // Create a new order via API
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch('{{ route("api.orders.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ cart_items: cartItems })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to create order');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success && data.orders && data.orders.length > 0) {
                        // Store the new encrypted order ID in localStorage array
                        const encryptedOrderId = data.orders[0].encrypted_id;
                        
                        // Get existing encrypted order IDs or create new array
                        const existingOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
                        let orderIdsArray = [];
                        
                        if (existingOrderIds) {
                            try {
                                const parsed = JSON.parse(existingOrderIds);
                                if (Array.isArray(parsed)) {
                                    orderIdsArray = parsed;
                                } else {
                                    orderIdsArray = [parsed];
                                }
                            } catch (e) {
                                orderIdsArray = [];
                            }
                        }
                        
                        // Add the new encrypted order ID to the array (avoid duplicates)
                        if (!orderIdsArray.includes(encryptedOrderId)) {
                            orderIdsArray.push(encryptedOrderId);
                        }
                        
                        // Store the updated array back to localStorage
                        localStorage.setItem('diaszone_encrypted_order_ids', JSON.stringify(orderIdsArray));
                        
                        // Update "My Orders" button visibility
                        if (window.updateMyOrdersButton) {
                            window.updateMyOrdersButton();
                        }
                        
                        // Clear cart from localStorage after order is created
                        localStorage.removeItem('diaszone_cart');
                        
                        // Navigate to crypto form page with encrypted order ID
                        const encodedOrderId = encodeURIComponent(encryptedOrderId);
                        window.location.href = '/select/crypto/' + encodedOrderId;
                    } else {
                        throw new Error('Order creation failed');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    alert('Failed to create order. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Envoyer';
                }
            } else if (paymentMethod === 'baridimob') {
                // Baridimob is coming soon - show message and do nothing
                alert('Coming Soon!\n\nBaridimob payment method will be available soon. Please select another payment method.');
                return;
            } else {
                // For other payment methods, handle accordingly
                console.log('Selected payment method:', paymentMethod);
                // You can add other payment method handling here
            }
        });
    }
});
</script>
@endpush

