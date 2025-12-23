@if(!empty($giftCards) && count($giftCards) > 0)
<div class="page-section products-section">
    <div class="products-container">
        <div class="section-header">
            <h2 class="section-title">{{ __('home.gift_cards') }}</h2>
            <a href="{{ route('shop', ['category' => 'giftcard']) }}" class="section-more">
                {{ __('common.view_all') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>
        
        <ul class="products-grid">
            @foreach(collect($giftCards)->take(16) as $game)
                <li class="product-item">
                    <a href="{{ $game['route'] }}" class="product-link">
                        <div class="product-thumbnail">
                            @if($game['image_path'] ?? null)
                                <img src="{{ asset($game['image_path']) }}" 
                                     alt="{{ $game['name'] ?? '' }}" 
                                     class="product-image">
                            @else
                                <div class="product-placeholder">
                                    <span class="placeholder-text">
                                        {{ strtoupper(substr($game['name'] ?? '', 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <h2 class="product-title">{{ $game['name'] ?? '' }}</h2>
                        <div class="product-rating-badge">
                            <div class="product-label">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                                <span>{{ __('shop.instant') }}</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="star-icon filled">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            @php
                                $avgRating = $game['averageRating'] ?? 5.0;
                                $totalReviews = $game['totalReviews'] ?? 0;
                            @endphp
                            <span class="rating-count">({{ number_format($avgRating, 1) }})</span>
                            @if($totalReviews > 0)
                            <span class="rating-number">{{ $totalReviews }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<style>
    .page-section.products-section {
        padding: 0 0 3rem 0;
        background: #ffffff;
        margin-top: 0;
    }
    
    .products-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    
    .section-more {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        padding: 0.625rem 1.25rem;
        background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(147, 51, 234, 0.2);
        transition: all 0.3s ease;
    }
    
    .section-more:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #7e22ce 0%, #6d21aa 100%);
        box-shadow: 0 4px 8px rgba(147, 51, 234, 0.3);
        transform: translateY(-2px);
    }
    
    .section-more svg {
        width: 16px;
        height: 16px;
        transition: transform 0.3s ease;
    }
    
    .section-more:hover svg {
        transform: translateX(2px);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 1rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    @media (max-width: 1024px) {
        .products-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    .product-item {
        margin: 0;
    }
    
    .product-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    .product-thumbnail {
        position: relative;
        margin-bottom: 0.75rem;
        background: #ffffff;
        border-radius: 12px;
        padding: 9px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .product-thumbnail::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(0, 0, 0, 0.03) 100%);
        pointer-events: none;
        border-radius: 7px;
        z-index: 1;
    }
    
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 7px;
        position: relative;
        z-index: 0;
    }
    
    @media (max-width: 768px) {
        .product-image {
            transform: scale(1.2);
        }
    }
    
    .product-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #9333ea 0%, #ec4899 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    .placeholder-text {
        font-size: 2.5rem;
        font-weight: bold;
        color: white;
    }
    
    .product-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        text-align: center;
        margin: 0 0 0.5rem 0;
        line-height: 1.4;
        min-height: 2.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-rating-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
    }
    
    .product-label {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.65rem;
        color: #92400e;
        background-color: #fef3c7;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }
    
    .product-label svg {
        width: 10px;
        height: 10px;
    }
    
    .star-icon {
        width: 14px;
        height: 14px;
        color: #fbbf24;
    }
    
    .star-icon.filled {
        color: #fbbf24;
    }
    
    .rating-count {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
    }
    
    .rating-number {
        font-size: 0.7rem;
        color: #6b7280;
    }
</style>

