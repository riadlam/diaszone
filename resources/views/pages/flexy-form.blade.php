@extends('layouts.app')

@section('title', 'Upload Flexy Receipt - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen pt-6 pb-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Upload Flexy Receipt</h1>
            <p class="text-sm text-gray-600">Please upload your payment receipt to complete your order</p>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Order Number</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Diamonds</span>
                    <span class="text-sm font-semibold text-purple-600">
                        {{ $order->diamondPack->diamonds }} + {{ $order->diamondPack->bonus_diamonds }} Bonus
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">User ID</span>
                    <span class="text-sm font-mono text-gray-900">{{ $order->user_id_ml }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Zone ID</span>
                    <span class="text-sm font-mono text-gray-900">{{ $order->zone_id_ml }}</span>
                </div>
            </div>
        </div>

        <!-- Flexy Phone Number Notice - Very Prominent -->
        <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 rounded-xl shadow-2xl border-4 border-purple-400 p-6 mb-6 transform hover:scale-[1.01] transition-all duration-300 ring-4 ring-purple-300 ring-opacity-50">
            <div class="flex items-center justify-center gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <div class="text-center flex-1">
                    <p class="text-white text-sm md:text-base font-semibold mb-2 uppercase tracking-wide">
                        Send Flexy Payment To This Number:
                    </p>
                    <button type="button" 
                            id="copy-phone-btn"
                            onclick="copyPhoneNumber()"
                            class="inline-block cursor-pointer group">
                        <p id="phone-number" class="text-white text-3xl md:text-4xl font-black tracking-wider mb-1 group-hover:text-yellow-300 transition-colors duration-200">
                            0673771763
                        </p>
                    </button>
                    <p id="copy-feedback" class="text-purple-200 text-xs md:text-sm font-medium mt-1">
                        Click the number to copy or <a href="tel:0673771763" class="underline hover:text-yellow-300">call directly</a>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Flexy Upload Form -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
            <form id="flexy-form" action="{{ route('flexy-submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="encrypted_order_id" value="{{ Crypt::encryptString($order->id) }}">
                
                <!-- Receipt Image Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Receipt Image <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <label for="receipt_image" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF or WEBP (MAX. 5MB)</p>
                            </div>
                            <input id="receipt_image" name="receipt_image" type="file" class="hidden" accept="image/*" required>
                        </label>
                        <div id="image-preview" class="mt-4 hidden">
                            <img id="preview-img" src="" alt="Receipt preview" class="max-w-full h-48 object-contain rounded-lg border-2 border-purple-200">
                            <button type="button" id="remove-image" class="mt-2 text-sm text-red-600 hover:text-red-700 font-medium">
                                Remove image
                            </button>
                        </div>
                        @error('receipt_image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Notes Field -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Additional Notes (Optional)
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4" 
                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                              placeholder="Add any additional information about your payment..."></textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <div class="mb-6">
                    <button type="submit" 
                            id="submit-btn"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                        Send Receipt
                    </button>
                </div>
            </form>
            
            <!-- Change Payment Gateway Link -->
            <div class="border-t-2 border-purple-200 pt-4 text-center">
                <a href="{{ route('home') }}" 
                   class="text-sm text-purple-600 hover:text-purple-700 font-semibold underline">
                    Change Payment Gateway
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const receiptInput = document.getElementById('receipt_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImageBtn = document.getElementById('remove-image');
    
    if (receiptInput) {
        receiptInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            receiptInput.value = '';
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        });
    }
    
    // Form submission
    const flexyForm = document.getElementById('flexy-form');
    if (flexyForm) {
        flexyForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading...';
            }
            
            // Clear cart after successful submission
            // The form will submit normally, and cart will be cleared on redirect
        });
    }
    
    // Clear cart if redirected from successful submission (but keep encrypted_order_id)
    @if(session('clear_cart'))
        // Only clear cart, keep encrypted_order_id for future reference
        localStorage.removeItem('diaszone_cart');
        // Note: We keep diaszone_encrypted_order_id so user can view their order later
    @endif
    
    // Copy phone number to clipboard
    window.copyPhoneNumber = function() {
        const phoneNumber = '0673771763';
        const feedback = document.getElementById('copy-feedback');
        
        // Copy to clipboard
        navigator.clipboard.writeText(phoneNumber).then(function() {
            // Show success feedback
            if (feedback) {
                const originalText = feedback.innerHTML;
                feedback.innerHTML = '<span class="text-yellow-300 font-bold">✓ Copied to clipboard!</span>';
                feedback.classList.remove('text-purple-200');
                feedback.classList.add('text-yellow-300');
                
                // Reset after 2 seconds
                setTimeout(function() {
                    feedback.innerHTML = originalText;
                    feedback.classList.remove('text-yellow-300');
                    feedback.classList.add('text-purple-200');
                }, 2000);
            }
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = phoneNumber;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                if (feedback) {
                    feedback.innerHTML = '<span class="text-yellow-300 font-bold">✓ Copied to clipboard!</span>';
                    setTimeout(function() {
                        feedback.innerHTML = 'Click the number to copy or <a href="tel:0673771763" class="underline hover:text-yellow-300">call directly</a>';
                    }, 2000);
                }
            } catch (err) {
                console.error('Failed to copy:', err);
            }
            document.body.removeChild(textArea);
        });
    };
});
</script>
@endpush

