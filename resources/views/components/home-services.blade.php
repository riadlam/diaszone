<!-- Home Services Section - Popular Games -->
<div class="home-services">
    <div class="icon-box icon-box--14">
        @php
            // Limit to 12 games
            $displayGames = collect($games ?? [])->take(12);
        @endphp
        @forelse($displayGames as $game)
            @php
                // Use image_path from controller (already mapped via findGameImage method)
                $imagePath = $game['image_path'] ?? null;
                $imageExists = $imagePath !== null;
                
                // Truncate game name if too long (limit to 15 characters)
                $gameName = $game['name'] ?? '';
                $displayName = strlen($gameName) > 15 ? substr($gameName, 0, 15) . '...' : $gameName;
            @endphp
            <div class="icon-box__item">
                <a href="{{ $game['route'] }}" title="{{ $game['name'] }}">
                    <span class="icon-box__icon">
                        @if($imageExists && $imagePath)
                            <img src="{{ asset($imagePath) }}" 
                                 alt="{{ $game['name'] }}" 
                                 class="w-full h-full object-contain">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center rounded-full">
                                <span class="text-2xl font-bold text-white">
                                    {{ strtoupper(substr($game['name'], 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </span>
                    <span class="icon-box__title">{{ $displayName }}</span>
                </a>
            </div>
        @empty
            <div class="text-center text-gray-500 py-8">
                <p>{{ __('home.no_games_available') }}</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .home-services {
        padding: 2rem 0 3.125rem 0;
        background: #f9fafb;
    }
    
    .icon-box {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 0rem;
        padding: 1rem 0.5rem;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .icon-box::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    .icon-box__item {
        flex-shrink: 0;
        width: calc(100% / 12); /* Each item takes 1/12 of container width for 12 items */
        min-width: 95px; /* Minimum width for small screens */
    }
    
    @media (max-width: 768px) {
        .icon-box__item {
            width: auto;
            min-width: 90px;
        }
    }
    
    .icon-box__item a {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        transition: all 0.3s ease;
        width: 100%;
        padding: 0.5rem 0.25rem;
        border-radius: 8px;
    }
    
    .icon-box__item a:hover {
        background-color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .icon-box__icon {
        display: block;
        width: 70px;
        height: 70px;
        margin-bottom: 0.5rem;
        background-color: #ffffff;
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    @media (min-width: 768px) {
        .icon-box__icon {
            width: 88px;
            height: 88px;
            padding: 10px;
        }
    }
    
    .icon-box__icon img {
        border-radius: 8px;
    }
    
    .icon-box__icon .rounded-full {
        border-radius: 12px !important;
    }
    
    .icon-box__title {
        font-size: 0.75rem;
        font-weight: 500;
        color: #374151;
        text-align: center;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        margin-top: 0.25rem;
    }
    
    @media (min-width: 768px) {
        .icon-box__title {
            font-size: 0.875rem;
        }
    }
    
    .icon-box__item a:hover .icon-box__title {
        color: #6b7280;
    }
</style>

