<footer class="bg-gray-900 text-gray-300 pt-8 pb-6">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-6">
            <!-- About -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4">{{ __('footer.about_section') }}</h3>
                <p class="text-sm text-gray-400">{{ __('footer.about_description') }}</p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4">{{ __('footer.quick_links') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-purple-400 transition-colors">{{ __('nav.home') }}</a></li>
                    <li><a href="{{ route('dashboard.orders') }}" class="hover:text-purple-400 transition-colors">{{ __('nav.my_orders') }}</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">{{ __('footer.support') }}</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">{{ __('footer.faq') }}</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h4 class="text-white font-semibold mb-4">{{ __('footer.support') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('contact') }}" class="hover:text-purple-400 transition-colors">{{ __('footer.contact_us') }}</a></li>
                    <li><a href="{{ route('terms-of-use') }}" class="hover:text-purple-400 transition-colors">{{ __('footer.terms_of_use') }}</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="hover:text-purple-400 transition-colors">{{ __('footer.privacy_policy') }}</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">{{ __('footer.refund_policy') }}</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-white font-semibold mb-4">{{ __('footer.contact') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ __('footer.email_support') }}</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 pt-6 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} {{ __('footer.copyright') }}</p>
        </div>
    </div>
</footer>


