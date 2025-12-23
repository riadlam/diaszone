<div class="gaming-hero-slider bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900 relative overflow-hidden" style="overflow-x: hidden !important;">
    <div class="container mx-auto px-4 py-8" style="overflow-x: hidden !important; max-width: 100%;">
        <div class="slider-wrapper relative" style="overflow: hidden !important; width: 100%; max-width: 100%;">
            <div id="slider-container" class="slider-container flex transition-transform duration-500 ease-in-out" style="width: 100%;">
                <!-- Slide 1 -->
                <div class="slide flex-shrink-0" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                    <div class="relative h-96 md:h-[500px] rounded-2xl overflow-hidden mx-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <img src="{{ url('storage_public/images_homepage/testslide.webp') }}" 
                             alt="Gaming Slide 1" 
                             class="w-full h-full object-contain"
                             loading="eager">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                            <div class="px-8 md:px-12 text-white">
                                <span class="inline-block px-4 py-2 bg-purple-600/80 backdrop-blur-sm rounded-full text-sm font-bold mb-4 animate-pulse">{{ __('home.new_season') }}</span>
                                <h2 class="text-4xl md:text-6xl font-black mb-4 bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">{{ __('home.top_up_now') }}</h2>
                                <p class="text-lg md:text-xl text-gray-200 mb-6 max-w-md">{{ __('home.best_deals') }}</p>
                                <a href="#offers-section" class="inline-block px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg font-bold text-white hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    {{ __('common.shop_now') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Slide 2 - Commented out
                <div class="slide flex-shrink-0" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                    <div class="relative h-96 md:h-[500px] rounded-2xl overflow-hidden mx-2">
                        <img src="{{ asset('storage_public/images_homepage/testtowslide.webp') }}" 
                             alt="Gaming Slide 2" 
                             class="w-full h-full object-contain"
                             loading="eager"
                             onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                            <div class="px-8 md:px-12 text-white">
                                <span class="inline-block px-4 py-2 bg-pink-600/80 backdrop-blur-sm rounded-full text-sm font-bold mb-4 animate-pulse">SPECIAL OFFER</span>
                                <h2 class="text-4xl md:text-6xl font-black mb-4 bg-gradient-to-r from-white to-pink-200 bg-clip-text text-transparent">Exclusive Deals</h2>
                                <p class="text-lg md:text-xl text-gray-200 mb-6 max-w-md">Limited time offers on diamond packs</p>
                                <a href="#offers-section" class="inline-block px-8 py-3 bg-gradient-to-r from-pink-600 to-red-600 rounded-lg font-bold text-white hover:from-pink-700 hover:to-red-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    View Offers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                --}}
                
                {{-- Slide 3 - Commented out
                <div class="slide flex-shrink-0" style="width: 100%; max-width: 100%; box-sizing: border-box;">
                    <div class="relative h-96 md:h-[500px] rounded-2xl overflow-hidden mx-2">
                        <img src="{{ asset('storage_public/images_homepage/testthreeslide.webp') }}" 
                             alt="Gaming Slide 3" 
                             class="w-full h-full object-contain"
                             loading="eager"
                             onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex items-center">
                            <div class="px-8 md:px-12 text-white">
                                <span class="inline-block px-4 py-2 bg-blue-600/80 backdrop-blur-sm rounded-full text-sm font-bold mb-4 animate-pulse">FAST DELIVERY</span>
                                <h2 class="text-4xl md:text-6xl font-black mb-4 bg-gradient-to-r from-white to-blue-200 bg-clip-text text-transparent">Instant Delivery</h2>
                                <p class="text-lg md:text-xl text-gray-200 mb-6 max-w-md">Get your diamonds instantly after payment</p>
                                <a href="#offers-section" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg font-bold text-white hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Get Started
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                --}}
            </div>
            
            <!-- Dots Indicator -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <button class="slider-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all active-dot" data-slide="0"></button>
                {{-- <button class="slider-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all" data-slide="1"></button>
                <button class="slider-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all" data-slide="2"></button> --}}
            </div>
        </div>
    </div>
</div>


