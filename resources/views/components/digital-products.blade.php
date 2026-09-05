@php
    $categories = $categories ?? [];
@endphp

<div class="page-section products-section digital-products-section">
    <div class="products-container">
        <div class="section-header">
            <h2 class="section-title">Digital Products</h2>
        </div>

        @if(count($categories) > 0)
            <ul class="products-grid">
                @foreach($categories as $category)
                    <li class="product-item">
                        <a href="{{ $category['route'] }}" class="product-link">
                            <div class="product-thumbnail">
                                @if(!empty($category['image_path']))
                                    <img src="{{ $category['image_path'] }}"
                                         alt="{{ $category['name'] ?? '' }}"
                                         class="product-image"
                                         loading="lazy">
                                @else
                                    <div class="product-placeholder">
                                        <span class="placeholder-text">
                                            {{ strtoupper(substr($category['name'] ?? 'D', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <h2 class="product-title">{{ $category['name'] ?? '' }}</h2>
                            <div class="product-rating-badge">
                                <div class="product-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                    </svg>
                                    <span>{{ __('shop.instant') }}</span>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="digital-products-empty">
                <p>No digital products available yet.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .page-section.digital-products-section {
        padding: 2rem 0 3rem 0;
        background: #ffffff;
        margin-top: 0;
    }

    .digital-products-section .products-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .digital-products-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .digital-products-section .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .digital-products-section .products-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 1rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    @media (max-width: 1024px) {
        .digital-products-section .products-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .digital-products-section .products-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .digital-products-section .product-item {
        margin: 0;
    }

    .digital-products-section .product-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .digital-products-section .product-thumbnail {
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
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .digital-products-section .product-link:hover .product-thumbnail {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .digital-products-section .product-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }

    .digital-products-section .product-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        background: linear-gradient(145deg, #eef2ff 0%, #e0e7ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .digital-products-section .placeholder-text {
        font-size: 2rem;
        font-weight: 700;
        color: #4f46e5;
    }

    .digital-products-section .product-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.4em;
    }

    .digital-products-section .product-rating-badge {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .digital-products-section .product-label {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #6d28d9;
        background: #f3e8ff;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
    }

    .digital-products-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
    }
</style>
