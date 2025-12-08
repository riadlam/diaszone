@extends('layouts.seller')

@section('title', 'Packs & Pricing - Seller Panel')
@section('header', 'Packs & Pricing')

@section('content')
<!-- Game Selector -->
<div class="mb-6">
    <div class="flex flex-wrap gap-2">
        @foreach($gameTypes as $type)
            <a href="{{ route('seller.packs', ['game' => $type]) }}" 
               class="px-4 py-2 rounded-lg font-medium transition {{ $gameType === $type ? 'bg-blue-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600' }}">
                {{ ucfirst(str_replace('mobilelegends', 'Mobile Legends', str_replace('freefire', 'Free Fire', str_replace('pubgmobile', 'PUBG Mobile', str_replace('honorofkings', 'Honor of Kings', str_replace('bloodstrike', 'Blood Strike', $type)))))) }}
            </a>
        @endforeach
    </div>
</div>

<!-- Info Box -->
<div class="mb-6 p-4 bg-blue-600/20 border border-blue-500/30 rounded-lg">
    <p class="text-blue-300">
        <strong>💡 Tip:</strong> Set your custom prices for each pack. Your price must be at least the base price. 
        The difference between your price and the base price is your profit.
    </p>
</div>

<!-- Packs Form -->
<form action="{{ route('seller.packs.update-prices') }}" method="POST">
    @csrf
    
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-700">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-300">Pack</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Diamonds</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Base Price (DZD)</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Your Price (DZD)</th>
                            <!-- Base Price (USD) removed per request -->
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Flexy Price (DZD)</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @foreach($packs as $pack)
                        @php
                            $sellerPrice = $sellerPrices->get($pack->id);
                            $customDzd = $sellerPrice ? $sellerPrice->custom_price_dzd : $pack->price_dzd;
                            $flexyPrice = $sellerPrice ? $sellerPrice->flexy_price ?? '' : '';
                            $baseDzd = $pack->base_price_dzd ?? $pack->price_dzd;
                            // Flexy price is now expressed in DZD: minimum is the pack's base price
                            $minFlexyDzd = $baseDzd;
                            $isActive = $sellerPrice ? $sellerPrice->is_active : true;
                        @endphp
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-4">
                                <input type="hidden" name="prices[{{ $loop->index }}][pack_id]" value="{{ $pack->id }}">
                                <div>
                                    <p class="font-medium text-white">{{ $pack->name }}</p>
                                    {{-- pack code hidden from UI by request --}}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-cyan-400 font-medium">{{ $pack->diamonds }}</span>
                                @if($pack->bonus_diamonds > 0)
                                    <span class="text-green-400 text-sm">+{{ $pack->bonus_diamonds }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center text-gray-400">
                                {{ number_format($pack->base_price_dzd ?? $pack->price_dzd, 2) }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                    <input type="number" step="0.01" min="{{ $baseDzd }}" 
                                       name="prices[{{ $loop->index }}][price_dzd]" 
                                       value="{{ $customDzd }}"
                                       class="w-28 px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-center focus:border-blue-500 outline-none">
                            </td>
                            <td class="px-4 py-4 text-center">
                                    <input type="number" step="0.01" min="{{ $minFlexyDzd }}" 
                                       name="prices[{{ $loop->index }}][flexy_price]" 
                                       value="{{ $flexyPrice }}"
                                       class="w-28 px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-center focus:border-blue-500 outline-none">
                            </td>
                            <!-- Profit column removed per request -->
                            <td class="px-4 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                     <!-- Always submit a 0 by default, the checkbox will submit 1 when checked -->
                                     <input type="hidden" name="prices[{{ $loop->index }}][is_active]" value="0">
                                     <input type="checkbox" name="prices[{{ $loop->index }}][is_active]" value="1" 
                                         {{ $isActive ? 'checked' : '' }}
                                         class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="seller-pack-errors" class="mb-4 hidden bg-red-800/20 border border-red-600/30 text-red-200 p-3 rounded-lg"></div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
            Save All Prices
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('seller.packs.update-prices') }}"]');
    if (!form) return;

    form.addEventListener('submit', function (e) {
                // Flexy price is DZD, compare values directly to base DZD
        const rows = form.querySelectorAll('table tbody tr');
        const errors = [];

        rows.forEach(function (row, index) {
            const basePriceCell = row.querySelector('td:nth-child(3)');
            const baseDzd = basePriceCell ? parseFloat(basePriceCell.textContent.replace(/[^0-9.-]+/g, '')) : 0;

            const priceDzdInput = row.querySelector('input[name*="[price_dzd]"]');
            if (priceDzdInput && priceDzdInput.value !== '') {
                const val = parseFloat(priceDzdInput.value);
                if (isNaN(val) || val < baseDzd) {
                    const packName = row.querySelector('td:first-child p')?.textContent?.trim() || `row ${index+1}`;
                    errors.push(`${packName}: DZD price must be at least ${baseDzd} DZD`);
                }
            }

            const flexyInput = row.querySelector('input[name*="[flexy_price]"]');
            if (flexyInput && flexyInput.value !== '') {
                const fVal = parseFloat(flexyInput.value);
                if (isNaN(fVal) || fVal < baseDzd) {
                    const packName = row.querySelector('td:first-child p')?.textContent?.trim() || `row ${index+1}`;
                    errors.push(`${packName}: Flexy price must be at least ${baseDzd} DZD`);
                }
            }
        });

        if (errors.length) {
            e.preventDefault();
            const errBox = document.getElementById('seller-pack-errors');
            errBox.innerHTML = errors.map(err => `<div class="py-1">${err}</div>`).join('');
            errBox.classList.remove('hidden');
            errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
@endpush
