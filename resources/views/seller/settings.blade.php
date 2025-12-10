@extends('layouts.seller')

@section('title', 'Settings - Seller Panel')
@section('header', 'Settings')

@section('content')
<form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2">
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700/50">
                <h3 class="text-lg font-bold mb-4">Website Controls</h3>

                <div class="space-y-4">
                    <!-- Store images: logo and banner -->
                    <div class="p-4 bg-slate-700/20 rounded-lg flex flex-col gap-4">
                        <div>
                            <label class="text-xs text-gray-400 mb-2 block">Store Logo (circle)</label>
                            <div class="flex items-center gap-3">
                                <div class="relative w-16 h-16 rounded-full overflow-hidden bg-slate-900 flex items-center justify-center border border-slate-700">
                                    @if($seller->store_logo_thumb ?? $seller->store_logo)
                                        <img id="store-logo-preview" src="{{ storage_public_url($seller->store_logo_thumb ?? $seller->store_logo) }}" class="w-full h-full object-cover" alt="logo">
                                    @else
                                        <span id="store-logo-placeholder" class="text-white font-bold">{{ substr($seller->name, 0, 1) }}</span>
                                    @endif
                                    @if($seller->store_logo)
                                        <button id="remove-logo-btn" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">✕</button>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <!-- hidden real input; custom controls below to avoid 'No file chosen' text -->
                                    <input id="store-logo-input" type="file" name="store_logo" accept="image/*" class="hidden">
                                    <div class="flex items-center gap-2">
                                        <button id="choose-logo-btn" type="button" class="px-3 py-1 rounded-lg bg-blue-600 text-white text-xs">Change Logo</button>
                                        <span id="store-logo-filename" class="text-xs text-gray-300">{{ $seller->store_logo ? basename($seller->store_logo) : 'No file chosen' }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Recommended: square image, 400×400. Max 5 MB.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-400 mb-2 block">Cover banner (mobile only)</label>
                            <div class="rounded-lg overflow-hidden border border-slate-700 bg-slate-800">
                                @if($seller->store_banner_resized ?? $seller->store_banner)
                                        <div class="relative w-full h-36 overflow-hidden">
                                            <img id="store-banner-preview" src="{{ storage_public_url($seller->store_banner_resized ?? $seller->store_banner) }}" class="w-full h-full object-cover" alt="banner">
                                            @if($seller->store_banner)
                                                <button id="remove-banner-btn" type="button" class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs rounded">Remove</button>
                                            @endif
                                        </div>
                                @else
                                    <div id="store-banner-placeholder" class="w-full h-36 bg-gradient-to-r from-slate-700 via-slate-800 to-slate-700 flex items-center justify-center sm:hidden">
                                        <span class="text-gray-400 text-sm">No banner — mobile-only header will show a placeholder</span>
                                    </div>
                                @endif
                            </div>
                            <!-- hidden real file input and custom controls to show filename + preview -->
                            <input id="store-banner-input" type="file" name="store_banner" accept="image/*" class="hidden">
                            <div class="flex items-center gap-2 mt-2">
                                <button id="choose-banner-btn" type="button" class="px-3 py-1 rounded-lg bg-blue-600 text-white text-xs">Change banner</button>
                                <span id="store-banner-filename" class="text-xs text-gray-300">{{ $seller->store_banner ? basename($seller->store_banner) : 'No file chosen' }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Recommended: wide image, 800×300. Max 8 MB.</p>
                        </div>
                    </div>
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
                            <span id="store-prefix" class="px-3 py-2 text-slate-400 text-xs select-none bg-slate-900/30 border-r border-slate-700">https://diaszone.com/store/</span>
                            <input id="store-slug-input" type="text" name="website_url" value="{{ old('website_url', $seller->website_url ?? $seller->username) }}" placeholder="sellerriad" class="flex-1 px-3 py-2 bg-transparent text-white outline-none" aria-describedby="store-prefix" data-original="{{ $seller->website_url ?? $seller->username }}">
                        
                        <div class="flex items-center justify-between gap-3">
                            <p id="store-slug-error" class="text-xs text-red-400 mt-1 hidden">Store slug is required when website is enabled.</p>
                            <div id="store-slug-status" class="text-xs mt-1 ml-auto flex items-center gap-2 text-gray-400 hidden">
                                <svg id="store-slug-spinner" class="w-4 h-4 animate-spin text-gray-400 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                <span id="store-slug-status-text" class="hidden"></span>
                            </div>
                        </div>
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
                        <input id="flexy-number-input" type="text" name="flexy_number" value="{{ old('flexy_number', $seller->flexy_number ?? '') }}" placeholder="e.g., 0673771763" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white outline-none mb-2">
                        <p id="flexy-number-error" class="text-xs text-red-400 mt-1 hidden">Flexy number is required when Flexy is enabled.</p>
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
    // Simple toast utility used by many seller pages — tiny copy from orders view
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
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

    // Prevent form submission if flexy is enabled but flexy_number is empty
    const settingsForm = document.querySelector('form[action="{{ route('seller.settings.update') }}"]');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function (e) {
            // Website slug required if website is enabled
            const websiteToggle = document.getElementById('website-enabled-toggle');
            const slugInputEl = document.getElementById('store-slug-input');
            const slugErrorEl = document.getElementById('store-slug-error');
            if (websiteToggle && websiteToggle.checked) {
                if (!slugInputEl || slugInputEl.value.trim() === '') {
                    e.preventDefault();
                    if (slugErrorEl) {
                        slugErrorEl.classList.remove('hidden');
                        slugErrorEl.classList.add('animate-pulse');
                        setTimeout(() => slugErrorEl.classList.remove('animate-pulse'), 800);
                    }
                    slugInputEl?.focus();
                    return false;
                }
            }
            const flexyToggle = document.getElementById('flexy-enabled-toggle');
            const flexyNumber = document.getElementById('flexy-number-input');
            const errorEl = document.getElementById('flexy-number-error');
            if (flexyToggle && flexyToggle.checked) {
                if (!flexyNumber || flexyNumber.value.trim() === '') {
                    e.preventDefault();
                    if (errorEl) {
                        errorEl.classList.remove('hidden');
                        // brief pulse to draw attention
                        errorEl.classList.add('animate-pulse');
                        setTimeout(() => errorEl.classList.remove('animate-pulse'), 800);
                    }
                    flexyNumber?.focus();
                    return false;
                }
            }
            // hide errors if present and valid
            if (errorEl) errorEl.classList.add('hidden');
            if (slugErrorEl) slugErrorEl.classList.add('hidden');
            return true;
        });
    }

    // Debounced AJAX check for slug availability
    function debounce(fn, wait) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    async function checkSlugAvailability(slug) {
        const statusEl = document.getElementById('store-slug-status');
        const spinner = document.getElementById('store-slug-spinner');
        const statusText = document.getElementById('store-slug-status-text');
        if (!statusEl || !statusText || !spinner) return;

        if (!slug) {
            statusEl.classList.add('hidden');
            statusText.classList.add('hidden');
            spinner.classList.add('hidden');
            return;
        }

        statusEl.classList.remove('hidden');
        statusText.classList.add('hidden');
        spinner.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('seller.settings.check-slug') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ slug })
            });

            const json = await res.json();
            spinner.classList.add('hidden');
            statusText.classList.remove('hidden');
            if (json.available) {
                statusText.textContent = 'Available';
                statusText.classList.remove('text-red-400');
                statusText.classList.add('text-green-400');
            } else {
                statusText.textContent = json.message || 'Taken';
                statusText.classList.remove('text-green-400');
                statusText.classList.add('text-red-400');
            }
        } catch (e) {
            spinner.classList.add('hidden');
            statusText.classList.remove('hidden');
            statusText.textContent = 'Check failed';
            statusText.classList.remove('text-green-400');
            statusText.classList.add('text-red-400');
        }
    }

    const debouncedCheckSlug = debounce((val) => checkSlugAvailability(val), 450);

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

        // run debounced availability check for allowed slug (lowercase, cleaned)
        const cleaned = slug.toLowerCase().replace(/[^a-z0-9_-]+/g, '');
        const original = (slugInput?.dataset?.original ?? '').toString().toLowerCase().replace(/[^a-z0-9_-]+/g, '');
        // Only check availability when user has changed the slug from the original value
        if (cleaned.length > 0 && cleaned !== original && !/[^a-z0-9_-]/i.test(slug)) {
            debouncedCheckSlug(cleaned);
        } else {
            // hide status if invalid, empty, or unchanged
            checkSlugAvailability('');
        }
    }

    slugInput?.addEventListener('input', updatePreview);
    // initialise preview when the page loads
    updatePreview();

    // Image previews for logo/banner
    const logoInput = document.getElementById('store-logo-input');
    const logoPreview = document.getElementById('store-logo-preview');
    const logoPlaceholder = document.getElementById('store-logo-placeholder');

    logoInput?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        if (logoPreview) {
            logoPreview.src = url;
            logoPreview.classList.remove('hidden');
        }
        if (logoPlaceholder) logoPlaceholder.classList.add('hidden');
        // update filename UI
        const fn = document.getElementById('store-logo-filename');
        if (fn && this.files && this.files[0]) fn.textContent = this.files[0].name;
    });

    const bannerInput = document.getElementById('store-banner-input');
    const bannerPreview = document.getElementById('store-banner-preview');
    const bannerPlaceholder = document.getElementById('store-banner-placeholder');
    bannerInput?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        if (bannerPreview) {
            bannerPreview.src = url;
            bannerPreview.classList.remove('hidden');
        }
        if (bannerPlaceholder) bannerPlaceholder.classList.add('hidden');
        // update filename UI
        const bf = document.getElementById('store-banner-filename');
        if (bf && this.files && this.files[0]) bf.textContent = this.files[0].name;
    });

    // wire custom choose buttons to hidden input
    document.getElementById('choose-logo-btn')?.addEventListener('click', function () {
        document.getElementById('store-logo-input')?.click();
    });
    document.getElementById('choose-banner-btn')?.addEventListener('click', function () {
        document.getElementById('store-banner-input')?.click();
    });

    // Remove image handlers
    document.getElementById('remove-logo-btn')?.addEventListener('click', async function () {
        if (!confirm('Remove store logo?')) return;
        const res = await fetch('{{ route('seller.settings.remove-image') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ type: 'logo' })
        });
        if (res.ok) {
            // remove preview
            document.getElementById('store-logo-preview')?.remove();
            document.getElementById('store-logo-placeholder')?.classList.remove('hidden');
            this.remove();
        }
    });

    document.getElementById('remove-banner-btn')?.addEventListener('click', async function () {
        if (!confirm('Remove store banner?')) return;
        const res = await fetch('{{ route('seller.settings.remove-image') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ type: 'banner' })
        });
        if (res.ok) {
            document.getElementById('store-banner-preview')?.remove();
            document.getElementById('store-banner-placeholder')?.classList.remove('hidden');
            this.remove();
        }
    });

    // Show server-side errors and success messages as toasts for better UX
    (function showServerFlash() {
        try {
            const serverErrors = @json($errors->all());
            if (Array.isArray(serverErrors) && serverErrors.length) {
                serverErrors.forEach(m => showToast(m, 'error'));
            }
            const successMsg = @json(session('success'));
            if (successMsg) showToast(successMsg, 'success');
        } catch (e) {
            // ignore errors if JSON could not be parsed
        }
    })();
</script>
@endpush
@endsection
