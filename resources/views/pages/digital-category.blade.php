@extends('layouts.app')

@section('title', $category->name . ' - DiasZone')

@section('content')
<style>
    body { background-color: #ffffff !important; }
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-slate-50">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center gap-4">
            @if($category->imageUrl())
                <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="w-20 h-20 object-cover rounded-xl">
            @endif
            <div>
                <p class="text-sm text-gray-500 mb-1"><a href="{{ route('digital') }}" class="hover:underline">Digital</a> / {{ $category->name }}</p>
                <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-gray-600 mt-2 max-w-2xl">{{ $category->description }}</p>
                @endif
            </div>
        </div>

        @guest
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm">
                Please <a href="{{ route('login') }}" class="font-semibold underline">log in</a> to purchase.
            </div>
        @endguest

        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-3" id="vip-pack-list">
                @forelse($packs as $pack)
                    <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-indigo-400 transition vip-pack-option"
                           data-pack-id="{{ $pack->id }}"
                           data-pack-name="{{ $pack->name }}"
                           data-pack-price="{{ $pack->price_dzd }}">
                        <input type="radio" name="vip_pack" value="{{ $pack->id }}" class="vip-pack-radio text-indigo-600" {{ $loop->first ? 'checked' : '' }}>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900">{{ $pack->name }}</div>
                            @if($pack->description)
                                <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($pack->description), 120) }}</div>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-bold text-indigo-700">{{ number_format((float) $pack->price_dzd, 0) }} DZD</div>
                        </div>
                    </label>
                @empty
                    <div class="p-6 rounded-xl border border-dashed border-gray-300 text-gray-500 text-center">
                        No packs available yet.
                    </div>
                @endforelse
            </div>

            <div class="lg:col-span-2">
                <div class="sticky top-24 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-lg mb-4">Order</h2>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="vip-email">Email</label>
                    <input id="vip-email" type="email" autocomplete="email" placeholder="you@email.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 mb-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ auth()->user()->email ?? '' }}">
                    <p class="text-xs text-gray-500 mb-4">Used for delivery. Login link will appear in My Orders when ready (usually within 10–15 minutes).</p>

                    <div class="flex justify-between text-sm mb-4">
                        <span class="text-gray-600">Selected</span>
                        <span id="vip-selected-label" class="font-medium text-gray-900 text-right max-w-[60%]"></span>
                    </div>
                    <div class="flex justify-between mb-5">
                        <span class="font-semibold">Total</span>
                        <span id="vip-selected-price" class="font-bold text-indigo-700">—</span>
                    </div>

                    <button type="button" id="vip-add-cart-btn"
                            class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 disabled:opacity-50"
                            @guest disabled @endguest>
                        Add to cart
                    </button>
                    <p id="vip-cart-msg" class="text-sm mt-3 hidden"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('vip-email');
    const labelEl = document.getElementById('vip-selected-label');
    const priceEl = document.getElementById('vip-selected-price');
    const msgEl = document.getElementById('vip-cart-msg');
    const btn = document.getElementById('vip-add-cart-btn');

    function selectedPack() {
        const radio = document.querySelector('.vip-pack-radio:checked');
        if (!radio) return null;
        const row = radio.closest('.vip-pack-option');
        return {
            id: parseInt(radio.value, 10),
            name: row?.dataset.packName || '',
            price: row?.dataset.packPrice || '0',
        };
    }

    function refreshSummary() {
        const pack = selectedPack();
        if (!pack) {
            labelEl.textContent = '—';
            priceEl.textContent = '—';
            return;
        }
        labelEl.textContent = pack.name;
        priceEl.textContent = Number(pack.price).toLocaleString() + ' DZD';
    }

    document.querySelectorAll('.vip-pack-radio').forEach(r => r.addEventListener('change', refreshSummary));
    refreshSummary();

    if (!btn) return;
    btn.addEventListener('click', function () {
        const pack = selectedPack();
        const email = (emailInput?.value || '').trim();
        msgEl.classList.add('hidden');

        if (!pack) {
            msgEl.textContent = 'Select a pack.';
            msgEl.className = 'text-sm mt-3 text-red-600';
            msgEl.classList.remove('hidden');
            return;
        }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            msgEl.textContent = 'Enter a valid email.';
            msgEl.className = 'text-sm mt-3 text-red-600';
            msgEl.classList.remove('hidden');
            return;
        }
        if (typeof CartManager === 'undefined') {
            msgEl.textContent = 'Cart unavailable. Refresh the page.';
            msgEl.className = 'text-sm mt-3 text-red-600';
            msgEl.classList.remove('hidden');
            return;
        }

        CartManager.addToCart({
            vipreseller_pack_id: pack.id,
            pack_type: 'vipreseller',
            email: email,
            quantity: 1,
            name: pack.name,
        });

        msgEl.textContent = 'Added to cart.';
        msgEl.className = 'text-sm mt-3 text-green-600';
        msgEl.classList.remove('hidden');

        if (typeof CartManager.updateCartUI === 'function') {
            CartManager.updateCartUI();
        }
    });
});
</script>
@endpush
@endsection
