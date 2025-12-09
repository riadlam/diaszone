<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Payment Method - {{ $seller->store_name ?? $seller->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        
        /* Modal Overlay */
        .modal-overlay {
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease-out;
        }
        
        /* Modal Container */
        .modal-container {
            animation: slideUp 0.3s ease-out;
            max-height: 90vh;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(20px) scale(0.98); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        /* Custom scrollbar styling */
        .modal-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .modal-content::-webkit-scrollbar-track {
            background: rgba(71, 85, 105, 0.3);
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #f97316, #ea580c);
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #fb923c, #f97316);
        }
        
        /* Firefox scrollbar */
        .modal-content {
            scrollbar-color: #f97316 rgba(71, 85, 105, 0.3);
            scrollbar-width: thin;
        }
        
        /* Image Preview */
        .image-preview-container {
            position: relative;
            display: inline-block;
        }
        
        .image-preview {
            max-width: 120px;
            max-height: 120px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid rgba(249, 115, 22, 0.5);
        }
        
        .remove-image-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ef4444;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s;
        }
        
        .remove-image-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        /* Upload area states */
        .upload-area {
            transition: all 0.2s ease;
        }
        
        .upload-area:hover {
            border-color: #f97316;
            background: rgba(249, 115, 22, 0.05);
        }
        
        .upload-area.dragover {
            border-color: #f97316;
            background: rgba(249, 115, 22, 0.1);
            transform: scale(1.02);
        }
        
        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('seller.store.game', ['username' => $seller->username, 'gameType' => $gameType]) }}" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">{{ substr($seller->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h1 class="text-white font-bold">{{ $seller->store_name ?? $seller->name }}</h1>
                        <p class="text-gray-400 text-sm">Payment Method</p>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-4 py-8">
        <!-- Order Summary -->
        <div class="mb-8 bg-slate-800 rounded-xl p-6 border border-slate-700">
            <h2 class="text-lg font-bold text-white mb-4">Order Summary</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center pb-3 border-b border-slate-700">
                    <span class="text-gray-400">Game</span>
                    <span class="text-white font-medium">{{ $gameName }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-slate-700">
                    <span class="text-gray-400">Pack</span>
                    <span class="text-cyan-400 font-bold">{{ number_format($pack['diamonds']) }} {{ $currencyLabel }}</span>
                </div>
                @if($pack['bonus_diamonds'] > 0)
                    <div class="flex justify-between items-center pb-3 border-b border-slate-700">
                        <span class="text-gray-400">Bonus</span>
                        <span class="text-green-400 font-bold">+{{ $pack['bonus_diamonds'] }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pb-3 border-b border-slate-700">
                    <span class="text-gray-400">Player ID</span>
                    <span class="text-white font-medium">{{ $playerData['player_id'] }}</span>
                </div>
                @if($playerData['zone_id'])
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Zone ID</span>
                        <span class="text-white font-medium">{{ $playerData['zone_id'] }}</span>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Payment Methods -->
        <div class="mb-8">
            <h2 class="text-lg font-bold text-white mb-4">Select Payment Method</h2>
            
            <form action="{{ route('seller.store.payment', ['username' => $seller->username]) }}" method="POST" id="payment-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pack_id" value="{{ $pack['id'] }}">
                <!-- final_price is populated by server and client-side updated for Flexy selection (server will re-validate) -->
                <input type="hidden" name="final_price" id="final_price" value="{{ (int)$pack['price_dzd'] }}">
                <input type="hidden" name="game_type" value="{{ $gameType }}">
                <input type="hidden" name="player_id" value="{{ $playerData['player_id'] }}">
                @if($playerData['zone_id'])
                    <input type="hidden" name="zone_id" value="{{ $playerData['zone_id'] }}">
                @endif
                
                <div class="space-y-3">
                    <!-- Baridimob -->
                    <label class="block group cursor-pointer">
                        <input type="radio" name="payment_method" value="baridimob" class="hidden peer" checked>
                        <div class="bg-slate-800 border-2 border-slate-700 rounded-xl p-4 hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-slate-700/50 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center bg-slate-700/50 rounded-lg">
                                    <img src="{{ asset('storage/images_homepage/barid_jazaair.webp') }}" 
                                         alt="Baridimob" 
                                         class="w-full h-full object-contain p-1">
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-white font-bold">Algérie Poste</h3>
                                    <p class="text-gray-400 text-sm">Pay via CIB/Edahabia</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 border-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center transition-all">
                                    <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Cryptocurrency (Coming Soon) -->
                    <div class="opacity-60 pointer-events-none">
                        <div class="block group cursor-not-allowed">
                            <div class="bg-slate-800 border-2 border-slate-700 rounded-xl p-4 grayscale">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center bg-slate-700/50 rounded-lg">
                                        <span class="text-2xl">🪙</span>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-white font-bold">Cryptocurrency</h3>
                                        <p class="text-gray-400 text-sm">Coming Soon</p>
                                    </div>
                                    <span class="bg-yellow-500/20 text-yellow-400 text-xs font-semibold px-3 py-1 rounded-full">Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flexy -->
                    @php
                        $flexyAvailable = isset($seller->is_flexy) ? (bool)$seller->is_flexy : $seller->flexy_enabled;
                    @endphp
                    @if($flexyAvailable)
                    <label class="block group cursor-pointer">
                        <input id="flexy-radio" type="radio" name="payment_method" value="flexy" class="hidden peer">
                        <div class="bg-slate-800 border-2 border-slate-700 rounded-xl p-4 hover:border-orange-500 peer-checked:border-orange-500 peer-checked:bg-slate-700/50 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center bg-slate-700/50 rounded-lg">
                                    <img src="{{ asset('storage/images_homepage/flexy.webp') }}" 
                                         alt="Flexy" 
                                         class="w-full h-full object-contain p-1">
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-white font-bold">Flexy</h3>
                                    <p class="text-gray-400 text-sm">Transfer to Flexy account</p>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 border-slate-600 peer-checked:border-orange-500 peer-checked:bg-orange-500 flex items-center justify-center transition-all">
                                    <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </label>
                    @else
                    <div class="opacity-60 pointer-events-none">
                        <div class="block group cursor-not-allowed">
                            <div class="bg-slate-800 border-2 border-slate-700 rounded-xl p-4 grayscale">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center bg-slate-700/50 rounded-lg">
                                        <img src="{{ asset('storage/images_homepage/flexy.webp') }}" alt="Flexy" class="w-full h-full object-contain p-1 grayscale">
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-white font-bold">Flexy</h3>
                                        <p class="text-gray-400 text-sm">Disabled by seller</p>
                                    </div>
                                    <span class="bg-gray-600/40 text-gray-300 text-xs font-semibold px-3 py-1 rounded-full">Disabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Total Price -->
                <div class="mt-8 p-4 bg-gradient-to-r from-blue-600/20 to-cyan-600/20 border border-blue-500/30 rounded-xl">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300 font-medium">Total Amount</span>
                        <span id="total-amount" class="text-2xl font-bold text-white">{{ (int)$pack['price_dzd'] }} DZD</span>
                    </div>
                </div>
                
                <!-- Proceed Button -->
                <button type="button" id="proceed-btn" onclick="handleProceed(event)" class="w-full mt-6 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                    Proceed to Payment
                </button>
            <!-- Form continues to include the Flexy modal inputs -->

    <!-- Flexy Modal -->
    <div id="flexy-modal" class="fixed inset-0 z-50 hidden">
        <!-- Overlay -->
        <div class="modal-overlay absolute inset-0" onclick="closeFlexyModal()"></div>
        
        <!-- Modal Container -->
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="modal-container relative w-full max-w-md bg-gradient-to-b from-slate-800 to-slate-900 border border-slate-600 rounded-2xl shadow-2xl overflow-hidden">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-orange-600 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <img src="{{ asset('storage/images_homepage/flexy.webp') }}" alt="Flexy" class="w-8 h-8 object-contain">
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Flexy Payment</h3>
                                <p class="text-orange-100 text-xs">Transfer & upload receipt</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeFlexyModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Content (Scrollable) -->
                <div class="modal-content overflow-y-auto" style="max-height: calc(90vh - 180px);">
                    <div class="p-6 space-y-5">
                        <!-- Flexy Number -->
                        <div class="bg-gradient-to-r from-orange-500/20 to-orange-600/20 border border-orange-500/50 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-orange-300 text-sm font-medium flex items-center gap-2">
                                    <span>💳</span> Transfer To Flexy:
                                </p>
                                <button type="button" onclick="copyToClipboard('{{ $seller->flexy_number ?? 'N/A' }}', this)" class="text-orange-400 hover:text-orange-300 text-xs font-medium px-3 py-1.5 bg-orange-500/20 hover:bg-orange-500/30 rounded-lg transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Copy</span>
                                </button>
                            </div>
                            <p class="text-white font-bold text-2xl tracking-wider font-mono">{{ $seller->flexy_number ?? 'N/A' }}</p>

                            {{-- Instruction directly under the phone/number block --}}
                            @if(!empty($seller->flexy_instruction))
                                <div class="mt-3 text-sm text-slate-300 bg-slate-800/30 rounded-md p-3 border border-slate-700">
                                    <p class="font-medium text-slate-200">Payment instructions</p>
                                    <p class="text-xs text-slate-300 mt-1">{{ $seller->flexy_instruction }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Amount -->
                        <div class="bg-cyan-500/20 border border-cyan-500/50 rounded-xl p-4">
                            <p class="text-cyan-300 text-sm font-medium mb-1 flex items-center gap-2">
                                <span>💰</span> Amount to Send:
                            </p>
                            <p class="text-white font-bold text-3xl"><span id="flexy-amount">{{ (int)$pack['price_dzd'] }}</span> <span class="text-xl text-cyan-300">DZD</span></p>
                        </div>

                        <!-- Upload Receipt -->
                        <div>
                            <label class="block text-gray-200 font-semibold mb-2 flex items-center gap-2">
                                <span>📸</span> Upload Receipt <span class="text-red-400">*</span>
                            </label>
                            <p class="text-gray-400 text-xs mb-3">{{ $seller->flexy_instruction ?? 'Send a screenshot of your Flexy transfer confirmation' }}</p>
                            
                            <!-- Upload Area -->
                            <div id="upload-area" class="upload-area border-2 border-dashed border-slate-500 rounded-xl p-6 text-center cursor-pointer transition" onclick="document.getElementById('receipt-input').click()">
                                <div id="upload-placeholder">
                                    <div class="w-14 h-14 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-300 text-sm font-medium">Click to upload or drag and drop</p>
                                    <p class="text-gray-500 text-xs mt-1">PNG, JPG, PDF up to 10MB</p>
                                </div>
                                
                                <!-- Image Preview (hidden by default) -->
                                <div id="image-preview-wrapper" class="hidden">
                                    <div class="image-preview-container inline-block">
                                        <img id="image-preview" src="" alt="Receipt preview" class="image-preview">
                                        <button type="button" onclick="removeImage(event)" class="remove-image-btn">✕</button>
                                    </div>
                                    <p id="file-info" class="text-green-400 text-sm mt-3 font-medium"></p>
                                </div>
                            </div>
                            <input type="file" id="receipt-input" name="receipt" accept=".png,.jpg,.jpeg,.pdf" class="hidden" onchange="handleFileSelect(this)">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="description" class="block text-gray-200 font-semibold mb-2 flex items-center gap-2">
                                <span>📝</span> Notes <span class="text-gray-500 text-sm font-normal">(Optional)</span>
                            </label>
                            <p class="text-gray-400 text-xs mb-2">Add any reference or notes about this transfer</p>
                            <textarea id="description" name="description" rows="2" placeholder="e.g., Transaction ID, time of transfer..." class="w-full bg-slate-700/50 text-white rounded-xl border border-slate-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none px-4 py-3 resize-none transition text-sm"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="p-4 bg-slate-800/50 border-t border-slate-700 flex gap-3">
                    <button type="button" onclick="closeFlexyModal()" class="flex-1 px-4 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-medium transition">
                        Cancel
                    </button>
                    <button type="button" id="confirm-flexy-btn" onclick="confirmFlexyAndSubmit()" class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl font-bold transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Submit Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </form>
    </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-slate-800 border-t border-slate-700 mt-8">
        <div class="max-w-6xl mx-auto px-4 py-6 text-center">
            <p class="text-gray-400 text-sm">
                © {{ date('Y') }} {{ $seller->store_name ?? $seller->name }}. 
                Powered by <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300">DiasZone</a>
            </p>
        </div>
    </footer>

    <script>
        // Save order info to cookies for future reuse
        function saveToCookies() {
            const packId = '{{ $pack['id'] }}';
            const playerId = '{{ $playerData['player_id'] }}';
            const zoneId = '{{ $playerData['zone_id'] ?? '' }}';
            const gameType = '{{ $gameType }}';
            const sellerUsername = '{{ $seller->username }}';
            
            // Set cookies with 30 day expiry
            const expires = new Date();
            expires.setDate(expires.getDate() + 30);
            const expireStr = expires.toUTCString();
            
            // Store with seller-specific prefix
            const prefix = `dz_${sellerUsername}_${gameType}_`;
            document.cookie = `${prefix}player_id=${encodeURIComponent(playerId)}; expires=${expireStr}; path=/; SameSite=Lax`;
            document.cookie = `${prefix}zone_id=${encodeURIComponent(zoneId)}; expires=${expireStr}; path=/; SameSite=Lax`;
            document.cookie = `${prefix}pack_id=${encodeURIComponent(packId)}; expires=${expireStr}; path=/; SameSite=Lax`;
        }

        function handleProceed(e) {
            e = e || window.event;
            const flexyRadio = document.getElementById('flexy-radio');
            
            // If Flexy is selected, open modal
            if (flexyRadio && flexyRadio.checked) {
                openFlexyModal();
                if (e.preventDefault) e.preventDefault();
                return false;
            }
            
            // Save to cookies before submitting
            saveToCookies();
            
            // Submit the form for other methods
            var form = document.getElementById('payment-form');
            if (form) form.submit();
        }

        function openFlexyModal() {
            const modal = document.getElementById('flexy-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeFlexyModal() {
            const modal = document.getElementById('flexy-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Handle file selection
        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;

            const uploadPlaceholder = document.getElementById('upload-placeholder');
            const previewWrapper = document.getElementById('image-preview-wrapper');
            const imagePreview = document.getElementById('image-preview');
            const fileInfo = document.getElementById('file-info');
            const uploadArea = document.getElementById('upload-area');

            // Check file size
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                input.value = '';
                return;
            }

            // Format file size
            const fileSize = file.size < 1024 * 1024 
                ? (file.size / 1024).toFixed(1) + ' KB'
                : (file.size / (1024 * 1024)).toFixed(1) + ' MB';

            if (file.type.startsWith('image/')) {
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    uploadPlaceholder.classList.add('hidden');
                    previewWrapper.classList.remove('hidden');
                    fileInfo.innerHTML = `<span class="text-green-400">✓</span> ${file.name} (${fileSize})`;
                };
                reader.readAsDataURL(file);
            } else {
                // PDF file - show icon instead
                imagePreview.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNmOTczMTYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTQgMkg2YTIgMiAwIDAgMC0yIDJ2MTZhMiAyIDAgMCAwIDIgMmgxMmEyIDIgMCAwIDAgMi0yVjhsLTYtNnoiLz48cGF0aCBkPSJNMTQgMnY2aDYiLz48cGF0aCBkPSJNMTAgOUg4Ii8+PHBhdGggZD0iTTE2IDEzSDgiLz48cGF0aCBkPSJNMTYgMTdIOCIvPjwvc3ZnPg==';
                uploadPlaceholder.classList.add('hidden');
                previewWrapper.classList.remove('hidden');
                fileInfo.innerHTML = `<span class="text-green-400">✓</span> ${file.name} (${fileSize})`;
            }

            uploadArea.classList.add('border-green-500');
            uploadArea.classList.remove('border-slate-500');
        }

        // Remove selected image
        function removeImage(e) {
            e.stopPropagation();
            const input = document.getElementById('receipt-input');
            const uploadPlaceholder = document.getElementById('upload-placeholder');
            const previewWrapper = document.getElementById('image-preview-wrapper');
            const uploadArea = document.getElementById('upload-area');

            input.value = '';
            uploadPlaceholder.classList.remove('hidden');
            previewWrapper.classList.add('hidden');
            uploadArea.classList.remove('border-green-500');
            uploadArea.classList.add('border-slate-500');
        }

        // Drag and drop handlers
        const uploadArea = document.getElementById('upload-area');
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const input = document.getElementById('receipt-input');
                    input.files = files;
                    handleFileSelect(input);
                }
            });
        }

        let _isSubmittingFlexy = false;
        function confirmFlexyAndSubmit() {
            const receiptInput = document.getElementById('receipt-input');
            const confirmBtn = document.getElementById('confirm-flexy-btn');

            // Prevent double-clicks / multiple submissions
            if (_isSubmittingFlexy) return false;

            if (!receiptInput || receiptInput.files.length === 0) {
                alert('Please upload a receipt file');
                return false;
            }

            // Additional client-side validation: file size & mime check (mirror server rules)
            const file = receiptInput.files[0];
            if (!file) {
                alert('No file selected. Please upload a receipt file');
                return false;
            }
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('Receipt file too large. Maximum 10MB allowed.');
                return false;
            }
            const allowedTypes = ['image/png', 'image/jpeg', 'application/pdf'];
            if (file.type && !allowedTypes.includes(file.type)) {
                // type may be empty for some files; allow server to recheck but warn user
                alert('Unsupported receipt file type. Allowed: PNG, JPG, JPEG, PDF.');
                return false;
            }

            // Show loading state
            confirmBtn.disabled = true;
            _isSubmittingFlexy = true;
            confirmBtn.innerHTML = `
                <svg class="w-5 h-5 spinner" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Processing...</span>
            `;

            // Save to cookies before submitting
            saveToCookies();

            // Close modal and restore scroll before submitting
            document.body.style.overflow = '';
            
            // Submit via AJAX to reliably handle JSON redirect responses
            (async () => {
                try {
                    const form = document.getElementById('payment-form');
                    const url = form.action;
                    const fd = new FormData(form);

                    // Ensure the file is present in the FormData
                    fd.delete('receipt');
                    const file = receiptInput.files[0];
                    fd.append('receipt', file);

                    const res = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    if (!res.ok) {
                        // If server returned HTML/redirect or an error, try to show helpful message
                        const txt = await res.text();
                        console.error('Flexy submit failed:', res.status, txt);
                        alert('Submission failed. Please try again.');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<span>Submit Order</span>';
                        return;
                    }

                    const data = await res.json();
                    if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }

                    // If the backend returned a non-success JSON or message, show it
                    if (data.message) {
                        alert(data.message);
                    } else {
                        alert('Unexpected response from server');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Network error while submitting. Please try again.');
                    } finally {
                    if (confirmBtn) {
                        // re-enable only on failure; if server redirected we won't reach here
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<span>Submit Order</span>';
                    }
                    _isSubmittingFlexy = false;
                }
            })();
        }

        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Copied!</span>
                `;
                btn.classList.add('bg-green-500/30', 'text-green-300');
                btn.classList.remove('text-orange-400', 'bg-orange-500/20');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-green-500/30', 'text-green-300');
                    btn.classList.add('text-orange-400', 'bg-orange-500/20');
                }, 2000);
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFlexyModal();
            }
        });

        // --- Dynamic Flexy price fetching ---
        (function() {
            const defaultPrice = {{ (int)$pack['price_dzd'] }};
            const totalAmountEl = document.getElementById('total-amount');
            const flexyAmountEl = document.getElementById('flexy-amount');
            const radios = document.querySelectorAll('input[name="payment_method"]');

            // URL for fetching flexy price (server-calculated secure endpoint)
            const flexyPriceUrl = '{{ route('seller.store.flexy-price', ['username' => $seller->username, 'pack' => $pack['id']]) }}';

            async function showFlexyPrice() {
                try {
                    // disable proceed button while retrieving server price
                    const proceedBtn = document.getElementById('proceed-btn');
                    if (proceedBtn) {
                        proceedBtn.disabled = true;
                        proceedBtn.dataset.savedHtml = proceedBtn.innerHTML;
                        proceedBtn.innerHTML = `<svg class="w-4 h-4 spinner mr-2" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span>Checking price...</span>`;
                    }
                    const res = await fetch(flexyPriceUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (!res.ok) {
                        console.warn('Flexy price fetch failed', res.status);
                        // fallback to default
                        totalAmountEl.textContent = defaultPrice + ' DZD';
                        if (flexyAmountEl) flexyAmountEl.textContent = defaultPrice;
                        return;
                    }

                    const json = await res.json();
                    if (!json.success) {
                        console.warn('Flexy price API returned non-success', json.message);
                        totalAmountEl.textContent = defaultPrice + ' DZD';
                        if (flexyAmountEl) flexyAmountEl.textContent = defaultPrice;
                        return;
                    }

                    const price = Math.round(json.flexy_price);
                    totalAmountEl.textContent = price + ' DZD';
                    if (flexyAmountEl) flexyAmountEl.textContent = price;
                    const finalInput = document.getElementById('final_price');
                    if (finalInput) finalInput.value = price;
                } catch (err) {
                    console.error('Error fetching flexy price', err);
                    totalAmountEl.textContent = defaultPrice + ' DZD';
                    if (flexyAmountEl) flexyAmountEl.textContent = defaultPrice;
                    const finalInput = document.getElementById('final_price');
                    if (finalInput) finalInput.value = defaultPrice;
                } finally {
                    const proceedBtn = document.getElementById('proceed-btn');
                    if (proceedBtn) {
                        // restore original state
                        proceedBtn.disabled = false;
                        if (proceedBtn.dataset && proceedBtn.dataset.savedHtml) {
                            proceedBtn.innerHTML = proceedBtn.dataset.savedHtml;
                            delete proceedBtn.dataset.savedHtml;
                        }
                    }
                }
            }

            // Listen for selection changes
            radios.forEach(r => r.addEventListener('change', function(e) {
                    if (this.value === 'flexy') {
                    showFlexyPrice();
                } else {
                    // restore to default
                    totalAmountEl.textContent = defaultPrice + ' DZD';
                    if (flexyAmountEl) flexyAmountEl.textContent = defaultPrice;
                }
            }));

            // If page loads with flexy pre-selected, fetch price
            const selected = document.querySelector('input[name="payment_method"]:checked');
            if (selected && selected.value === 'flexy') {
                showFlexyPrice();
            }
        })();
    </script>
</body>
</html>
