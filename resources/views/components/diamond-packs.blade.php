<div class="space-y-4">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Diamond Packs</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($packs as $index => $pack)
            <label class="diamond-pack-item cursor-pointer">
                <input type="radio" 
                       name="diamond_pack" 
                       value="{{ $pack->id }}" 
                       class="hidden pack-radio"
                       {{ $index === 0 ? 'checked' : '' }}
                       data-pack-id="{{ $pack->id }}"
                       data-pack-diamonds="{{ $pack->diamonds }}"
                       data-pack-bonus="{{ $pack->bonus_diamonds }}"
                       data-pack-price="{{ $pack->price }}"
                       data-pack-discount="{{ $pack->discount_percentage }}">
                
                <div class="SKU_type bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-purple-500 transition-all">
                    <div class="flex items-start gap-4">
                        <!-- Diamond Image -->
                        <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg">
                            @php
                                $imageName = 'diaslow.webp';
                                if ($pack->diamonds >= 2000) {
                                    $imageName = 'diasbigbig.webp';
                                } elseif ($pack->diamonds >= 500) {
                                    $imageName = 'diaslarge.webp';
                                } elseif ($pack->diamonds >= 100) {
                                    $imageName = 'diasmid.webp';
                                }
                            @endphp
                            <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                 alt="{{ $pack->diamonds }} Diamonds" 
                                 class="w-full h-full object-contain"
                                 style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                        </div>
                        
                        <!-- Pack Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $pack->diamonds }} Diamonds</h3>
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded">{{ $pack->discount_percentage }}% OFF</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mb-2">+ {{ $pack->bonus_diamonds }} Bonus Diamonds</p>
                            <div class="flex items-center justify-between">
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs text-gray-400 line-through">US$ {{ number_format($pack->price, 2) }}</span>
                                @endif
                                <span class="text-sm font-bold text-purple-600">US$ {{ number_format($pack->price * (1 - $pack->discount_percentage / 100), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>

