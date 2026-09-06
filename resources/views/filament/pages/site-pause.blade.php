<x-filament-panels::page>
    <div @class([
        'rounded-xl border p-6',
        'border-danger-300 bg-danger-50 dark:border-danger-700 dark:bg-danger-950/40' => $this->getPaused(),
        'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' => ! $this->getPaused(),
    ])>
        <p class="text-sm text-gray-700 dark:text-gray-300">
            @if ($this->getPaused())
                The public site is paused. Shoppers, checkout, and APIs show a maintenance page. Filament admin, Livewire, and payment/provider webhooks stay up.
            @else
                Use <strong>Pause website</strong> when you need to maintain DiasZone. Nobody will be able to top up until you click <strong>Go live</strong>.
            @endif
        </p>

        @if ($this->getPaused() && $this->getStatus()['paused_at'])
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Paused at {{ \Illuminate\Support\Carbon::parse($this->getStatus()['paused_at'])->timezone('Africa/Algiers')->format('d M Y, H:i') }}
                @if ($this->getStatus()['paused_by'])
                    by {{ $this->getStatus()['paused_by'] }}
                @endif
            </p>
        @endif
    </div>
</x-filament-panels::page>
