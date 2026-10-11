@extends('layouts.app')

@section('title', __('giveaway.page_title'))

@push('styles')
<style>
.dz-gw {
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --accent: #6d28d9;
}
.dz-gw-shell {
    background:
        radial-gradient(1000px 420px at 8% -8%, rgba(109, 40, 217, 0.12), transparent 55%),
        radial-gradient(800px 360px at 100% 0%, rgba(37, 99, 235, 0.08), transparent 50%),
        #f8fafc;
    min-height: calc(100vh - 4rem);
}
.dz-gw-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.5rem;
    min-height: 280px;
    color: #fff;
    box-shadow: 0 22px 50px rgba(15, 23, 42, 0.18);
}
@media (min-width: 768px) {
    .dz-gw-hero { min-height: 360px; }
}
.dz-gw-hero__bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.dz-gw-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(15, 23, 42, 0.88) 0%, rgba(49, 20, 92, 0.72) 55%, rgba(15, 23, 42, 0.55) 100%);
}
.dz-gw-hero__content {
    position: relative;
    z-index: 1;
    padding: 1.75rem 1.35rem 1.5rem;
}
@media (min-width: 768px) {
    .dz-gw-hero__content { padding: 2.5rem; }
}
.dz-gw-count {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.5rem;
    max-width: 28rem;
}
.dz-gw-count__cell {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.16);
    border-radius: 0.85rem;
    padding: 0.65rem 0.35rem;
    text-align: center;
    backdrop-filter: blur(6px);
}
.dz-gw-count__num {
    display: block;
    font-size: 1.35rem;
    font-weight: 900;
    letter-spacing: -0.02em;
}
.dz-gw-count__lbl {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 0.75;
    margin-top: 0.15rem;
}
.dz-gw-step {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.15rem;
    padding: 1.15rem;
    height: 100%;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.dz-gw-step img {
    width: 3.25rem;
    height: 3.25rem;
}
.dz-gw-panel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.25rem;
    padding: 1.25rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
@media (min-width: 640px) {
    .dz-gw-panel { padding: 1.75rem; }
}
.dz-gw-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
}
.dz-gw-names {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}
.dz-gw-name {
    background: #f5f3ff;
    color: #5b21b6;
    border: 1px solid #ddd6fe;
    border-radius: 9999px;
    padding: 0.3rem 0.7rem;
    font-size: 0.8rem;
    font-weight: 700;
}
.dz-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    min-height: 3.25rem;
    padding: 0.85rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #dadce0;
    background: #fff;
    color: #3c4043;
    font-weight: 700;
    box-shadow: 0 1px 2px rgba(60, 64, 67, 0.12);
}
.dz-google-btn:hover { background: #f8faff; border-color: #c6dafc; }
.dz-google-btn__icon { display: grid; place-items: center; width: 1.5rem; height: 1.5rem; }
</style>
@endpush

@section('content')
@php
    $fbIcon = \App\Models\Giveaway::assetUrl('facebook.svg');
    $usersIcon = \App\Models\Giveaway::assetUrl('users.svg');
    $shareIcon = \App\Models\Giveaway::assetUrl('share.svg');
    $endsAtMs = $giveaway?->ends_at ? ((int) $giveaway->ends_at->getTimestamp() * 1000) : null;
    $startsAtMs = $giveaway?->starts_at ? ((int) $giveaway->starts_at->getTimestamp() * 1000) : null;
    $statusKey = match ($entry?->status) {
        'approved' => 'status_approved',
        'declined' => 'status_declined',
        'pending' => 'status_pending',
        default => null,
    };
    $statusClass = match ($entry?->status) {
        'approved' => 'bg-emerald-100 text-emerald-800',
        'declined' => 'bg-rose-100 text-rose-800',
        'pending' => 'bg-amber-100 text-amber-800',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<div class="dz-gw dz-gw-shell py-6 sm:py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ session('error') }}</div>
        @endif

        @unless($giveaway)
            <div class="dz-gw-panel text-center py-16">
                <img src="{{ $shareIcon }}" alt="" class="mx-auto w-16 h-16 mb-4">
                <h1 class="text-2xl font-black text-slate-900">{{ __('giveaway.no_active') }}</h1>
            </div>
        @else
            <section class="dz-gw-hero mb-6 sm:mb-8">
                <img src="{{ $giveaway->heroImageUrl() }}" alt="" class="dz-gw-hero__bg" loading="eager">
                <div class="dz-gw-hero__overlay"></div>
                <div class="dz-gw-hero__content">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-violet-200 mb-2">{{ __('giveaway.eyebrow') }}</p>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight max-w-2xl">{{ $giveaway->title }}</h1>
                    <p class="mt-3 text-sm sm:text-base text-slate-200 max-w-xl leading-relaxed">
                        {{ $giveaway->description ?: __('giveaway.subtitle') }}
                    </p>

                    <div class="mt-6">
                        <p class="text-xs font-bold uppercase tracking-wide text-violet-100/80 mb-2">{{ __('giveaway.countdown_label') }}</p>
                        <div class="dz-gw-count" id="gw-countdown"
                             data-ends="{{ $endsAtMs }}"
                             data-starts="{{ $startsAtMs }}">
                            <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="d">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.days') }}</span></div>
                            <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="h">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.hours') }}</span></div>
                            <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="m">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.mins') }}</span></div>
                            <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="s">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.secs') }}</span></div>
                        </div>
                        <p id="gw-countdown-note" class="mt-2 text-sm font-semibold text-violet-100/90 hidden"></p>
                    </div>
                </div>
            </section>

            <div class="grid gap-5 lg:grid-cols-[1.35fr_0.95fr] items-start">
                <div class="space-y-5">
                    <section>
                        <h2 class="text-lg font-black text-slate-900 mb-3">{{ __('giveaway.how_title') }}</h2>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <article class="dz-gw-step">
                                <img src="{{ $fbIcon }}" alt="Facebook" class="mb-3">
                                <h3 class="font-black text-slate-900">1. {{ __('giveaway.step1_title') }}</h3>
                                <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ __('giveaway.step1_desc') }}</p>
                                <div class="mt-3 flex flex-col gap-2">
                                    <a href="{{ $giveaway->facebook_post_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1877F2] hover:bg-[#166FE5] text-white text-xs font-bold px-3 py-2">
                                        <img src="{{ $fbIcon }}" alt="" class="w-4 h-4 rounded"> {{ __('giveaway.open_post') }}
                                    </a>
                                    <a href="{{ $giveaway->facebook_page_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-800 text-xs font-bold px-3 py-2">
                                        {{ __('giveaway.open_page') }}
                                    </a>
                                </div>
                            </article>
                            <article class="dz-gw-step">
                                <img src="{{ $usersIcon }}" alt="" class="mb-3">
                                <h3 class="font-black text-slate-900">2. {{ __('giveaway.step2_title') }}</h3>
                                <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ __('giveaway.step2_desc') }}</p>
                            </article>
                            <article class="dz-gw-step">
                                <img src="{{ $shareIcon }}" alt="" class="mb-3">
                                <h3 class="font-black text-slate-900">3. {{ __('giveaway.step3_title') }}</h3>
                                <p class="mt-1.5 text-sm text-slate-500 leading-relaxed">{{ __('giveaway.step3_desc') }}</p>
                            </article>
                        </div>
                    </section>

                    @if($giveaway->instructions)
                        <section class="dz-gw-panel">
                            <div class="prose prose-sm max-w-none text-slate-600 whitespace-pre-line">{{ $giveaway->instructions }}</div>
                        </section>
                    @endif

                    <section class="dz-gw-panel">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h2 class="text-lg font-black text-slate-900">{{ __('giveaway.enrolled_label') }}</h2>
                            <span class="dz-gw-chip bg-violet-100 text-violet-800">{{ number_format($enrolleeCount) }}</span>
                        </div>
                        @if($enrollees->isEmpty())
                            <p class="text-sm text-slate-500">{{ __('giveaway.enrolled_empty') }}</p>
                        @else
                            <div class="dz-gw-names">
                                @foreach($enrollees as $row)
                                    <span class="dz-gw-name">{{ $row->displayFirstName() }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-24">
                    @guest
                        <section class="dz-gw-panel text-center">
                            <h2 class="text-xl font-black text-slate-900">{{ __('giveaway.login_title') }}</h2>
                            <p class="mt-2 text-sm text-slate-500">{{ __('giveaway.login_subtitle') }}</p>
                            <div class="mt-5">
                                @include('components.google-sign-in-button', [
                                    'href' => route('auth.google', ['redirect' => '/giveaway/'.$giveaway->slug]),
                                ])
                            </div>
                        </section>
                    @else
                        @if($entry)
                            <section class="dz-gw-panel space-y-4">
                                <div class="flex items-center justify-between gap-2">
                                    <h2 class="text-lg font-black text-slate-900">{{ __('giveaway.status_title') }}</h2>
                                    <span class="dz-gw-chip {{ $statusClass }}">{{ __('giveaway.'.$statusKey) }}</span>
                                </div>
                                @if($entry->status === 'declined' && $entry->decline_reason)
                                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3 text-sm text-rose-900">
                                        <p class="font-bold mb-0.5">{{ __('giveaway.decline_reason') }}</p>
                                        <p>{{ $entry->decline_reason }}</p>
                                    </div>
                                @endif

                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-500 mb-2">{{ __('giveaway.referral_title') }}</h3>
                                    <p class="text-sm text-slate-500 mb-3">{{ __('giveaway.referral_hint') }}</p>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input id="gw-ref-link" type="text" readonly value="{{ $entry->referralUrl() }}"
                                               class="flex-1 min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs sm:text-sm font-semibold text-slate-800">
                                        <button type="button" id="gw-copy-btn"
                                                class="inline-flex items-center justify-center rounded-xl bg-violet-700 hover:bg-violet-800 text-white text-sm font-bold px-4 py-2.5">
                                            {{ __('giveaway.copy_link') }}
                                        </button>
                                    </div>
                                    <p class="mt-3 text-sm font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                    </p>
                                </div>
                            </section>
                        @elseif($giveaway->isCurrentlyActive())
                            <section class="dz-gw-panel">
                                <h2 class="text-lg font-black text-slate-900 mb-4">{{ __('giveaway.form_title') }}</h2>
                                <form method="POST" action="{{ route('giveaway.enter', $giveaway->slug) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-1.5">{{ __('giveaway.screenshot_label') }}</label>
                                        <input type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" required
                                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-violet-700 hover:file:bg-violet-100">
                                        <p class="mt-1 text-xs text-slate-500">{{ __('giveaway.screenshot_hint') }}</p>
                                        @error('screenshot')
                                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <label class="flex items-start gap-3 text-sm text-slate-700">
                                        <input type="checkbox" name="liked_followed_ack" value="1" required class="mt-1 rounded border-slate-300 text-violet-700 focus:ring-violet-600">
                                        <span>{{ __('giveaway.ack_label') }}</span>
                                    </label>
                                    @error('liked_followed_ack')
                                        <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" class="w-full rounded-xl bg-violet-700 hover:bg-violet-800 text-white font-bold py-3 shadow-sm">
                                        {{ __('giveaway.submit') }}
                                    </button>
                                </form>
                            </section>
                        @else
                            <section class="dz-gw-panel text-center">
                                <p class="font-bold text-slate-700">{{ __('giveaway.closed') }}</p>
                            </section>
                        @endif
                    @endguest
                </aside>
            </div>
        @endunless
    </div>
</div>
@endsection

@if($giveaway)
@push('scripts')
<script>
(function () {
    const root = document.getElementById('gw-countdown');
    if (!root) return;
    const ends = parseInt(root.dataset.ends || '0', 10);
    const starts = parseInt(root.dataset.starts || '0', 10);
    const note = document.getElementById('gw-countdown-note');
    const endedLabel = @json(__('giveaway.ended'));
    const soonLabel = @json(__('giveaway.not_started'));

    function pad(n) { return String(n); }

    function tick() {
        const now = Date.now();
        let target = ends;
        let label = null;
        if (starts && now < starts) {
            target = starts;
            label = soonLabel;
        } else if (ends && now >= ends) {
            ['d','h','m','s'].forEach((u) => {
                const el = root.querySelector('[data-unit="'+u+'"]');
                if (el) el.textContent = '0';
            });
            if (note) { note.textContent = endedLabel; note.classList.remove('hidden'); }
            return;
        }
        if (note) {
            if (label) { note.textContent = label; note.classList.remove('hidden'); }
            else note.classList.add('hidden');
        }
        let diff = Math.max(0, target - now);
        const d = Math.floor(diff / 86400000); diff -= d * 86400000;
        const h = Math.floor(diff / 3600000); diff -= h * 3600000;
        const m = Math.floor(diff / 60000); diff -= m * 60000;
        const s = Math.floor(diff / 1000);
        const map = { d, h, m, s };
        Object.keys(map).forEach((u) => {
            const el = root.querySelector('[data-unit="'+u+'"]');
            if (el) el.textContent = pad(map[u]);
        });
    }
    tick();
    setInterval(tick, 1000);

    const copyBtn = document.getElementById('gw-copy-btn');
    const linkInput = document.getElementById('gw-ref-link');
    if (copyBtn && linkInput) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(linkInput.value);
                copyBtn.textContent = @json(__('giveaway.copied'));
                setTimeout(() => { copyBtn.textContent = @json(__('giveaway.copy_link')); }, 1600);
            } catch (e) {
                linkInput.select();
                document.execCommand('copy');
            }
        });
    }
})();
</script>
@endpush
@endif
