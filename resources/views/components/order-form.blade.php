<div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 lg:p-6" style="padding: 20px;">
    <h2 class="text-lg font-semibold text-gray-800 mb-3 lg:mb-4">{{ __('game.order_information') }}</h2>
    @if(isset($gameTitle))
        <p class="text-sm text-gray-600 mb-3">{{ $gameTitle }} {{ __('game.top_up') }}</p>
    @endif
    
    <form id="order-form" class="space-y-4">
        @if(isset($gameType) && $gameType === 'bloodstrike')
            <!-- User ID and Server (Blood Strike) -->
            <div>
                <label for="user_id_bs" class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                <input type="text" 
                       id="user_id_bs" 
                       name="user_id_bs" 
                       required
                       pattern="[0-9]+"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       placeholder="Enter your User ID">
            </div>
            
            <!-- Server Selection (Blood Strike) -->
            <div>
                <label for="server_bs" class="block text-sm font-medium text-gray-700 mb-2">Server</label>
                <select id="server_bs" 
                        name="server_bs" 
                        required
                        class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm">
                    <option value="global" selected>Global</option>
                </select>
            </div>
        @elseif(isset($gameType) && ($gameType === 'freefire' || $gameType === 'pubgmobile' || $gameType === 'honorofkings'))
            <!-- Player ID (Free Fire / PUBG Mobile / Honor of Kings) -->
            <div>
                <label for="player_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('game.player_id') }}</label>
                <input type="text" 
                       id="player_id" 
                       name="player_id" 
                       required
                       pattern="[0-9]+"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       placeholder="{{ __('game.enter_player_id') }}">
            </div>
        @else
            <!-- User ID (Mobile Legends) -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('game.user_id') }}</label>
                <input type="text" 
                       id="user_id" 
                       name="user_id" 
                       required
                       pattern="[0-9]+"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       placeholder="{{ __('game.enter_user_id') }}">
            </div>
            
            <!-- Zone ID (Mobile Legends) -->
            <div>
                <label for="zone_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('game.zone_id') }}</label>
                <input type="text" 
                       id="zone_id" 
                       name="zone_id" 
                       required
                       pattern="[0-9]+"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       placeholder="{{ __('game.enter_zone_id') }}">
            </div>
        @endif
        
        <!-- Selected Pack Info -->
        <div id="selected-pack-info" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">{{ __('game.selected_pack') }}</span>
                <span id="pack-name" class="text-sm font-bold text-purple-600"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">{{ __('game.price') }}</span>
                <span id="pack-price" class="text-sm font-semibold text-purple-600"></span>
            </div>
        </div>
        
        <!-- Total Price -->
        <div class="pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-base font-semibold text-gray-900">{{ __('game.total') }}</span>
                <span id="total-price" class="text-lg font-bold text-purple-600">US$ 0.00</span>
            </div>
            <p id="diaszone-credit" class="text-xs text-gray-500 lowercase text-right">{{ __('game.diaszone_credit') }} 0</p>
        </div>
        
        <!-- Buy Now Button -->
        <button type="submit" 
                id="buy-now-btn"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed">
            {{ __('game.buy_now') }}
        </button>
    </form>
</div>


