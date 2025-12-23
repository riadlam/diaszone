@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', __('shop.page_title'))

@section('content')
<div class="min-h-screen bg-white">
    <!-- Shop Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">{{ __('shop.title') }}</h1>
            <p class="text-purple-100 text-lg">{{ __('shop.subtitle') }}</p>
        </div>
    </div>
    
    <!-- Search Bar & Category Filter -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <!-- Search Bar -->
            <div class="mb-6 max-w-2xl mx-auto">
                <form method="GET" action="{{ route('shop') }}" id="search-form" class="relative">
                    @if($category)
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <div class="relative" id="search-wrapper">
                        <input 
                            type="text" 
                            name="search" 
                            id="search-input"
                            value="{{ request('search') }}" 
                            placeholder="{{ __('shop.search_placeholder') }}" 
                            class="w-full pl-12 pr-12 py-3.5 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-gray-900 placeholder-gray-400 text-base shadow-sm hover:border-purple-400"
                            autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <button type="button" id="search-clear-btn" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-purple-600 transition-colors hidden" title="{{ __('shop.clear_search') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <button type="submit" id="search-submit-btn" class="absolute inset-y-0 right-0 pr-4 flex items-center text-purple-600 hover:text-purple-700 transition-colors" title="{{ __('shop.search') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                        <div id="search-loading" class="absolute inset-y-0 right-0 pr-4 flex items-center hidden">
                            <svg class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div id="search-results" class="hidden absolute w-full mt-2 bg-white border-2 border-purple-200 rounded-xl shadow-2xl max-h-96 overflow-hidden" style="z-index: 10000 !important;">
                        <div id="search-results-content" class="overflow-y-auto max-h-96 p-4">
                            <!-- Results will be inserted here -->
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Category Filter -->
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('shop', request('search') ? ['search' => request('search')] : []) }}" class="px-5 py-2 rounded-lg font-semibold text-sm transition-all {{ !$category ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('shop.all_products') }}
                </a>
                <a href="{{ route('shop', array_merge(request('search') ? ['search' => request('search')] : [], ['category' => 'topseller'])) }}" class="px-5 py-2 rounded-lg font-semibold text-sm transition-all {{ $category === 'topseller' ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('shop.top_sellers') }}
                </a>
                <a href="{{ route('shop', array_merge(request('search') ? ['search' => request('search')] : [], ['category' => 'new'])) }}" class="px-5 py-2 rounded-lg font-semibold text-sm transition-all {{ $category === 'new' ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('shop.new_products') }}
                </a>
                <a href="{{ route('shop', array_merge(request('search') ? ['search' => request('search')] : [], ['category' => 'giftcard'])) }}" class="px-5 py-2 rounded-lg font-semibold text-sm transition-all {{ $category === 'giftcard' ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ __('shop.gift_cards') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="container mx-auto px-4 py-12">
        @if(request('search') && $games->total() > 0)
            <div class="mb-6 text-center">
                <p class="text-gray-600">
                    {!! str_replace(
                        [':count', ':results', ':search'],
                        [
                            '<strong class="text-purple-600">' . $games->total() . '</strong>',
                            '<strong>' . __('shop.' . ($games->total() == 1 ? 'result' : 'results')) . '</strong>',
                            '<strong class="text-gray-900">' . request('search') . '</strong>'
                        ],
                        __('shop.found_results')
                    ) !!}
                </p>
            </div>
        @endif
        
        @if($games->count() > 0)
            <div class="products-grid">
                @foreach($games as $game)
                    <div class="product-card">
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
                                @if($game['is_topseller'] ?? false)
                                    <span class="product-badge product-badge-topseller">{{ __('shop.top_seller') }}</span>
                                @endif
                                @if($game['is_newproduct'] ?? false)
                                    <span class="product-badge product-badge-new">{{ __('shop.new') }}</span>
                                @endif
                            </div>
                            <h3 class="product-title">{{ $game['name'] ?? '' }}</h3>
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
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $games->links('components.pagination') }}
            </div>
        @else
            <div class="text-center py-20">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">{{ __('shop.no_products') }}</h3>
                <p class="text-gray-500">{{ __('shop.try_adjusting_filters') }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    @media (max-width: 1024px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }
    }
    
    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .product-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    .product-thumbnail {
        position: relative;
        background: #ffffff;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 1rem;
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
        font-size: 3rem;
        font-weight: bold;
        color: white;
    }
    
    .product-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        z-index: 2;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .product-badge-topseller {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
    }
    
    .product-badge-new {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .product-title {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        text-align: center;
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
        min-height: 2.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-rating-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .product-label {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        color: #92400e;
        background-color: #fef3c7;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }
    
    .product-label svg {
        width: 10px;
        height: 10px;
    }
    
    .star-icon {
        width: 16px;
        height: 16px;
        color: #fbbf24;
    }
    
    .rating-count {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
    }
    
    .rating-number {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .pagination-wrapper {
        margin-top: 4rem;
        display: flex;
        justify-content: center;
    }
    
    /* Search Results Styles */
    #search-results {
        top: 100%;
        left: 0;
    }
    
    .search-result-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }
    
    .search-result-card:hover {
        background-color: #f3f4f6;
        transform: translateX(4px);
    }
    
    .search-result-image {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 8px;
        background: #ffffff;
        padding: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }
    
    .search-result-info {
        flex: 1;
        min-width: 0;
    }
    
    .search-result-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: #111827;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .search-result-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .search-result-badge {
        font-size: 0.7rem;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .search-result-badge-topseller {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
    }
    
    .search-result-badge-new {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .search-result-badge-giftcard {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
    
    .search-no-results {
        padding: 2rem;
        text-align: center;
        color: #6b7280;
    }
</style>

<script>
(function() {
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    const searchResults = document.getElementById('search-results');
    const searchResultsContent = document.getElementById('search-results-content');
    const searchClearBtn = document.getElementById('search-clear-btn');
    const searchSubmitBtn = document.getElementById('search-submit-btn');
    const searchLoading = document.getElementById('search-loading');
    
    // Translations
    const translations = {
        topSeller: {!! json_encode(__('shop.top_seller')) !!},
        new: {!! json_encode(__('shop.new')) !!},
        giftCards: {!! json_encode(__('shop.gift_cards')) !!},
        noProducts: {!! json_encode(__('shop.no_products')) !!}
    };
    
    let searchTimeout = null;
    let currentSearchRequest = null;
    
    // Show/hide clear button based on input value
    function updateClearButton() {
        if (searchInput.value.trim().length > 0) {
            searchClearBtn.classList.remove('hidden');
            searchSubmitBtn.classList.add('hidden');
        } else {
            searchClearBtn.classList.add('hidden');
            searchSubmitBtn.classList.remove('hidden');
            hideResults();
        }
    }
    
    // Clear search
    searchClearBtn.addEventListener('click', function() {
        searchInput.value = '';
        updateClearButton();
        hideResults();
        // Clear URL parameter and reload
        const url = new URL(window.location);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    });
    
    // Hide results dropdown
    function hideResults() {
        searchResults.classList.add('hidden');
        searchResultsContent.innerHTML = '';
    }
    
    // Show results dropdown
    function showResults() {
        searchResults.classList.remove('hidden');
    }
    
    // Perform AJAX search
    function performSearch(query) {
        if (query.trim().length < 2) {
            hideResults();
            return;
        }
        
        // Cancel previous request if any
        if (currentSearchRequest) {
            currentSearchRequest.abort();
        }
        
        // Show loading
        searchLoading.classList.remove('hidden');
        searchSubmitBtn.classList.add('hidden');
        showResults();
        
        // Create new request
        const url = new URL('{{ route("api.search") }}', window.location.origin);
        url.searchParams.set('q', query);
        url.searchParams.set('limit', '10');
        
        currentSearchRequest = fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            currentSearchRequest = null;
            searchLoading.classList.add('hidden');
            
            if (data.success && data.results.length > 0) {
                renderResults(data.results);
            } else {
                renderNoResults();
            }
        })
        .catch(error => {
            currentSearchRequest = null;
            searchLoading.classList.add('hidden');
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                renderNoResults();
            }
        });
    }
    
    // Render search results
    function renderResults(results) {
        const imageBaseUrl = '{{ asset("") }}';
        let html = '<div class="space-y-2">';
        
        results.forEach(function(result) {
            const imageSrc = result.image_path ? imageBaseUrl + result.image_path : '';
            const imageTag = imageSrc 
                ? `<img src="${imageSrc}" alt="${result.name}" class="search-result-image">`
                : `<div class="search-result-image bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl">${result.name.charAt(0).toUpperCase()}</div>`;
            
            let badges = '';
            if (result.is_topseller) {
                badges += '<span class="search-result-badge search-result-badge-topseller">' + escapeHtml(translations.topSeller) + '</span>';
            }
            if (result.is_newproduct) {
                badges += '<span class="search-result-badge search-result-badge-new">' + escapeHtml(translations.new) + '</span>';
            }
            if (result.is_giftcard) {
                badges += '<span class="search-result-badge search-result-badge-giftcard">' + escapeHtml(translations.giftCards) + '</span>';
            }
            
            html += `
                <a href="${result.route}" class="search-result-card">
                    ${imageTag}
                    <div class="search-result-info">
                        <div class="search-result-title">${escapeHtml(result.name)}</div>
                        ${badges ? `<div class="search-result-badges">${badges}</div>` : ''}
                    </div>
                </a>
            `;
        });
        
        html += '</div>';
        searchResultsContent.innerHTML = html;
        showResults();
    }
    
    // Render no results message
    function renderNoResults() {
        searchResultsContent.innerHTML = `
            <div class="search-no-results">
                <p>${escapeHtml(translations.noProducts)}</p>
            </div>
        `;
        showResults();
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    // Input event with debouncing
    searchInput.addEventListener('input', function() {
        updateClearButton();
        const query = this.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Set new timeout for debouncing (300ms)
        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });
    
    // Focus event
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            performSearch(this.value.trim());
        }
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(event) {
        const wrapper = document.getElementById('search-wrapper');
        if (!wrapper.contains(event.target) && !searchResults.contains(event.target)) {
            hideResults();
        }
    });
    
    // Initialize clear button state
    updateClearButton();
    
    // Prevent form submission on Enter if results are showing (let user click result instead)
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !searchResults.classList.contains('hidden')) {
            e.preventDefault();
            // Optionally, submit the form to show full results
            searchForm.submit();
        }
    });
})();
</script>
@endsection

