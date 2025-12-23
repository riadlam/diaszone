@extends('layouts.seller')

@section('title', __('seller.settings_title'))
@section('header', __('seller.settings'))

@section('content')
<form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 col-span-1">
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700/50">
                <h3 class="text-lg font-bold mb-4">{{ __('seller.website_controls') }}</h3>

                <div class="space-y-4">
                    <!-- Store images: logo and banner -->
                    <div class="p-4 bg-slate-700/20 rounded-lg flex flex-col gap-4">
                        <div>
                            <label class="text-xs text-gray-400 mb-2 block">{{ __('seller.store_logo_label') }}</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <div class="relative w-12 h-12 sm:w-16 sm:h-16 rounded-full overflow-hidden bg-slate-900 flex items-center justify-center border border-slate-700">
                                    @if($seller->store_logo_thumb ?? $seller->store_logo)
                                        <img id="store-logo-preview" src="{{ storage_public_url($seller->store_logo_thumb ?? $seller->store_logo) }}" class="w-full h-full object-cover" alt="logo">
                                    @else
                                        <span id="store-logo-placeholder" class="text-white font-bold">{{ substr($seller->name, 0, 1) }}</span>
                                    @endif
                                    @if($seller->store_logo)
                                        <button id="remove-logo-btn" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">✕</button>
                                    @endif
                                </div>
                                <div class="flex-1 w-full">
                                    <!-- hidden real input; custom controls below to avoid 'No file chosen' text -->
                                    <input id="store-logo-input" type="file" name="store_logo" accept="image/*" class="hidden">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <button id="choose-logo-btn" type="button" class="w-full sm:w-auto px-3 py-1 rounded-lg bg-blue-600 text-white text-xs">{{ __('seller.change_logo') }}</button>
                                        <span id="store-logo-filename" class="text-xs text-gray-300">{{ $seller->store_logo ? basename($seller->store_logo) : __('seller.no_file_chosen') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">{{ __('seller.logo_recommendation') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-400 mb-2 block">{{ __('seller.cover_banner_label') }}</label>
                            <div class="rounded-lg overflow-hidden border border-slate-700 bg-slate-800">
                                @if($seller->store_banner_resized ?? $seller->store_banner)
                                        <div class="relative w-full h-28 sm:h-36 overflow-hidden">
                                            <img id="store-banner-preview" src="{{ storage_public_url($seller->store_banner_resized ?? $seller->store_banner) }}" class="w-full h-full object-cover" alt="banner">
                                            @if($seller->store_banner)
                                                <button id="remove-banner-btn" type="button" class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs rounded">{{ __('seller.remove') }}</button>
                                            @endif
                                        </div>
                                @else
                                    <div id="store-banner-placeholder" class="w-full h-28 sm:h-36 bg-gradient-to-r from-slate-700 via-slate-800 to-slate-700 flex items-center justify-center sm:hidden">
                                        <span class="text-gray-400 text-sm">{{ __('seller.no_banner_placeholder') }}</span>
                                    </div>
                                @endif
                            </div>
                            <!-- hidden real file input and custom controls to show filename + preview -->
                            <input id="store-banner-input" type="file" name="store_banner" accept="image/*" class="hidden">
                            <div class="flex items-center gap-2 mt-2">
                                <button id="choose-banner-btn" type="button" class="w-full sm:w-auto px-3 py-1 rounded-lg bg-blue-600 text-white text-xs">{{ __('seller.change_banner') }}</button>
                                <span id="store-banner-filename" class="text-xs text-gray-300">{{ $seller->store_banner ? basename($seller->store_banner) : __('seller.no_file_chosen') }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('seller.banner_recommendation') }}</p>
                        </div>
                    </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-300 font-medium">{{ __('seller.enable_dynamic_website') }}</p>
                            <p class="text-xs text-gray-400">{{ __('seller.website_toggle_description') }}</p>
                        </div>
                        <div class="flex flex-col sm:items-end items-start sm:items-end">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="website_enabled" value="0">
                                <input type="checkbox" name="website_enabled" value="1" {{ $seller->website_enabled ? 'checked' : '' }} class="sr-only peer" id="website-enabled-toggle">
                                <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <div id="store-url-row" class="p-4 bg-slate-700/20 rounded-lg {{ $seller->website_enabled ? '' : 'hidden' }}">
                        <label class="text-xs text-gray-400 mb-2 block">{{ __('seller.store_slug_optional') }}</label>

                        <!-- UX: show non-editable base prefix + editable slug (common multivendor pattern) -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center bg-slate-800 border border-slate-700 rounded-lg overflow-hidden">
                            <span id="store-prefix" class="px-3 py-2 text-slate-400 text-xs select-none bg-slate-900/30 sm:border-r border-slate-700">https://diaszone.com/store/</span>
                            <input id="store-slug-input" type="text" name="website_url" value="{{ old('website_url', $seller->website_url ?? $seller->username) }}" placeholder="sellerriad" class="flex-1 px-3 py-2 bg-transparent text-white outline-none" aria-describedby="store-prefix" data-original="{{ $seller->website_url ?? $seller->username }}">
                        
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <p id="store-slug-error" class="text-xs text-red-400 mt-1 hidden">{{ __('seller.store_slug_required') }}</p>
                            <div id="store-slug-status" class="text-xs mt-1 sm:ml-auto w-full sm:w-auto flex items-center gap-2 text-gray-400 hidden">
                                <svg id="store-slug-spinner" class="w-4 h-4 animate-spin text-gray-400 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                <span id="store-slug-status-text" class="hidden"></span>
                            </div>
                        </div>
                        </div>

                        <p id="store-preview" class="text-xs text-gray-500 mt-2">{{ __('seller.preview') }}: <a href="{{ $seller->getStoreUrl() }}" target="_blank" class="text-blue-400 underline break-words">{{ $seller->getStoreUrl() }}</a></p>
                        <p id="store-slug-hint" class="text-xs text-gray-400 mt-1">{{ __('seller.store_slug_hint') }}</p>
                    </div>

                    <!-- Flexy details moved below the flexy_enabled toggle so they appear after the toggle button -->

                    <!-- Simulation switches removed; flexy/state is controlled by flexy_enabled / website_enabled -->

                    <!-- Choose Games section is part of store controls and should be visible only when website_enabled is on -->
                    <div id="store-games" class="p-4 bg-slate-700/30 rounded-lg {{ $seller->website_enabled ? '' : 'hidden' }}">
                        <p class="text-sm text-gray-300 font-medium mb-2">{{ __('seller.choose_games') }}</p>
                        <p class="text-xs text-gray-400 mb-3">{{ __('seller.choose_games_help') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
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

                    <div class="p-4 bg-slate-700/30 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-300 font-medium">{{ __('seller.enable_flexy_payment') }}</p>
                            <p class="text-xs text-gray-400">{{ __('seller.flexy_payment_description') }}</p>
                        </div>
                        <div class="sm:ml-0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="flexy_enabled" value="0">
                                <input type="checkbox" name="flexy_enabled" value="1" {{ $seller->flexy_enabled ? 'checked' : '' }} class="sr-only peer" id="flexy-enabled-toggle">
                                <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-orange-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>
                        <p id="flexy-toggle-help" class="text-xs text-gray-300 mt-2 hidden">{!! __('seller.flexy_toggle_help', ['packs_route' => route('seller.packs')]) !!}</p>
                        <p id="flexy-enabled-error" class="field-error hidden"></p>

                    <div id="flexy-details" class="p-4 bg-slate-700/30 rounded-lg {{ $seller->flexy_enabled ? '' : 'hidden' }}">
                        <p class="text-sm text-gray-300 font-medium mb-2">{{ __('seller.flexy_details_label') }}</p>
                        <p class="text-xs text-gray-400 mb-2">{{ __('seller.flexy_details_help') }}</p>
                        <input id="flexy-number-input" type="text" name="flexy_number" value="{{ old('flexy_number', $seller->flexy_number ?? '') }}" placeholder="e.g., 0673771763" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white outline-none mb-2">
                        <p id="flexy-number-error" class="text-xs text-red-400 mt-1 hidden">{{ __('seller.flexy_number_required') }}</p>
                        <textarea id="flexy-instruction-input" name="flexy_instruction" rows="3" placeholder="Short transfer instructions or account details" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white outline-none">{{ old('flexy_instruction', $seller->flexy_instruction ?? '') }}</textarea>
                        <p id="flexy-instruction-error" class="field-error hidden">{{ __('seller.flexy_instruction_required') }}</p>
                    </div>

                </div>

                <div class="mt-6">
                    <div class="flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg font-semibold">{{ __('seller.save_settings') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions removed per design request -->
    </div>
</form>

@push('scripts')
<style>
    /* Modern toast styles (tailwind-like) */
    .toast-container { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999; display: flex; flex-direction: column; gap: .5rem; }
    .toast-item { min-width: 260px; max-width: 360px; padding: .6rem .75rem; border-radius: .5rem; box-shadow: 0 10px 30px rgba(2,6,23,0.4); color: #fff; display: flex; align-items: flex-start; gap: .5rem; transform: translateY(-10px); opacity: 0; transition: all .28s cubic-bezier(0.2,0.8,0.2,1); }
    .toast-item.show { transform: translateY(0); opacity: 1; }
    .toast-item.success { background: linear-gradient(90deg,#059669,#10b981); }
    .toast-item.error { background: linear-gradient(90deg,#ef4444,#f97316); }
    .toast-item .toast-msg { flex: 1; font-size: .875rem; line-height: 1.2; }
    .toast-item .toast-close { cursor: pointer; opacity: .9; margin-left: .5rem; }
    .toast-cta { margin-left: .5rem; color: rgba(255,255,255,0.95); text-decoration: underline; font-weight: 600; }
    .field-error { color: #fb7185; font-size: .825rem; margin-top: .375rem; }
</style>
<script>
    // Simple toast utility used by many seller pages — tiny copy from orders view
    // Modern toast system
    const toastContainer = document.createElement('div');
    // Localized static strings used by JS
    const SLUG_AVAILABLE = {!! json_encode(__('seller.slug_available')) !!};
    const SLUG_TAKEN = {!! json_encode(__('seller.slug_taken')) !!};
    const STORE_HINT_ALLOWED = {!! json_encode(__('seller.store_slug_hint')) !!};
    const STORE_HINT_INVALID = {!! json_encode(__('seller.store_slug_invalid')) !!};
    const PREVIEW_LABEL = {!! json_encode(__('seller.preview')) !!};
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
    function showToast(message, type = 'success', options = {}) {
        const toast = document.createElement('div');
        toast.className = `toast-item ${type}`;
        const icon = document.createElement('div');
        icon.className = 'toast-icon';
        icon.innerHTML = type === 'error' ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.9989 6.99902H12.0089" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 9V13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5V19" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        const msg = document.createElement('div');
        msg.className = 'toast-msg';
        msg.textContent = message;
        const close = document.createElement('button');
        close.className = 'toast-close';
        close.innerHTML = '✕';
        close.onclick = () => { toast.remove(); };
        toast.appendChild(icon);
        toast.appendChild(msg);
        if (options.cta) {
            const cta = document.createElement('a');
            cta.className = 'toast-cta';
            cta.href = options.cta.href || '#';
            cta.textContent = options.cta.text || 'Fix it';
            // leave target as same window (open in current app) intentionally
            toast.appendChild(cta);
        }
        toast.appendChild(close);
        toastContainer.appendChild(toast);
        // Animate
        setTimeout(() => toast.classList.add('show'), 30);
        const timeout = options.duration || 4500;
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, timeout);
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
    document.getElementById('flexy-enabled-toggle')?.addEventListener('change', function (e) {
        const flexyDetails = document.getElementById('flexy-details');
        if (!flexyDetails) return;

        // When enabling, show helper and Flexy details; do not run validations here — validations and toasts are shown on Save.
        if (this.checked) {
            flexyDetails.classList.remove('hidden');
            const help = document.getElementById('flexy-toggle-help');
            if (help) help.classList.remove('hidden');
            const flexyNumber = document.getElementById('flexy-number-input');
            flexyNumber?.focus();
        } else {
            // user disabled Flexy; hide details
            flexyDetails.classList.add('hidden');
        }
    });

    // Prevent form submission if flexy is enabled but flexy_number is empty
    const settingsForm = document.querySelector('form[action="{{ route('seller.settings.update') }}"]');
    if (settingsForm) {
        settingsForm.addEventListener('submit', async function (e) {
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

            // Check Flexy fields only when Flexy toggle is checked
            const flexyToggle = document.getElementById('flexy-enabled-toggle');
            const flexyNumber = document.getElementById('flexy-number-input');
            const flexyInstructionEl = document.getElementById('flexy-instruction-input');
            const errorEl = document.getElementById('flexy-number-error');
            const instrEl = document.getElementById('flexy-instruction-error');

            if (flexyToggle && flexyToggle.checked) {
                if (!flexyNumber || flexyNumber.value.trim() === '') {
                    e.preventDefault();
                    if (errorEl) {
                        errorEl.classList.remove('hidden');
                        errorEl.classList.add('animate-pulse');
                        setTimeout(() => errorEl.classList.remove('animate-pulse'), 800);
                    }
                    flexyNumber?.focus();
                    return false;
                }
                if (!flexyInstructionEl || flexyInstructionEl.value.trim() === '') {
                    e.preventDefault();
                    if (instrEl) {
                        instrEl.classList.remove('hidden');
                    }
                    flexyInstructionEl?.focus();
                    return false;
                }

                // NOTE: We no longer call the ajax to check pack completeness here.
                // Pack-level flexy price checks and server-side validations will be handled by the backend on save.
            }

            // hide errors if present and valid
            if (errorEl) errorEl.classList.add('hidden');
            if (instrEl) instrEl.classList.add('hidden');
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
                statusText.textContent = SLUG_AVAILABLE;
                statusText.classList.remove('text-red-400');
                statusText.classList.add('text-green-400');
            } else {
                statusText.textContent = json.message || SLUG_TAKEN;
                statusText.classList.remove('text-green-400');
                statusText.classList.add('text-red-400');
            }
        } catch (e) {
            spinner.classList.add('hidden');
            statusText.classList.remove('hidden');
            statusText.classList.remove('text-green-400');
            statusText.classList.add('text-red-400');
        }
    }

    const debouncedCheckSlug = debounce((val) => checkSlugAvailability(val), 450);

    // Update preview while editing slug and prevent invalid characters client-side
    const slugInput = document.getElementById('store-slug-input');
    const prefix = document.getElementById('store-prefix');
    const preview = document.getElementById('store-preview');
    const statusEl = document.getElementById('store-slug-status');

    function updatePreview() {
        if (!slugInput || !prefix || !preview) return;
        const slug = slugInput.value.trim();
        const full = prefix.textContent + slug;
        preview.innerHTML = `${PREVIEW_LABEL}: <a href="${full}" target="_blank" class="text-blue-400 underline">${full}</a>`;
        // simple client-side feedback: if slug contains invalid chars, add warning
        const invalid = /[^a-zA-Z0-9_-]/.test(slug);
        const hint = document.getElementById('store-slug-hint');
        if (hint) {
            hint.classList.toggle('text-red-400', invalid);
            hint.textContent = invalid ? STORE_HINT_INVALID : STORE_HINT_ALLOWED;
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
        if (!confirm({!! json_encode(__('seller.remove_logo_confirm')) !!})) return;
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
        if (!confirm({!! json_encode(__('seller.remove_banner_confirm')) !!})) return;
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

    // Remove inline errors and borders when user types
    (function attachInlineFieldListeners() {
        const flexyNumber = document.getElementById('flexy-number-input');
        const flexyInstructionEl = document.getElementById('flexy-instruction-input');
        const flexyNumberErrorEl = document.getElementById('flexy-number-error');
        const flexyInstructionErrorEl = document.getElementById('flexy-instruction-error');
        if (flexyNumber) {
            flexyNumber.addEventListener('input', function () {
                if (flexyNumberErrorEl) flexyNumberErrorEl.classList.add('hidden');
                flexyNumber.classList.remove('border-red-400');
            });
        }
        if (flexyInstructionEl) {
            flexyInstructionEl.addEventListener('input', function () {
                if (flexyInstructionErrorEl) flexyInstructionErrorEl.classList.add('hidden');
                flexyInstructionEl.classList.remove('border-red-400');
            });
        }
    })();

    // Show server-side errors and success messages as toasts for better UX
    (function showServerFlash() {
        try {
            const serverErrors = @json($errors->all());
            const serverErrorsMap = @json($errors->messages());
            if (Array.isArray(serverErrors) && serverErrors.length) {
                // show inline errors for specific fields if present
                if (serverErrorsMap.flexy_number && serverErrorsMap.flexy_number.length) {
                    const el = document.getElementById('flexy-number-error');
                    const field = document.getElementById('flexy-number-input');
                    if (el) { el.classList.remove('hidden'); el.textContent = serverErrorsMap.flexy_number.join(' '); }
                    if (field) field.classList.add('border-red-400');
                }
                if (serverErrorsMap.flexy_instruction && serverErrorsMap.flexy_instruction.length) {
                    const el = document.getElementById('flexy-instruction-error');
                    const field = document.getElementById('flexy-instruction-input');
                    if (el) { el.classList.remove('hidden'); el.textContent = serverErrorsMap.flexy_instruction.join(' '); }
                    if (field) field.classList.add('border-red-400');
                }
                if (serverErrorsMap.flexy_enabled && serverErrorsMap.flexy_enabled.length) {
                    const el = document.getElementById('flexy-enabled-error');
                    if (el) { el.classList.remove('hidden'); el.textContent = serverErrorsMap.flexy_enabled.join(' '); }
                }
                // Also show toasts for all server errors so the user sees a compact summary
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
