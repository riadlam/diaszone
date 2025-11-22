@php
    $gameType = $gameType ?? 'mobilelegends';
    $gameTitle = $gameTitle ?? 'Mobile Legends';
    
    // Game-specific data
    $gameData = [
        'mobilelegends' => [
            'title' => 'Mobile Legends Diamonds',
            'image' => 'mobilelegends.webp',
            'region' => 'Global',
            'note' => 'Important Note: This Top Up service is available for all regions.',
        ],
        'freefire' => [
            'title' => 'Free Fire Diamonds',
            'image' => 'freefire.webp',
            'region' => 'Global',
            'note' => 'Important Note: This Top Up service not applicable to Indonesia, Vietnam, and India users.',
        ],
        'pubgmobile' => [
            'title' => 'PUBG Mobile UC',
            'image' => 'pubgmobile.webp',
            'region' => 'Global',
            'note' => 'Important Note: This Top Up service is available for all regions.',
        ],
        'honorofkings' => [
            'title' => 'Honor of Kings Tokens',
            'image' => 'honorofkings.webp',
            'region' => 'Global',
            'note' => 'Important Note: This Top Up service is available for all regions.',
        ],
        'bloodstrike' => [
            'title' => 'Blood Strike Golds',
            'image' => 'bloodrivels.webp',
            'region' => 'Global',
            'note' => 'Important Note: This Top Up service is available for all regions.',
        ],
    ];
    
    $currentGame = $gameData[$gameType] ?? $gameData['mobilelegends'];
@endphp

<div class="bg-white border-b border-gray-200" style="margin-top: 15px;">
    <div class="container mx-auto px-4 py-6 lg:py-8">
        <div class="flex flex-col lg:flex-row items-start gap-6 lg:gap-8">
            <!-- Game Image (Left) -->
            <div class="flex-shrink-0">
                <div class="w-48 h-48 lg:w-56 lg:h-56 bg-white rounded-xl p-4 flex items-center justify-center border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <img src="{{ asset('storage/images_homepage/' . $currentGame['image']) }}" 
                         alt="{{ $currentGame['title'] }}" 
                         class="w-full h-full object-contain rounded-xl">
                </div>
            </div>
            
            <!-- Game Information (Right) -->
            <div class="flex-1 min-w-0">
                <!-- Title and Region -->
                <div class="mb-4">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $currentGame['title'] }}</h1>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-md">
                            {{ $currentGame['region'] }}
                        </span>
                    </div>
                </div>
                
                <!-- Important Note -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-gray-800 leading-relaxed">
                        <strong class="font-semibold text-amber-900">Important Note:</strong> {{ str_replace('Important Note: ', '', $currentGame['note']) }}
                    </p>
                </div>
                
                <!-- Add to Favorite Button -->
                <button id="add-to-favorite-btn" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:border-gray-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span>Add to favorite</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.getElementById('add-to-favorite-btn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            // Toggle favorite state
            const isFavorite = this.classList.contains('favorited');
            
            if (isFavorite) {
                this.classList.remove('favorited', 'bg-red-50', 'border-red-300', 'text-red-700', 'hover:bg-red-100');
                this.classList.add('bg-gray-50', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
                this.querySelector('svg path').setAttribute('fill', 'none');
                this.querySelector('span').textContent = 'Add to favorite';
            } else {
                this.classList.add('favorited', 'bg-red-50', 'border-red-300', 'text-red-700', 'hover:bg-red-100');
                this.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
                this.querySelector('svg path').setAttribute('fill', 'currentColor');
                this.querySelector('span').textContent = 'Remove from favorite';
            }
            
            // Here you can add logic to save to localStorage or send to server
            const gameType = '{{ $gameType }}';
            const favorites = JSON.parse(localStorage.getItem('diaszone_favorites') || '[]');
            
            if (isFavorite) {
                const index = favorites.indexOf(gameType);
                if (index > -1) {
                    favorites.splice(index, 1);
                }
            } else {
                if (!favorites.includes(gameType)) {
                    favorites.push(gameType);
                }
            }
            
            localStorage.setItem('diaszone_favorites', JSON.stringify(favorites));
        });
        
        // Check if already favorited on load
        const gameType = '{{ $gameType }}';
        const favorites = JSON.parse(localStorage.getItem('diaszone_favorites') || '[]');
        if (favorites.includes(gameType)) {
            favoriteBtn.classList.add('favorited', 'bg-red-50', 'border-red-300', 'text-red-700', 'hover:bg-red-100');
            favoriteBtn.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
            favoriteBtn.querySelector('svg path').setAttribute('fill', 'currentColor');
            favoriteBtn.querySelector('span').textContent = 'Remove from favorite';
        }
    }
});
</script>
