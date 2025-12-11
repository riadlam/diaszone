<div class="relative language-dropdown">
    <button type="button" aria-haspopup="listbox" aria-expanded="false" class="language-dropdown-toggle flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-slate-700 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-400 relative z-[99999] pointer-events-auto">
        @php
            $currentLocale = app()->getLocale();
            $localeData = [
                'en' => ['flag' => '🇬🇧', 'code' => 'EN'],
                'ar' => ['flag' => '🇩🇿', 'code' => 'AR'],
                'fr' => ['flag' => '🇫🇷', 'code' => 'FR']
            ];
            $current = $localeData[$currentLocale] ?? $localeData['en'];
        @endphp
        <span class="text-xl leading-none" style="font-size: 1.25rem;">{{ $current['flag'] }}</span>
        <span class="text-sm font-semibold text-white">{{ $current['code'] }}</span>
        <svg class="w-4 h-4 text-gray-300 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div class="language-dropdown-menu absolute right-0 mt-2 w-56 bg-slate-800 rounded-xl shadow-2xl border border-slate-700 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden pointer-events-auto" role="listbox" tabindex="-1" style="min-width: 14rem;">
        <div class="px-3 py-2 border-b border-slate-700">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('language.title') }}</span>
        </div>
        <a href="{{ route('language.switch', 'en') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-slate-700 transition-all duration-200 group {{ app()->getLocale() == 'en' ? 'bg-slate-700' : '' }}">
            <span class="text-2xl leading-none">🇬🇧</span>
            <div class="flex-1">
                <div class="text-sm font-semibold text-white">English</div>
                <div class="text-xs text-gray-400">{{ __('language.en') }}</div>
            </div>
            <span class="text-xs font-medium {{ app()->getLocale() == 'en' ? 'text-cyan-400 bg-cyan-900/10' : 'text-gray-400 bg-gray-800/20' }} px-2 py-1 rounded">EN</span>
        </a>
        <a href="{{ route('language.switch', 'ar') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-slate-700 transition-all duration-200 group {{ app()->getLocale() == 'ar' ? 'bg-slate-700' : '' }}">
            <span class="text-2xl leading-none">🇩🇿</span>
            <div class="flex-1">
                <div class="text-sm font-semibold text-white">العربية</div>
                <div class="text-xs text-gray-400">{{ __('language.ar') }}</div>
            </div>
            <span class="text-xs font-medium {{ app()->getLocale() == 'ar' ? 'text-cyan-400 bg-cyan-900/10' : 'text-gray-400 bg-gray-800/20' }} px-2 py-1 rounded">AR</span>
        </a>
        <a href="{{ route('language.switch', 'fr') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-slate-700 transition-all duration-200 group {{ app()->getLocale() == 'fr' ? 'bg-slate-700' : '' }}">
            <span class="text-2xl leading-none">🇫🇷</span>
            <div class="flex-1">
                <div class="text-sm font-semibold text-white">Français</div>
                <div class="text-xs text-gray-400">{{ __('language.fr') }}</div>
            </div>
            <span class="text-xs font-medium {{ app()->getLocale() == 'fr' ? 'text-cyan-400 bg-cyan-900/10' : 'text-gray-400 bg-gray-800/20' }} px-2 py-1 rounded">FR</span>
        </a>
    </div>
</div>
