<div class="border-2 border-gray-200 rounded-xl p-4 hover:shadow-lg transition-all bg-white group">
    <div class="relative mb-4">
        <img src="{{ asset($image->image_path) }}" 
             alt="{{ $image->alt_text ?? 'Game image' }}" 
             class="w-full h-48 object-contain bg-gray-50 rounded-lg border border-gray-200">
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="px-2 py-1 bg-black bg-opacity-75 text-white text-xs font-semibold rounded">
                {{ __('game_content.display_order') }}: {{ $image->display_order }}
            </span>
        </div>
    </div>
    
    <div class="space-y-2 text-sm">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700">{{ __('game_content.section') }}:</span>
            @if($image->image_type === 'about')
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">{{ __('game_content.section_about') }}</span>
            @elseif($image->image_type === 'instruction')
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">{{ __('game_content.section_instruction') }}</span>
            @elseif($image->image_type === 'how_to_topup')
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-semibold">{{ __('game_content.section_how_to_topup') }}</span>
            @else
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-semibold">{{ ucfirst($image->image_type) }}</span>
            @endif
        </div>
        
        @if($image->alt_text)
        <div>
            <span class="font-semibold text-gray-700">{{ __('game_content.alt_text') }}:</span>
            <span class="text-gray-600 text-xs ml-1">{{ strlen($image->alt_text) > 30 ? substr($image->alt_text, 0, 30) . '...' : $image->alt_text }}</span>
        </div>
        @endif
        
        @if($image->title)
        <div>
            <span class="font-semibold text-gray-700">{{ __('game_content.title_caption') }}:</span>
            <span class="text-gray-600 text-xs ml-1">{{ strlen($image->title) > 30 ? substr($image->title, 0, 30) . '...' : $image->title }}</span>
        </div>
        @endif
    </div>
    
    <form action="{{ route('admin.game-content.images.delete', [$game, $image]) }}" 
          method="POST" 
          class="mt-4"
          onsubmit="return confirm('{{ __('common.delete') }}?');">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            <span>{{ __('common.delete') }}</span>
        </button>
    </form>
</div>

