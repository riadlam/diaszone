<div class="fi-wi-widget">
    <div @class([
        'rounded-xl border p-5',
        'border-danger-300 bg-danger-50 dark:border-danger-700 dark:bg-danger-950/40' => $this->getPaused(),
        'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' => ! $this->getPaused(),
    ])>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                    @if ($this->getPaused())
                        Website is paused
                    @else
                        Website is live
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if ($this->getPaused())
                        Customers see “We’ll be back soon” on every public page. Checkout is blocked.
                        @if ($this->getStatus()['paused_by'])
                            Paused by {{ $this->getStatus()['paused_by'] }}.
                        @endif
                    @else
                        Pause the storefront during maintenance so nobody can top up until you go live again. Admin stays available.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($this->getPaused())
                    <button
                        type="button"
                        wire:click="resumeSite"
                        wire:confirm="Take the website live? Customers will be able to browse and place orders again."
                        class="fi-btn fi-btn-color-success relative inline-flex items-center justify-center gap-1.5 rounded-lg bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-500"
                    >
                        Go live
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="pauseSite"
                        wire:confirm="Pause the website? Every public page and checkout will show We’ll be back soon. The admin panel stays open."
                        class="fi-btn fi-btn-color-danger relative inline-flex items-center justify-center gap-1.5 rounded-lg bg-danger-600 px-4 py-2 text-sm font-semibold text-white hover:bg-danger-500"
                    >
                        Pause website
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
