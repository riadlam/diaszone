@extends('layouts.seller')

@section('title', 'Direct Top-Up - Seller Panel')
@section('header', 'Direct Top-Up')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form -->
    <div class="bg-slate-800 rounded-xl p-6">
        <h3 class="text-lg font-bold mb-6">Quick Recharge</h3>
        
        <form action="{{ route('seller.direct-topup.process') }}" method="POST" id="topup-form" class="space-y-5">
            @csrf
            
            <!-- Game Selection -->
            <div>
                <label class="block text-gray-300 text-sm mb-2">Select Game</label>
                <select name="game_type" id="game_type" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                    <option value="">Choose a game...</option>
                    @foreach($gameTypes as $type)
                        <option value="{{ $type }}">
                            {{ ucfirst(str_replace('mobilelegends', 'Mobile Legends', str_replace('freefire', 'Free Fire', str_replace('pubgmobile', 'PUBG Mobile', str_replace('honorofkings', 'Honor of Kings', str_replace('bloodstrike', 'Blood Strike', $type)))))) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Pack Selection -->
            <div id="pack-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2">Select Pack</label>
                <select name="pack_id" id="pack_id" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                    <option value="">Choose a pack...</option>
                </select>
            </div>
            
            <!-- Player ID -->
            <div id="player-id-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2">Player ID</label>
                <input type="text" name="player_id" id="player_id" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none"
                    placeholder="Enter player ID">
            </div>
            
            <!-- Zone ID (for ML/BS) -->
            <div id="zone-id-container" class="hidden">
                <label class="block text-gray-300 text-sm mb-2" id="zone-label">Zone ID</label>
                <input type="text" name="zone_id" id="zone_id"
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none"
                    placeholder="Enter zone ID">
            </div>
            
            <!-- Summary -->
            <div id="summary" class="hidden bg-slate-700/50 rounded-lg p-4">
                <h4 class="font-medium text-gray-300 mb-3">Order Summary</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Pack:</span>
                        <span id="summary-pack" class="text-white"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Base Cost:</span>
                        <span id="summary-cost" class="text-red-400"></span>
                    </div>
                    <div class="flex justify-between border-t border-slate-600 pt-2">
                        <span class="text-gray-400">Your Balance After:</span>
                        <span id="summary-balance" class="text-white font-medium"></span>
                    </div>
                </div>
            </div>
            
            <div id="topup-status" class="mt-3 text-sm text-gray-300 hidden"></div>

            <button type="submit" id="submit-btn" disabled
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Process Top-Up
            </button>
        </form>
    </div>
    
    <!-- Info Card -->
    <div class="space-y-6">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-2">Your Wallet</h3>
            <p class="text-4xl font-bold text-white">{{ number_format($seller->wallet_balance, 0, '.', '') }} DZD</p>
            <p class="text-blue-100 mt-2">Available for direct top-ups</p>
        </div>
        
        <div class="bg-slate-800 rounded-xl p-6">
            <h4 class="font-bold text-white mb-4">How Direct Top-Up Works</h4>
            <div class="space-y-3 text-gray-400 text-sm">
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">1</span>
                    <p>Select the game and diamond pack</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">2</span>
                    <p>Enter the customer's Player ID (and Zone ID if required)</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">3</span>
                    <p>The base price is deducted from your wallet</p>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center flex-shrink-0 text-xs">4</span>
                    <p>Diamonds are instantly delivered to the player!</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4">
            <p class="text-yellow-400 text-sm">
                <strong>⚠️ Important:</strong> Double-check the Player ID before submitting. 
                Diamonds cannot be recovered once sent to the wrong account.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const walletBalance = {{ $seller->wallet_balance }};
let selectedPack = null;

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
    
    packSelect.innerHTML = '<option value="">Choose a pack...</option>';
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
    
    packContainer.classList.remove('hidden');
    playerIdContainer.classList.remove('hidden');
    
    // Show zone ID for ML and BS
    if (gameType === 'mobilelegends') {
        zoneIdContainer.classList.remove('hidden');
        zoneLabel.textContent = 'Zone ID';
        document.getElementById('zone_id').placeholder = 'Enter zone ID';
        document.getElementById('zone_id').required = true;
    } else if (gameType === 'bloodstrike') {
        zoneIdContainer.classList.remove('hidden');
        zoneLabel.textContent = 'Server';
        document.getElementById('zone_id').placeholder = 'e.g., Global';
        document.getElementById('zone_id').required = false;
    } else {
        zoneIdContainer.classList.add('hidden');
        document.getElementById('zone_id').required = false;
    }
});

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
});

document.getElementById('topup-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const statusEl = document.getElementById('topup-status');
    statusEl.classList.add('hidden');

    const playerId = document.getElementById('player_id').value;
    if (!playerId) {
        statusEl.classList.remove('hidden');
        statusEl.textContent = 'Please enter a Player ID';
        statusEl.classList.add('text-red-400');
        return;
    }

    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Processing...';

    const formData = new FormData(this);

    // Clear previous status
    statusEl.classList.remove('text-red-400', 'text-green-400');

    try {
        const resp = await fetch(this.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });

        const json = await resp.json().catch(() => ({ success: false, message: 'Invalid server response' }));

        if (resp.ok && json.success) {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-green-400');
            statusEl.textContent = `Top-up completed — Order #${json.order_number}`;

            // Keep the button disabled briefly to avoid double-click; update UI
            submitBtn.textContent = 'Completed';
            setTimeout(() => { submitBtn.disabled = false; submitBtn.textContent = originalText; }, 2000);

            // Optionally: reset form after success
            document.getElementById('summary').classList.add('hidden');
            this.reset();
        } else {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-red-400');
            statusEl.textContent = json.message || 'Top-up failed. Please try again.';

            // Re-enable button so seller can try again
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (err) {
        statusEl.classList.remove('hidden');
        statusEl.classList.add('text-red-400');
        statusEl.textContent = 'Network error. Please try again.';
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});
</script>
@endpush
