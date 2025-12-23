@extends('layouts.seller')

@section('title', __('seller.direct_topup_title'))
@section('header', __('seller.direct_topup'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form -->
    <div class="bg-slate-800 rounded-xl p-6">
        <h3 class="text-lg font-bold mb-6">{{ __('seller.quick_recharge') }}</h3>
        
        <form action="{{ route('seller.direct-topup.process') }}" method="POST" id="topup-form" class="space-y-5">
            @csrf
            
            <!-- Game Selection -->
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.select_game') }}</label>
                <select name="game_type" id="game_type" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                    <option value="">{{ __('seller.choose_game_prompt') }}</option>
                    @foreach($gameTypes as $type)
                        <option value="{{ $type }}">
                            {{ ucfirst(str_replace('mobilelegends', 'Mobile Legends', str_replace('freefire', 'Free Fire', str_replace('pubgmobile', 'PUBG Mobile', str_replace('honorofkings', 'Honor of Kings', str_replace('bloodstrike', 'Blood Strike', $type)))))) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Pack Selection -->
            <div id="pack-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.select_pack') }}</label>
                <select name="pack_id" id="pack_id" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                    <option value="">{{ __('seller.choose_pack_prompt') }}</option>
                </select>
                <!-- Mobile friendly pack cards (hidden on md+) -->
                <div id="pack-cards" class="md:hidden mt-3 grid grid-cols-1 gap-3"></div>
            </div>
            
            <!-- Player ID -->
            <div id="player-id-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.player_id') }}</label>
                <input type="text" name="player_id" id="player_id" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none"
                    placeholder="{{ __('seller.enter_player_id_placeholder') }}">
            </div>
            
            <!-- Zone ID (for ML/BS) -->
            <div id="zone-id-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2" id="zone-label">{{ __('seller.zone_id') }}</label>
                <input type="text" name="zone_id" id="zone_id"
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none"
                    placeholder="{{ __('seller.enter_zone_id_placeholder') }}">
            </div>
            
            <!-- Summary -->
            <div id="summary" class="hidden bg-slate-700/50 rounded-lg p-4">
                <h4 class="font-medium text-gray-300 mb-3">{{ __('seller.order_summary') }}</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('seller.pack') }}:</span>
                        <span id="summary-pack" class="text-white"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">{{ __('seller.base_cost') }}:</span>
                        <span id="summary-cost" class="text-red-400"></span>
                    </div>
                    <div class="flex justify-between border-t border-slate-600 pt-2">
                        <span class="text-gray-400">{{ __('seller.your_balance_after') }}:</span>
                        <span id="summary-balance" class="text-white font-medium"></span>
                    </div>
                </div>
            </div>
            
            <div id="topup-status" class="mt-3 text-sm text-gray-300 hidden"></div>

            <button type="submit" id="submit-btn" disabled
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __('seller.process_topup') }}
            </button>
        </form>

        <!-- Mobile sticky action bar removed per request: mobile-submit-btn no longer present -->
    </div>
    
    <!-- Info Card -->
    <div class="space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-2">{{ __('seller.your_wallet') }}</h3>
            <p class="text-4xl font-bold text-white">{{ number_format($seller->wallet_balance, 0, '.', '') }} DZD</p>
            <p class="text-blue-100 mt-2">{{ __('seller.available_for_direct_topups') }}</p>
        </div>
        
        <div class="bg-slate-800 rounded-xl p-6">
            <h4 class="font-bold text-white mb-4">{{ __('seller.how_direct_topup_works') }}</h4>
            <div class="space-y-3 text-gray-400 text-sm">
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">1</span>
                    <p>{{ __('seller.how_direct_topup_step1') }}</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">2</span>
                    <p>{{ __('seller.how_direct_topup_step2') }}</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">3</span>
                    <p>{{ __('seller.how_direct_topup_step3') }}</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">4</span>
                    <p>{{ __('seller.how_direct_topup_step4') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4">
            <p class="text-yellow-400 text-sm">{{ __('seller.check_player_id_warning') }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const walletBalance = {{ $seller->wallet_balance }};
const SELECT_PACK_LABEL = {!! json_encode(__('seller.select_pack')) !!};
let selectedPack = null;
const packCardsEl = document.getElementById('pack-cards');

document.getElementById('game_type').addEventListener('change', async function() {
    const gameType = this.value;
    const packContainer = document.getElementById('pack-container');
    const packSelect = document.getElementById('pack_id');
    const playerIdContainer = document.getElementById('player-id-container');
    const zoneIdContainer = document.getElementById('zone-id-container');
    const zoneLabel = document.getElementById('zone-label');
    
    if (!gameType) {
        packContainer.classList.add('hidden');
        playerIdContainer.classList.add('hidden');
        zoneIdContainer.classList.add('hidden');
        document.getElementById('summary').classList.add('hidden');
        return;
    }
    
    // Fetch packs
    const response = await fetch(`{{ route('seller.api.packs') }}?game_type=${gameType}`);
    const packs = await response.json();
    
    packSelect.innerHTML = '<option value="">{{ __('seller.choose_pack_prompt') }}</option>';
    const tDiamonds = {!! json_encode(__('seller.diamonds')) !!};
    packs.forEach(pack => {
        if (pack.is_active) {
            const option = document.createElement('option');
            option.value = pack.id;
            option.textContent = `${pack.name} - ${pack.base_price_dzd} DZD`;
            option.dataset.name = pack.name;
            option.dataset.cost = pack.base_price_dzd;
            packSelect.appendChild(option);
        }
    });
    // Mobile pack cards
    if (packCardsEl) {
        packCardsEl.innerHTML = '';
        packs.forEach(pack => {
            if (pack.is_active) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full p-3 rounded-xl bg-slate-700/50 flex items-center justify-between text-left';
                btn.setAttribute('data-pack-id', pack.id);
                btn.setAttribute('data-pack-name', pack.name);
                btn.setAttribute('data-pack-cost', pack.base_price_dzd);
                btn.setAttribute('aria-label', `${SELECT_PACK_LABEL} ${pack.name}`);
                btn.innerHTML = `<div><div class='font-medium text-white'>${pack.name}</div><div class='text-xs text-gray-300'>${pack.diamonds} ${tDiamonds}</div></div><div class='text-sm font-bold text-white'>${Number(pack.base_price_dzd).toFixed(2)} DZD</div>`;
                btn.addEventListener('click', function() {
                    packSelect.value = pack.id;
                    packSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    document.getElementById('summary').scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                packCardsEl.appendChild(btn);
            }
        });
    }
    
    packContainer.classList.remove('hidden');
    playerIdContainer.classList.remove('hidden');
    
    // Show zone ID for ML and BS
        if (gameType === 'mobilelegends') {
        zoneIdContainer.classList.remove('hidden');
        zoneLabel.textContent = {!! json_encode(__('seller.zone_id')) !!};
            document.getElementById('zone_id').placeholder = {!! json_encode(__('seller.enter_zone_id_placeholder')) !!};
        document.getElementById('zone_id').required = true;
    } else if (gameType === 'bloodstrike') {
        zoneIdContainer.classList.remove('hidden');
        zoneLabel.textContent = {!! json_encode(__('seller.server')) !!};
            document.getElementById('zone_id').placeholder = {!! json_encode(__('seller.enter_zone_id_placeholder')) !!};
        document.getElementById('zone_id').required = false;
    } else {
        zoneIdContainer.classList.add('hidden');
        document.getElementById('zone_id').required = false;
    }
});

// (Mobile submit button removed; no click handler required)

document.getElementById('pack_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const summary = document.getElementById('summary');
    const submitBtn = document.getElementById('submit-btn');
    
    if (!this.value) {
        summary.classList.add('hidden');
        submitBtn.disabled = true;
        return;
    }
    
    const packName = option.dataset.name;
    const cost = parseFloat(option.dataset.cost);
    const balanceAfter = walletBalance - cost;
    
    document.getElementById('summary-pack').textContent = packName;
    document.getElementById('summary-cost').textContent = '-' + cost.toFixed(2) + ' DZD';
    document.getElementById('summary-balance').textContent = balanceAfter.toFixed(2) + ' DZD';
    
    if (balanceAfter < 0) {
        document.getElementById('summary-balance').classList.add('text-red-400');
        document.getElementById('summary-balance').classList.remove('text-white');
        submitBtn.disabled = true;
    } else {
        document.getElementById('summary-balance').classList.remove('text-red-400');
        document.getElementById('summary-balance').classList.add('text-white');
        submitBtn.disabled = false;
    }
    
    summary.classList.remove('hidden');
    // highlight selected pack card if present and sync mobile submit button state
    if (packCardsEl) {
        packCardsEl.querySelectorAll('button').forEach(b => b.classList.remove('ring-2', 'ring-blue-500'));
        const selected = packCardsEl.querySelector(`button[data-pack-id="${this.value}"]`);
        if (selected) selected.classList.add('ring-2', 'ring-blue-500');
    }
});

document.getElementById('topup-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const statusEl = document.getElementById('topup-status');
    statusEl.classList.add('hidden');

    const playerId = document.getElementById('player_id').value;
    if (!playerId) {
        statusEl.classList.remove('hidden');
        statusEl.textContent = {!! json_encode(__('seller.please_enter_player_id')) !!};
        statusEl.classList.add('text-red-400');
        return;
    }

    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = {!! json_encode(__('seller.processing_text')) !!};

    const formData = new FormData(this);

    // Clear previous status
    statusEl.classList.remove('text-red-400', 'text-green-400');

    try {
        const resp = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        // Try to parse JSON but fall back to text if server returned HTML/redirect
        let json;
        try {
            json = await resp.json();
        } catch (_e) {
            const text = await resp.text().catch(() => null);
            json = { success: false, message: text || 'Invalid server response' };
        }

            if (resp.ok && json.success) {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-green-400');
            statusEl.textContent = {!! json_encode(__('seller.topup_completed')) !!}.replace(':order', json.order_number);

            // Keep the button disabled briefly to avoid double-click; update UI
            submitBtn.textContent = {!! json_encode(__('seller.completed')) !!};
            setTimeout(() => { submitBtn.disabled = false; submitBtn.textContent = originalText; }, 2000);

            // Optionally: reset form after success
            document.getElementById('summary').classList.add('hidden');
            this.reset();
        } else {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-red-400');
            statusEl.textContent = json.message || {!! json_encode(__('seller.topup_failed')) !!};

            // Re-enable button so seller can try again
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (err) {
        statusEl.classList.remove('hidden');
        statusEl.classList.add('text-red-400');
        statusEl.textContent = {!! json_encode(__('seller.network_error')) !!};
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
    }
});
</script>
@endpush
