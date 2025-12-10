<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seller->store_name ?? $seller->name }} - DiasZone</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .game-card {
            transition: all 0.3s ease;
        }
        .game-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center overflow-hidden">
                        @if(!empty($seller->store_logo_thumb ?? $seller->store_logo))
                                <img src="{{ storage_public_url($seller->store_logo_thumb ?? $seller->store_logo) }}" alt="{{ $seller->store_name ?? $seller->name }}" class="w-full h-full object-cover" />
                        @else
                            <span class="text-white font-bold text-xl">{{ substr($seller->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-xl">{{ $seller->store_name ?? $seller->name }}</h1>
                        <p class="text-gray-400 text-sm">Game Top-Up Store</p>
                    </div>
                </div>
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white text-sm">
                    Powered by DiasZone
                </a>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Mobile-only store header: banner and circular logo (Facebook-like) -->
        <div class="sm:hidden mb-6">
            <div class="w-full h-40 bg-gradient-to-r from-gray-700 via-gray-800 to-gray-700 overflow-hidden rounded-xl">
                @if(!empty($seller->store_banner_resized ?? $seller->store_banner))
                    {{-- Use root-relative storage_public path so hosted URLs don't get a /public prefix injected by asset() --}}
                    <img src="{{ storage_public_url($seller->store_banner_resized ?? $seller->store_banner) }}" alt="Banner" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-r from-indigo-700 via-indigo-900 to-slate-800 flex items-center justify-center">
                        <span class="text-gray-300">Store banner (mobile)</span>
                    </div>
                @endif
            </div>

                <div class="-mt-10 flex items-center gap-4 px-2">
                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-slate-900 bg-slate-800 flex items-center justify-center">
                    @if($seller->store_logo_thumb ?? $seller->store_logo)
                        <img src="{{ storage_public_url($seller->store_logo_thumb ?? $seller->store_logo) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <span class="text-white font-bold text-xl">{{ substr($seller->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-white text-lg font-bold" style="text-shadow: 0 6px 18px rgba(0,0,0,0.7);">{{ $seller->store_name ?? $seller->name }}</h1>
                    @if($seller->store_description)
                        <p class="text-gray-400 text-sm mt-1" style="text-shadow: 0 4px 12px rgba(0,0,0,0.55);">{{ $seller->store_description }}</p>
                    @endif
                </div>
            </div>
        </div>
        {{-- Description moved to store header under store name (redundant here) --}}
        
        <h2 class="text-2xl font-bold text-white mb-6">Choose a Game</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($games as $game)
                <a href="{{ route('seller.store.game', ['username' => $seller->username, 'gameType' => $game['type']]) }}" 
                   class="game-card bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-blue-500 flex flex-col">
                    <div class="w-full h-48 bg-slate-700 overflow-hidden flex-shrink-0 relative">
                        @if($game['type'] === 'mobilelegends')
                            <img src="{{ asset('storage/images_homepage/ml.webp') }}" alt="{{ $game['name'] }}" class="absolute top-0 left-0 w-full h-auto min-h-full object-cover" onerror="this.style.display='none'">
                        @elseif($game['type'] === 'freefire')
                            <img src="{{ asset('storage/images_homepage/Free_Fire.webp') }}" alt="{{ $game['name'] }}" class="absolute top-0 left-0 w-full h-auto min-h-full object-cover" onerror="this.style.display='none'">
                        @elseif($game['type'] === 'pubgmobile')
                            <img src="{{ asset('storage/images_homepage/games/pubg.png') }}" alt="{{ $game['name'] }}" class="absolute top-0 left-0 w-full h-auto min-h-full object-cover" onerror="this.style.display='none'">
                        @else
                            <div class="w-full h-48 bg-blue-500/20 flex items-center justify-center">
                                <span class="text-4xl">🎮</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4 text-center flex-shrink-0">
                        <h3 class="text-white font-bold">{{ $game['name'] }}</h3>
                        <p class="text-gray-400 text-sm">Top Up Now</p>
                    </div>
                </a>
            @endforeach
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-slate-800 border-t border-slate-700 mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-6 text-center">
            <p class="text-gray-400 text-sm">
                © {{ date('Y') }} {{ $seller->store_name ?? $seller->name }}. 
                Powered by <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300">DiasZone</a>
            </p>
        </div>
    </footer>
</body>
</html>
