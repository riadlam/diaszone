@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-container">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-link-disabled" aria-disabled="true">
                        <svg class="pagination-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link pagination-link-hover" rel="prev" aria-label="Previous">
                        <svg class="pagination-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="pagination-item disabled" aria-disabled="true">
                        <span class="pagination-link pagination-link-disabled">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active" aria-current="page">
                                <span class="pagination-link pagination-link-active">{{ $page }}</span>
                            </li>
                        @else
                            <li class="pagination-item">
                                <a href="{{ $url }}" class="pagination-link pagination-link-hover">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link pagination-link-hover" rel="next" aria-label="Next">
                        <svg class="pagination-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </li>
            @else
                <li class="pagination-item disabled">
                    <span class="pagination-link pagination-link-disabled" aria-disabled="true">
                        <svg class="pagination-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif

<style>
    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-item {
        margin: 0;
    }
    
    .pagination-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .pagination-link:hover {
        color: #9333ea;
        border-color: #9333ea;
        background: #faf5ff;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(147, 51, 234, 0.1);
    }
    
    .pagination-link-active {
        color: white;
        background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
        border-color: #9333ea;
        box-shadow: 0 4px 6px rgba(147, 51, 234, 0.2);
    }
    
    .pagination-link-disabled {
        color: #d1d5db;
        background: #f9fafb;
        border-color: #e5e7eb;
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .pagination-link-disabled:hover {
        transform: none;
        box-shadow: none;
        color: #d1d5db;
        border-color: #e5e7eb;
        background: #f9fafb;
    }
    
    .pagination-icon {
        width: 20px;
        height: 20px;
    }
    
    @media (max-width: 768px) {
        .pagination-link {
            min-width: 36px;
            height: 36px;
            padding: 0.375rem 0.625rem;
            font-size: 0.8125rem;
        }
        
        .pagination {
            gap: 0.375rem;
        }
    }
</style>

