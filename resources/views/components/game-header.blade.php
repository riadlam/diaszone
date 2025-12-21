@php
    $gameType = $gameType ?? 'mobilelegends';
    $gameTitle = $gameTitle ?? 'Mobile Legends';
    
    // Use dynamic game image if provided, otherwise fallback to hardcoded mapping
    $gameImage = $gameImage ?? null;
    
    // Game-specific data for notes and regions (fallback)
    $gameData = [
        'mobilelegends' => [
            'title' => __('game.title_mobilelegends'),
            'image' => 'mobilelegends.webp',
            'region' => __('game.global'),
            'note' => __('game.note_mobilelegends'),
        ],
        'freefire' => [
            'title' => __('game.title_freefire'),
            'image' => 'freefire.webp',
            'region' => __('game.global'),
            'note' => __('game.note_freefire'),
        ],
        'pubgmobile' => [
            'title' => __('game.title_pubgmobile'),
            'image' => 'pubgmobile.webp',
            'region' => __('game.global'),
            'note' => __('game.note_pubgmobile'),
        ],
        'honorofkings' => [
            'title' => __('game.title_honorofkings'),
            'image' => 'honorofkings.webp',
            'region' => __('game.global'),
            'note' => __('game.note_honorofkings'),
        ],
        'bloodstrike' => [
            'title' => __('game.title_bloodstrike'),
            'image' => 'bloodrivels.webp',
            'region' => __('game.global'),
            'note' => __('game.note_bloodstrike'),
        ],
    ];
    
    $currentGame = $gameData[$gameType] ?? $gameData['mobilelegends'];
    
    // Use dynamic image if provided, otherwise use fallback
    $displayImage = $gameImage ? asset($gameImage) : asset('storage_public/images_homepage/' . $currentGame['image']);
    $displayTitle = $gameTitle ?: $currentGame['title'];
@endphp

<div class="bg-white border-b border-gray-200" style="margin-top: 15px;">
    <div class="container mx-auto px-4 py-6 lg:py-8">
        <div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
            <!-- Game Image (Left) -->
            <div class="flex-shrink-0">
                <div class="w-48 h-48 lg:w-56 lg:h-56 bg-white rounded-xl p-4 flex items-center justify-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <img src="{{ $displayImage }}" 
                         alt="{{ $displayTitle }}" 
                         class="w-full h-full object-contain rounded-xl"
                         title="{{ $displayTitle }}">
                </div>
            </div>
            
            <!-- Game Information (Right) -->
            <div class="flex-1 min-w-0">
                <!-- Title and Region -->
                <div class="mb-4">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $displayTitle }}</h1>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-md">
                            {{ $currentGame['region'] }}
                        </span>
                    </div>
                </div>
                
                <!-- Product Features -->
                <div class="product-features mb-4">
            @php
                $reviewCount = $totalReviews ?? 0;
                // Show 5.0 rating if no reviews, otherwise use average rating
                $avgRating = ($reviewCount > 0 && isset($averageRating)) ? $averageRating : 5.0;
                // Format rating to 2 decimal places
                $displayRating = number_format($avgRating, 2);
            @endphp
            
            <div class="product-features__item instant-item">
                <div class="product-features__title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon feather-zap instant-icon" fill="#eab308" stroke="#eab308" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                </div>
                <div class="product-features__text">
                    <span>{{ __('game.instant') }}</span>
                </div>
            </div>
            
            <a class="product-features__item instant-item guarantee-item" href="https://item4gamer.com/faq/guarantee" target="_blank">
                <div class="product-features__title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon feather-shield" fill="currentColor" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div class="product-features__text">
                    <span>{{ __('game.validity_guarantee') }}</span>
                </div>
            </a>
            
            <a class="product-features__item" href="#comments">
                <div class="product-features__title">
                    <span>{{ $displayRating }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon feather-star" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
                <div class="product-features__text">
                    <p>{{ $reviewCount }} {{ __('game.reviews') }}</p>
                </div>
            </a>
            
            <div class="product-features__item">
                <div class="product-features__title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <rect height="13" width="15" x="1" y="3"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    <span onclick="const c=document.querySelector('.product-addon-container');if(c){const i=c.querySelector('input,select,textarea');if(i){i.focus();i.scrollIntoView({behavior:'smooth',block:'center'});}}">{{ __('game.topup') }}</span>
                </div>
                <div class="product-features__text">
                    <p>{{ __('game.delivery_type') }}</p>
                </div>
            </div>
            
            <div class="product-features__item">
                <div class="product-features__title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>{{ __('game.delivery_time') }}</span>
                </div>
                <div class="product-features__text">
                    <p>{{ __('game.delivery_time_text') }}</p>
                </div>
                </div>
                
                <!-- Important Note -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-gray-800 leading-relaxed">
                        <strong class="font-semibold text-amber-900">{{ __('game.important_note') }}</strong> {{ $currentGame['note'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-features {
    display: flex;
    flex-wrap: wrap;
    gap: 1.75rem;
    align-items: flex-start;
    padding: 1.25rem 0;
}

.product-features__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0.75rem;
    min-width: 100px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    border-radius: 0.5rem;
}

.product-features__item:hover:not(.instant-item) {
    transform: translateY(-3px);
    background-color: rgba(124, 58, 237, 0.05);
}

.product-features__item.instant-item {
    cursor: default;
}

.product-features__item.guarantee-item {
    cursor: pointer;
}

.product-features__title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.product-features__title svg {
    width: 26px;
    height: 26px;
    color: #7c3aed;
    flex-shrink: 0;
}

.product-features__title span {
    font-weight: 600;
    font-size: 1rem;
    color: #000000;
    white-space: nowrap;
    letter-spacing: 0.01em;
}

.product-features__text {
    font-size: 0.875rem;
    color: #1a1a1a;
    line-height: 1.4;
    font-weight: 500;
}

.product-features__text span,
.product-features__text p {
    margin: 0;
    line-height: 1.3;
}

.main-grid-item-icon {
    display: block;
}

.feather-shield,
.feather-star {
    color: #7c3aed;
}

.feather-zap.instant-icon {
    fill: #eab308 !important;
    stroke: #eab308 !important;
    color: #eab308;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-features {
        gap: 1.25rem;
        padding: 1rem 0;
    }
    
    .product-features__item {
        min-width: 85px;
        padding: 0.5rem;
    }
    
    .product-features__title svg {
        width: 24px;
        height: 24px;
    }
    
    .product-features__title span {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #000000;
    }
    
    .product-features__text {
        font-size: 0.8125rem;
        color: #1a1a1a;
        font-weight: 500;
    }
}

@media (max-width: 640px) {
    .product-features {
        gap: 1rem;
        justify-content: space-between;
    }
    
    .product-features__item {
        min-width: 75px;
        padding: 0.625rem 0.375rem;
        flex: 1;
        max-width: calc(33.333% - 0.667rem);
    }
    
    .product-features__title svg {
        width: 22px;
        height: 22px;
    }
    
    .product-features__title span {
        font-size: 0.875rem;
    }
    
    .product-features__text {
        font-size: 0.75rem;
        color: #1a1a1a;
        font-weight: 500;
    }
    
    .product-features__title span {
        color: #000000;
        font-weight: 600;
    }
}
</style>

