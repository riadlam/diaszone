<footer class="bg-gray-900 text-gray-300 pt-8 pb-6">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-6">
            <!-- About -->
            <div>
                <h3 class="text-white font-bold text-lg mb-4">DiasZone</h3>
                <p class="text-sm text-gray-400">Your trusted partner for Mobile Legends diamond top-ups. Fast, secure, and reliable.</p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-purple-400 transition-colors">Home</a></li>
                    <li><a href="{{ route('dashboard.orders') }}" class="hover:text-purple-400 transition-colors">My Orders</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Support</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">FAQ</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h4 class="text-white font-semibold mb-4">Support</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('contact') }}" class="hover:text-purple-400 transition-colors">Contact Us</a></li>
                    <li><a href="{{ route('terms-of-use') }}" class="hover:text-purple-400 transition-colors">Terms of Use</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="hover:text-purple-400 transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Refund Policy</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-white font-semibold mb-4">Contact</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>support@diaszone.com</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 pt-6 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} DiasZone. All rights reserved.</p>
        </div>
    </div>
</footer>


