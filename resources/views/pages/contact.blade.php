@extends('layouts.app')

@section('title', __('contact.title') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4">
                {{ __('contact.title') }}
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ __('contact.description') }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Contact Information -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('contact.get_in_touch') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        Have a question or need assistance? Our team is ready to help you with any inquiries about Mobile Legends diamond recharges, payment methods, or account support.
                    </p>
                    
                    <!-- Contact Details -->
                    <div class="space-y-4">
                        <!-- Email -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ __('contact.email_label') }}</h3>
                                <a href="mailto:support@diaszone.com" class="text-purple-600 hover:text-purple-700 transition-colors">
                                    support@diaszone.com
                                </a>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ __('contact.location') }}</h3>
                                <p class="text-gray-700">{{ __('contact.algeria') }}</p>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ __('contact.support_hours') }}</h3>
                                <p class="text-gray-700">{{ __('contact.support_247') }}</p>
                                <p class="text-sm text-gray-600">{{ __('contact.always_here') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('contact.faq_title') }}</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('contact.faq_delivery_q') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('contact.faq_delivery_a') }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('contact.faq_payment_q') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('contact.faq_payment_a') }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('contact.faq_refund_q') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('contact.faq_refund_a') }}</p>
                        </div>
                    </div>
                    <a href="#" class="inline-block mt-4 text-purple-600 hover:text-purple-700 font-semibold text-sm">
                        {{ __('contact.view_all_faqs') }} →
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('contact.send') }}</h2>
                
                <form id="contact-form" class="space-y-6">
                    @csrf
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('contact.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                               placeholder="Enter your full name">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('contact.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                               placeholder="your.email@example.com">
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('contact.subject') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="subject" 
                                name="subject" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="order">Order Support</option>
                            <option value="payment">Payment Issue</option>
                            <option value="technical">Technical Support</option>
                            <option value="refund">Refund Request</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('contact.message') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                                  placeholder="Tell us how we can help you..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                        {{ __('contact.send') }}
                    </button>

                    <!-- Success/Error Messages -->
                    <div id="form-message" class="hidden p-4 rounded-lg"></div>
                </form>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="mt-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl shadow-lg p-8 text-white">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Fast Response</h3>
                    <p class="text-sm opacity-90">We typically respond within 24 hours</p>
                </div>
                <div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Secure & Private</h3>
                    <p class="text-sm opacity-90">Your information is safe with us</p>
                </div>
                <div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">24/7 Support</h3>
                    <p class="text-sm opacity-90">Always here when you need us</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            // Hide previous messages
            formMessage.classList.add('hidden');
            
            // Get form data
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            try {
                const response = await fetch('{{ route("contact.submit") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    formMessage.className = 'p-4 rounded-lg bg-green-50 border border-green-200 text-green-800';
                    formMessage.textContent = data.message || 'Thank you! Your message has been sent successfully. We will get back to you soon.';
                    formMessage.classList.remove('hidden');
                    
                    // Reset form
                    form.reset();
                } else {
                    // Show error message
                    formMessage.className = 'p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                    formMessage.textContent = data.message || 'Sorry, there was an error sending your message. Please try again.';
                    formMessage.classList.remove('hidden');
                }
            } catch (error) {
                // Show error message
                formMessage.className = 'p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                formMessage.textContent = 'Sorry, there was an error sending your message. Please try again.';
                formMessage.classList.remove('hidden');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});
</script>
@endpush
@endsection

