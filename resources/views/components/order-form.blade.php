<div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 lg:p-6" style="padding: 20px;">
    <h2 class="text-lg font-semibold text-gray-800 mb-3 lg:mb-4">{{ __('game.order_information') }}</h2>
    @if(isset($gameTitle))
        <p class="text-sm text-gray-600 mb-3">{{ $gameTitle }} {{ __('game.top_up') }}</p>
    @endif
    
    <form id="order-form" class="space-y-4">
        @if(!empty($item4gamerUnavailable))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p class="font-semibold">Not available</p>
                <p class="mt-1 text-amber-800">This game’s products cannot be purchased right now. You can view packs, but checkout is disabled.</p>
            </div>
        @endif
        @php
            // Check if game has required_fields defined (from JSON import)
            $requiredFields = isset($game) && $game && $game->required_fields ? $game->required_fields : null;
            
            // Fallback to hardcoded logic for existing games if no required_fields
            if (!$requiredFields) {
                if (isset($gameType) && $gameType === 'mobilelegends') {
                    $requiredFields = [
                        ['data_name' => 'user_id', 'type' => 'text', 'required' => true, 'name' => __('game.user_id')],
                        ['data_name' => 'zone_id', 'type' => 'text', 'required' => true, 'name' => __('game.zone_id')],
                    ];
                } elseif (isset($gameType) && $gameType === 'bloodstrike') {
                    $requiredFields = [
                        ['data_name' => 'user_id_bs', 'type' => 'text', 'required' => true, 'name' => 'User ID'],
                        ['data_name' => 'server_bs', 'type' => 'select', 'required' => true, 'name' => 'Server', 'options' => [['value' => 'global', 'label' => 'Global']]],
                    ];
                } elseif (isset($gameType) && in_array($gameType, ['freefire', 'pubgmobile', 'honorofkings'])) {
                    $requiredFields = [
                        ['data_name' => 'player_id', 'type' => 'text', 'required' => true, 'name' => __('game.player_id')],
                    ];
                } else {
                    // Default: User ID only
                    $requiredFields = [
                        ['data_name' => 'save_id', 'type' => 'text', 'required' => true, 'name' => 'User ID'],
                    ];
                }
            }
        @endphp

        @if(!empty($requiredFields) && is_array($requiredFields))
            @foreach($requiredFields as $field)
                @php
                    $fieldName = $field['data_name'] ?? '';
                    $fieldType = $field['type'] ?? 'text';
                    $fieldLabel = $field['name'] ?? ucfirst(str_replace('_', ' ', $fieldName));
                    $isRequired = $field['required'] ?? true;
                    $fieldOptions = $field['options'] ?? [];
                    
                    // Map save_id to user_id for compatibility (they're the same)
                    $inputName = $fieldName === 'save_id' ? 'save_id' : $fieldName;
                    $inputId = $fieldName;
                @endphp
                
                <div>
                    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-2">{{ $fieldLabel }}</label>
                    
                    @if($fieldType === 'select' && !empty($fieldOptions))
                        <select id="{{ $inputId }}" 
                                name="{{ $inputName }}" 
                                @if($isRequired) required @endif
                                class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm">
                            @foreach($fieldOptions as $option)
                                <option value="{{ $option['value'] ?? $option['label'] ?? '' }}"
                                        @if(isset($option['value']) && ($option['value'] === 'global' || $option['value'] === 'Global')) selected @endif>
                                    {{ $option['label'] ?? $option['value'] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $fieldType }}" 
                               id="{{ $inputId }}" 
                               name="{{ $inputName }}" 
                               @if($isRequired) required @endif
                               @if($fieldType === 'text' && in_array($fieldName, ['user_id', 'zone_id', 'player_id', 'user_id_bs'])) pattern="[0-9]+" @endif
                               class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                               placeholder="Enter your {{ $fieldLabel }}">
                    @endif
                </div>
            @endforeach
        @else
            {{-- Fallback: If no required_fields, show a default User ID field to prevent form from being empty --}}
            <div>
                <label for="save_id" class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                <input type="text" 
                       id="save_id" 
                       name="save_id" 
                       required
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       placeholder="Enter your User ID">
            </div>
        @endif
        
        <!-- Selected Packs Summary -->
        <div id="selected-pack-info" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-700">{{ __('game.selected_pack') }} <span id="selected-count-text">(0)</span></span>
            </div>
            <div id="selected-packs-list" class="space-y-2 max-h-48 overflow-y-auto">
                <!-- Selected packs will be listed here -->
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
                @if(!empty($item4gamerUnavailable)) disabled @endif
                class="w-full {{ !empty($item4gamerUnavailable) ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700' }} text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed">
            {{ !empty($item4gamerUnavailable) ? 'Not Available' : __('game.buy_now') }}
        </button>
    </form>
</div>


