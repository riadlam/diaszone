@extends('layouts.seller')

@section('title', __('seller.packs_pricing_title'))
@section('header', __('seller.packs_pricing'))

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
    <p class="text-blue-300">{{ __('seller.packs_tip') }}</p>
</div>

<!-- Packs Form -->
<form action="{{ route('seller.packs.update-prices') }}" method="POST">
    @csrf
    
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <!-- Desktop Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-700">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-300">{{ __('seller.pack') }}</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">{{ __('seller.diamonds') }}</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">{{ __('seller.base_price') }} ({{ __('seller.currency') }})</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">{{ __('seller.your_price') }} ({{ __('seller.currency') }})</th>
                            <!-- Base Price (USD) removed per request -->
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">{{ __('seller.flexy_price') }} ({{ __('seller.currency') }})</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-300">{{ __('seller.active') }}</th>
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
                        <tr class="hover:bg-slate-700/50 seller-pack-row" data-base-dzd="{{ $baseDzd }}" data-pack-name="{{ $pack->name }}">
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
                                                    class="w-28 md:w-28 px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-center focus:border-blue-500 outline-none">
                            </td>
                            <td class="px-4 py-4 text-center">
                                                <input type="number" step="0.01" min="{{ $minFlexyDzd }}" 
                                       name="prices[{{ $loop->index }}][flexy_price]" 
                                       value="{{ $flexyPrice }}"
                                                    class="w-28 md:w-28 px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-center focus:border-blue-500 outline-none">
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

        <!-- Mobile Cards -->
        <div class="md:hidden p-4 space-y-3">
            @foreach($packs as $pack)
                @php
                    $sellerPrice = $sellerPrices->get($pack->id);
                    $customDzd = $sellerPrice ? $sellerPrice->custom_price_dzd : $pack->price_dzd;
                    $flexyPrice = $sellerPrice ? $sellerPrice->flexy_price ?? '' : '';
                    $baseDzd = $pack->base_price_dzd ?? $pack->price_dzd;
                    $minFlexyDzd = $baseDzd;
                    $isActive = $sellerPrice ? $sellerPrice->is_active : true;
                @endphp
                <div class="bg-slate-700 rounded-lg p-4 seller-pack-row" data-base-dzd="{{ $baseDzd }}" data-pack-name="{{ $pack->name }}">
                    <input type="hidden" name="prices[{{ $loop->index }}][pack_id]" value="{{ $pack->id }}">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-medium text-white">{{ $pack->name }}</p>
                            <p class="text-xs text-gray-400">{{ $pack->diamonds }} {{ __('seller.diamonds') }} @if($pack->bonus_diamonds > 0) <span class="text-green-400">+{{ $pack->bonus_diamonds }}</span> @endif</p>
                        </div>
                        <div class="text-right text-gray-400">
                            <p class="text-sm">{{ __('seller.base_short') }} {{ number_format($baseDzd, 2) }} {{ __('seller.currency') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <div>
                            <label class="text-xs text-gray-300">{{ __('seller.your_price') }} ({{ __('seller.currency') }})</label>
                            <input type="number" step="0.01" min="{{ $baseDzd }}" name="prices[{{ $loop->index }}][price_dzd]" value="{{ $customDzd }}" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="text-xs text-gray-300">{{ __('seller.flexy_price') }} ({{ __('seller.currency') }})</label>
                            <input type="number" step="0.01" min="{{ $minFlexyDzd }}" name="prices[{{ $loop->index }}][flexy_price]" value="{{ $flexyPrice }}" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="text-xs text-gray-300">{{ __('seller.active') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="prices[{{ $loop->index }}][is_active]" value="0">
                                <input type="checkbox" name="prices[{{ $loop->index }}][is_active]" value="1" {{ $isActive ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <div id="seller-pack-errors" class="mb-4 hidden bg-red-800/20 border border-red-600/30 text-red-200 p-3 rounded-lg"></div>

        <div class="mt-6 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
            {{ __('seller.save_all_prices') }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('seller.packs.update-prices') }}"]');
    if (!form) return;

    const tPriceMin = {!! json_encode(__('seller.error_price_min')) !!};
    const tFlexyMin = {!! json_encode(__('seller.error_flexy_price_min')) !!};

    form.addEventListener('submit', function (e) {
                // Flexy price is DZD, compare values directly to base DZD
        const rows = form.querySelectorAll('.seller-pack-row');
        const errors = [];

        rows.forEach(function (row, index) {
            const baseDzd = row.dataset.baseDzd ? parseFloat(row.dataset.baseDzd) : 0;

            const priceDzdInput = row.querySelector('input[name*="[price_dzd]"]');
            if (priceDzdInput && priceDzdInput.value !== '') {
                const val = parseFloat(priceDzdInput.value);
                if (isNaN(val) || val < baseDzd) {
                    const packName = row.dataset.packName || row.querySelector('td:first-child p')?.textContent?.trim() || `row ${index+1}`;
                    const err = tPriceMin.replace(':pack', packName).replace(':min', baseDzd);
                    errors.push(err);
                }
            }

            const flexyInput = row.querySelector('input[name*="[flexy_price]"]');
            if (flexyInput && flexyInput.value !== '') {
                const fVal = parseFloat(flexyInput.value);
                if (isNaN(fVal) || fVal < baseDzd) {
                    const packName = row.dataset.packName || row.querySelector('td:first-child p')?.textContent?.trim() || `row ${index+1}`;
                    const ferr = tFlexyMin.replace(':pack', packName).replace(':min', baseDzd);
                    errors.push(ferr);
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
