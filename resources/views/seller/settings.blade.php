@extends('layouts.seller')

@section('title', 'Settings - Seller Panel')
@section('header', 'Settings')

@section('content')
<form action="{{ route('seller.settings.update') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2">
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700/50">
                <h3 class="text-lg font-bold mb-4">Website Controls</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-300 font-medium">Enable dynamic website</p>
                            <p class="text-xs text-gray-400">Show or hide your public storefront. When enabled your store will be reachable at the URL below.</p>
                        </div>
                        <div class="flex flex-col items-end">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="website_enabled" value="0">
                                <input type="checkbox" name="website_enabled" value="1" {{ $seller->website_enabled ? 'checked' : '' }} class="sr-only peer" id="website-enabled-toggle">
                                <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <div id="store-url-row" class="p-4 bg-slate-700/20 rounded-lg {{ $seller->website_enabled ? '' : 'hidden' }}">
                        <label class="text-xs text-gray-400 mb-2 block">Store slug (optional — only the last segment)</label>

                        <!-- UX: show non-editable base prefix + editable slug (common multivendor pattern) -->
                        <div class="flex items-center bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
                            <span id="store-prefix" class="px-3 py-2 text-slate-400 text-xs select-none bg-slate-900/30 border-r border-slate-700">{{ rtrim(config('app.url'), '/') }}/store/</span>
                            <input id="store-slug-input" type="text" name="website_url" value="{{ old('website_url', $seller->website_url ?? $seller->username) }}" placeholder="sellerriad" class="flex-1 px-3 py-2 bg-transparent text-white outline-none" aria-describedby="store-prefix">
                        </div>

                        <p id="store-preview" class="text-xs text-gray-500 mt-2">Preview: <a href="{{ $seller->getStoreUrl() }}" target="_blank" class="text-blue-400 underline">{{ $seller->getStoreUrl() }}</a></p>
                        <p id="store-slug-hint" class="text-xs text-gray-400 mt-1">Allowed characters: letters, numbers, hyphen (-) and underscore (_).</p>
                    </div>

                    <!-- Flexy details moved below the flexy_enabled toggle so they appear after the toggle button -->

                    <!-- Simulation switches removed; flexy/state is controlled by flexy_enabled / website_enabled -->

                    <!-- Choose Games section is part of store controls and should be visible only when website_enabled is on -->
                    <div id="store-games" class="p-4 bg-slate-700/30 rounded-lg {{ $seller->website_enabled ? '' : 'hidden' }}">
                        <p class="text-sm text-gray-300 font-medium mb-2">Choose Games to show on your storefront</p>
                        <p class="text-xs text-gray-400 mb-3">Uncheck games you don't want visible on your store. Leave all unchecked to show all.</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($availableGames as $g)
                                @php
                                    $allowed = empty($seller->allowed_games) || in_array($g, $seller->allowed_games);
                                @endphp
                                <label class="inline-flex items-center gap-2 p-2 bg-slate-800/30 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="allowed_games[]" value="{{ $g }}" {{ $allowed ? 'checked' : '' }}>
                                    <span class="text-white text-sm">{{ ucfirst($g) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-4 bg-slate-700/30 rounded-lg flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-300 font-medium">Enable Flexy payment</p>
                            <p class="text-xs text-gray-400">Allow customers to pay via Flexy on your store.</p>
                        </div>
                        <div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="flexy_enabled" value="0">
                                <input type="checkbox" name="flexy_enabled" value="1" {{ $seller->flexy_enabled ? 'checked' : '' }} class="sr-only peer" id="flexy-enabled-toggle">
                                <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-orange-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>

                    <div id="flexy-details" class="p-4 bg-slate-700/30 rounded-lg {{ $seller->flexy_enabled ? '' : 'hidden' }}">
                        <p class="text-sm text-gray-300 font-medium mb-2">Flexy details (optional)</p>
                        <p class="text-xs text-gray-400 mb-2">Number and instructions that will be displayed to customers when they choose Flexy.</p>
                        <input type="text" name="flexy_number" value="{{ old('flexy_number', $seller->flexy_number ?? '') }}" placeholder="e.g., 0673771763" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white outline-none mb-2">
                        <textarea name="flexy_instruction" rows="3" placeholder="Short transfer instructions or account details" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white outline-none">{{ old('flexy_instruction', $seller->flexy_instruction ?? '') }}</textarea>
                    </div>

                </div>

                <div class="mt-6 text-right">
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg font-semibold">Save settings</button>
                </div>
            </div>
        </div>

        <!-- Quick Actions removed per design request -->
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('website-enabled-toggle')?.addEventListener('change', function () {
        const row = document.getElementById('store-url-row');
        const games = document.getElementById('store-games');
        if (this.checked) {
            row.classList.remove('hidden');
            if (games) games.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
            if (games) games.classList.add('hidden');
        }
    });
    // Show/hide flexy details when flexy_enabled toggled
    document.getElementById('flexy-enabled-toggle')?.addEventListener('change', function () {
        const flexyDetails = document.getElementById('flexy-details');
        if (!flexyDetails) return;
        if (this.checked) flexyDetails.classList.remove('hidden'); else flexyDetails.classList.add('hidden');
    });

    // Update preview while editing slug and prevent invalid characters client-side
    const slugInput = document.getElementById('store-slug-input');
    const prefix = document.getElementById('store-prefix');
    const preview = document.getElementById('store-preview');

    function updatePreview() {
        if (!slugInput || !prefix || !preview) return;
        const slug = slugInput.value.trim();
        const full = prefix.textContent + slug;
        preview.innerHTML = `Preview: <a href="${full}" target="_blank" class="text-blue-400 underline">${full}</a>`;
        // simple client-side feedback: if slug contains invalid chars, add warning
        const invalid = /[^a-zA-Z0-9_-]/.test(slug);
        const hint = document.getElementById('store-slug-hint');
        if (hint) {
            hint.classList.toggle('text-red-400', invalid);
            hint.textContent = invalid ? 'Invalid characters detected — only letters, numbers, hyphen and underscore are allowed.' : 'Allowed characters: letters, numbers, hyphen (-) and underscore (_).';
        }
    }

    slugInput?.addEventListener('input', updatePreview);
    // initialise preview when the page loads
    updatePreview();
</script>
@endpush
@endsection
