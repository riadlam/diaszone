<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->stats() as $stat)
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $stat['value'] }}
                </p>

                @if (filled($stat['hint']))
                    <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $stat['hint'] }}">
                        {{ $stat['hint'] }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    {{ $this->table }}
</x-filament-panels::page>
