@extends('layouts.app')

@section('title', __('giveaway.page_title'))

@push('styles')
<style>
.dz-gw {
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --accent: #6d28d9;
    --soft: #f5f3ff;
}
.dz-gw-shell {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(900px 400px at 50% -10%, rgba(109, 40, 217, 0.14), transparent 55%),
        radial-gradient(700px 320px at 50% 100%, rgba(37, 99, 235, 0.07), transparent 50%),
        #f8fafc;
    min-height: calc(100vh - 4rem);
}
.dz-gw-wrap {
    width: 100%;
    max-width: 680px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}
.dz-gw-floaters {
    pointer-events: none;
    position: absolute;
    inset: 0;
    overflow: hidden;
    z-index: 0;
}
.dz-gw-floater {
    position: absolute;
    border-radius: 9999px;
    background: rgba(124, 58, 237, 0.12);
    animation: dz-gw-float 10s ease-in-out infinite;
}
.dz-gw-floater:nth-child(1) { width: 10px; height: 10px; left: 8%; top: 18%; animation-duration: 9s; }
.dz-gw-floater:nth-child(2) { width: 14px; height: 14px; right: 12%; top: 28%; background: rgba(37,99,235,.12); animation-duration: 12s; animation-delay: -2s; }
.dz-gw-floater:nth-child(3) { width: 8px; height: 8px; left: 18%; bottom: 22%; animation-duration: 11s; animation-delay: -4s; }
.dz-gw-floater:nth-child(4) { width: 12px; height: 12px; right: 20%; bottom: 30%; background: rgba(251,191,36,.18); animation-duration: 13s; animation-delay: -1s; }
.dz-gw-floater:nth-child(5) { width: 7px; height: 7px; left: 48%; top: 12%; animation-duration: 8s; animation-delay: -3s; }
@keyframes dz-gw-float {
    0%, 100% { transform: translateY(0) translateX(0); opacity: .55; }
    50% { transform: translateY(-18px) translateX(8px); opacity: 1; }
}
.dz-gw-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.35rem;
    min-height: 200px;
    color: #fff;
    text-align: center;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    animation: dz-gw-rise .55s ease-out both;
}
@media (min-width: 640px) {
    .dz-gw-hero { min-height: 230px; }
}
.dz-gw-hero__bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    animation: dz-gw-ken 18s ease-in-out infinite alternate;
}
@keyframes dz-gw-ken {
    from { transform: scale(1); }
    to { transform: scale(1.06); }
}
.dz-gw-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(165deg, rgba(15, 23, 42, 0.84) 0%, rgba(76, 29, 149, 0.72) 55%, rgba(15, 23, 42, 0.8) 100%);
}
.dz-gw-hero__content {
    position: relative;
    z-index: 1;
    padding: 1.35rem 1.1rem 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}
@media (min-width: 640px) {
    .dz-gw-hero__content { padding: 1.75rem 1.75rem 1.5rem; }
}
.dz-gw-count {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.4rem;
    width: 100%;
    max-width: 20rem;
    margin: 0 auto;
}
.dz-gw-count__cell {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 0.8rem;
    padding: 0.55rem 0.3rem;
    text-align: center;
    backdrop-filter: blur(8px);
    animation: dz-gw-pulse 2.8s ease-in-out infinite;
}
.dz-gw-count__cell:nth-child(2) { animation-delay: .15s; }
.dz-gw-count__cell:nth-child(3) { animation-delay: .3s; }
.dz-gw-count__cell:nth-child(4) { animation-delay: .45s; }
@keyframes dz-gw-pulse {
    0%, 100% { transform: translateY(0); box-shadow: 0 0 0 rgba(255,255,255,0); }
    50% { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(167,139,250,.18); }
}
.dz-gw-count__num {
    display: block;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}
.dz-gw-count__lbl {
    display: block;
    font-size: 0.58rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    opacity: 0.78;
    margin-top: 0.15rem;
}
.dz-gw-panel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 1.2rem;
    padding: 1rem 1rem;
    text-align: center;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    animation: dz-gw-rise .55s ease-out both;
}
.dz-gw-panel:nth-of-type(2) { animation-delay: .05s; }
.dz-gw-panel:nth-of-type(3) { animation-delay: .1s; }
.dz-gw-panel:nth-of-type(4) { animation-delay: .15s; }
@keyframes dz-gw-rise {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (min-width: 640px) {
    .dz-gw-panel { padding: 1.25rem 1.35rem; }
}
/* Compact steps — single card, no tall grid */
.dz-gw-steps {
    display: grid;
    gap: 0.55rem;
    text-align: left;
}
.dz-gw-step-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 0.7rem;
    align-items: center;
    padding: 0.7rem 0.75rem;
    border-radius: 0.95rem;
    border: 1px solid var(--line);
    background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
}
.dz-gw-step-row:hover {
    border-color: #ddd6fe;
    transform: translateX(2px);
    box-shadow: 0 8px 20px rgba(109, 40, 217, 0.06);
}
.dz-gw-step-row__num {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 9999px;
    background: #ede9fe;
    color: #6d28d9;
    font-size: 0.8rem;
    font-weight: 900;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    animation: dz-gw-softbeat 2.4s ease-in-out infinite;
}
.dz-gw-step-row:nth-child(2) .dz-gw-step-row__num { animation-delay: .3s; }
.dz-gw-step-row:nth-child(3) .dz-gw-step-row__num { animation-delay: .6s; }
@keyframes dz-gw-softbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}
.dz-gw-step-row__icon {
    width: 2rem;
    height: 2rem;
    object-fit: contain;
    flex-shrink: 0;
}
.dz-gw-step-row h3 {
    font-size: 0.9rem;
    font-weight: 900;
    color: var(--ink);
    line-height: 1.2;
}
.dz-gw-step-row p {
    margin-top: 0.15rem;
    font-size: 0.75rem;
    color: var(--muted);
    line-height: 1.35;
}
.dz-gw-step-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.45rem;
}
.dz-gw-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
}
/* Enrolled marquee */
.dz-gw-marquee {
    overflow: hidden;
    mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
    -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
}
.dz-gw-marquee__track {
    display: flex;
    width: max-content;
    gap: 0.5rem;
    animation: dz-gw-marquee 28s linear infinite;
}
.dz-gw-marquee:hover .dz-gw-marquee__track {
    animation-play-state: paused;
}
@keyframes dz-gw-marquee {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}
.dz-gw-name {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: var(--soft);
    color: #5b21b6;
    border: 1px solid #ddd6fe;
    border-radius: 9999px;
    padding: 0.35rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    animation: dz-gw-namepulse 3.2s ease-in-out infinite;
}
.dz-gw-name::before {
    content: "";
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 9999px;
    background: #22c55e;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45);
    animation: dz-gw-dot 1.8s ease-out infinite;
}
@keyframes dz-gw-namepulse {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-2px); }
}
@keyframes dz-gw-dot {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45); }
    70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
.dz-gw-name:nth-child(3n) { animation-delay: .4s; }
.dz-gw-name:nth-child(3n+1) { animation-delay: .8s; }
.dz-gw-name:nth-child(3n+2) { animation-delay: 1.2s; }
.dz-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    max-width: 360px;
    margin: 0 auto;
    min-height: 3.1rem;
    padding: 0.8rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #dadce0;
    background: #fff;
    color: #3c4043;
    font-weight: 700;
    box-shadow: 0 1px 2px rgba(60, 64, 67, 0.12);
    transition: transform .15s ease, box-shadow .15s ease;
}
.dz-google-btn:hover {
    background: #f8faff;
    border-color: #c6dafc;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(66, 133, 244, 0.16);
}
.dz-google-btn__icon { display: grid; place-items: center; width: 1.5rem; height: 1.5rem; }
.dz-gw-upload {
    position: relative;
    border: 1.5px dashed #c4b5fd;
    background: linear-gradient(180deg, #faf5ff 0%, #fff 100%);
    border-radius: 1.05rem;
    padding: 0.95rem;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    text-align: center;
}
.dz-gw-upload:hover,
.dz-gw-upload.is-drag {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}
.dz-gw-upload input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.dz-gw-upload__preview {
    display: none;
    margin: 0 auto 0.65rem;
    width: 104px;
    height: 104px;
    border-radius: 0.85rem;
    object-fit: cover;
    border: 2px solid #ddd6fe;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
}
.dz-gw-upload.has-preview .dz-gw-upload__preview { display: block; }
.dz-gw-upload.has-preview .dz-gw-upload__placeholder { display: none; }
.dz-gw-upload__icon {
    width: 2.5rem;
    height: 2.5rem;
    margin: 0 auto 0.45rem;
    border-radius: 0.8rem;
    background: #ede9fe;
    color: #6d28d9;
    display: grid;
    place-items: center;
}
.dz-gw-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 360px;
    margin: 0 auto;
    border-radius: 0.95rem;
    background: #6d28d9;
    color: #fff;
    font-weight: 800;
    padding: 0.85rem 1.25rem;
    box-shadow: 0 10px 24px rgba(109, 40, 217, 0.22);
    transition: background .15s ease, transform .15s ease;
}
.dz-gw-btn-primary:hover { background: #5b21b6; transform: translateY(-1px); }
.dz-gw-fb {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    border-radius: 0.7rem;
    background: #1877F2;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.4rem 0.65rem;
    transition: background .15s ease;
}
.dz-gw-fb:hover { background: #166FE5; }
.dz-gw-fb img { width: 0.9rem; height: 0.9rem; border-radius: 0.15rem; }
.dz-gw-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.7rem;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.4rem 0.65rem;
    background: #fff;
}
.dz-gw-ghost:hover { background: #f8fafc; }
@media (prefers-reduced-motion: reduce) {
    .dz-gw-floater,
    .dz-gw-hero__bg,
    .dz-gw-count__cell,
    .dz-gw-step-row__num,
    .dz-gw-marquee__track,
    .dz-gw-name,
    .dz-gw-name::before,
    .dz-gw-panel,
    .dz-gw-hero { animation: none !important; }
}
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
    $enrolleeNames = $enrollees->map(fn ($row) => $row->displayFirstName())->values();
@endphp

<div class="dz-gw dz-gw-shell py-5 sm:py-8">
    <div class="dz-gw-floaters" aria-hidden="true">
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
        <span class="dz-gw-floater"></span>
    </div>

    <div class="container mx-auto px-4">
        <div class="dz-gw-wrap space-y-3.5 sm:space-y-4">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 text-center">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 text-center">{{ session('error') }}</div>
            @endif

            @unless($giveaway)
                <div class="dz-gw-panel py-14">
                    <img src="{{ $shareIcon }}" alt="" class="mx-auto w-14 h-14 mb-3">
                    <h1 class="text-2xl font-black text-slate-900">{{ __('giveaway.no_active') }}</h1>
                </div>
            @else
                <section class="dz-gw-hero">
                    <img src="{{ $giveaway->heroImageUrl() }}" alt="" class="dz-gw-hero__bg" loading="eager">
                    <div class="dz-gw-hero__overlay"></div>
                    <div class="dz-gw-hero__content">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-violet-200 mb-1.5">{{ __('giveaway.eyebrow') }}</p>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight max-w-xl">{{ $giveaway->title }}</h1>
                        <p class="mt-2 text-sm text-slate-200 max-w-md leading-relaxed">
                            {{ $giveaway->description ?: __('giveaway.subtitle') }}
                        </p>
                        <div class="mt-4 w-full flex flex-col items-center">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-violet-100/85 mb-1.5">{{ __('giveaway.countdown_label') }}</p>
                            <div class="dz-gw-count" id="gw-countdown" data-ends="{{ $endsAtMs }}" data-starts="{{ $startsAtMs }}">
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="d">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.days') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="h">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.hours') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="m">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.mins') }}</span></div>
                                <div class="dz-gw-count__cell"><span class="dz-gw-count__num" data-unit="s">0</span><span class="dz-gw-count__lbl">{{ __('giveaway.secs') }}</span></div>
                            </div>
                            <p id="gw-countdown-note" class="mt-1.5 text-sm font-semibold text-violet-100/90 hidden"></p>
                        </div>
                    </div>
                </section>

                <section class="dz-gw-panel !text-left">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h2 class="text-base font-black text-slate-900">{{ __('giveaway.how_title') }}</h2>
                        <span class="dz-gw-chip bg-violet-50 text-violet-700">3 {{ __('giveaway.steps_short') }}</span>
                    </div>
                    <div class="dz-gw-steps">
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">1</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $fbIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step1_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step1_desc') }}</p>
                                <div class="dz-gw-step-actions">
                                    <a href="{{ $giveaway->facebook_post_url }}" target="_blank" rel="noopener noreferrer" class="dz-gw-fb">
                                        <img src="{{ $fbIcon }}" alt=""> {{ __('giveaway.open_post') }}
                                    </a>
                                    <a href="{{ $giveaway->facebook_page_url }}" target="_blank" rel="noopener noreferrer" class="dz-gw-ghost">
                                        {{ __('giveaway.open_page') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">2</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $usersIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step2_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step2_desc') }}</p>
                            </div>
                        </article>
                        <article class="dz-gw-step-row">
                            <span class="dz-gw-step-row__num">3</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $shareIcon }}" alt="" class="dz-gw-step-row__icon">
                                    <h3>{{ __('giveaway.step3_title') }}</h3>
                                </div>
                                <p>{{ __('giveaway.step3_desc') }}</p>
                            </div>
                        </article>
                    </div>
                    @if($giveaway->instructions)
                        <p class="mt-3 text-xs text-slate-500 whitespace-pre-line leading-relaxed">{{ $giveaway->instructions }}</p>
                    @endif
                </section>

                <section class="dz-gw-panel !py-3">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <h2 class="text-sm font-black text-slate-900">{{ __('giveaway.enrolled_label') }}</h2>
                        <span class="dz-gw-chip bg-violet-100 text-violet-800">{{ number_format($enrolleeCount) }}</span>
                    </div>
                    @if($enrolleeNames->isEmpty())
                        <p class="text-sm text-slate-500 py-1">{{ __('giveaway.enrolled_empty') }}</p>
                    @else
                        <div class="dz-gw-marquee" aria-label="{{ __('giveaway.enrolled_label') }}">
                            <div class="dz-gw-marquee__track">
                                @foreach([1, 2] as $loopPass)
                                    @foreach($enrolleeNames as $name)
                                        <span class="dz-gw-name">{{ $name }}</span>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                @guest
                    <section class="dz-gw-panel">
                        <h2 class="text-lg font-black text-slate-900">{{ __('giveaway.login_title') }}</h2>
                        <p class="mt-1.5 text-sm text-slate-500 max-w-sm mx-auto">{{ __('giveaway.login_subtitle') }}</p>
                        <div class="mt-4">
                            @include('components.google-sign-in-button', [
                                'href' => route('auth.google', ['redirect' => '/giveaway/'.$giveaway->slug]),
                            ])
                        </div>
                    </section>
                @else
                    @if($entry)
                        <section class="dz-gw-panel space-y-3.5">
                            <div class="flex flex-col items-center gap-2">
                                <h2 class="text-base font-black text-slate-900">{{ __('giveaway.status_title') }}</h2>
                                <span class="dz-gw-chip {{ $statusClass }}">{{ __('giveaway.'.$statusKey) }}</span>
                            </div>
                            @if($entry->status === 'declined' && $entry->decline_reason)
                                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-3 text-sm text-rose-900 text-left">
                                    <p class="font-bold mb-0.5">{{ __('giveaway.decline_reason') }}</p>
                                    <p>{{ $entry->decline_reason }}</p>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500 mb-1.5">{{ __('giveaway.referral_title') }}</h3>
                                <p class="text-sm text-slate-500 mb-2.5 max-w-md mx-auto">{{ __('giveaway.referral_hint') }}</p>
                                <div class="flex flex-col gap-2 max-w-md mx-auto">
                                    <input id="gw-ref-link" type="text" readonly value="{{ $entry->referralUrl() }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 text-center">
                                    <button type="button" id="gw-copy-btn" class="dz-gw-btn-primary !shadow-none !max-w-none">
                                        {{ __('giveaway.copy_link') }}
                                    </button>
                                </div>
                                <p class="mt-2.5 text-sm font-bold {{ $entry->referralSatisfied() ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ __('giveaway.referral_progress', ['count' => $entry->referralSatisfied() ? 1 : 0]) }}
                                </p>
                            </div>
                        </section>
                    @elseif($giveaway->isCurrentlyActive())
                        <section class="dz-gw-panel">
                            <h2 class="text-base font-black text-slate-900 mb-3">{{ __('giveaway.form_title') }}</h2>
                            <form method="POST" action="{{ route('giveaway.enter', $giveaway->slug) }}" enctype="multipart/form-data" class="space-y-3.5 text-left max-w-md mx-auto">
                                @csrf
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5 text-center">{{ __('giveaway.screenshot_label') }}</label>
                                    <label class="dz-gw-upload block" id="gw-upload">
                                        <input type="file" name="screenshot" id="gw-screenshot" accept="image/jpeg,image/png,image/webp,image/*" required>
                                        <img src="" alt="" class="dz-gw-upload__preview" id="gw-preview">
                                        <div class="dz-gw-upload__placeholder">
                                            <div class="dz-gw-upload__icon" aria-hidden="true">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-sm font-bold text-violet-800">{{ __('giveaway.upload_cta') }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ __('giveaway.screenshot_hint') }}</p>
                                        </div>
                                        <p class="mt-2 text-xs font-semibold text-violet-700 hidden" id="gw-preview-name"></p>
                                    </label>
                                    @error('screenshot')
                                        <p class="mt-1.5 text-xs font-semibold text-rose-600 text-center">{{ $message }}</p>
                                    @enderror
                                </div>
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="liked_followed_ack" value="1" required class="mt-1 rounded border-slate-300 text-violet-700 focus:ring-violet-600">
                                    <span>{{ __('giveaway.ack_label') }}</span>
                                </label>
                                @error('liked_followed_ack')
                                    <p class="text-xs font-semibold text-rose-600 text-center">{{ $message }}</p>
                                @enderror
                                <div class="pt-0.5 text-center">
                                    <button type="submit" class="dz-gw-btn-primary">{{ __('giveaway.submit') }}</button>
                                </div>
                            </form>
                        </section>
                    @else
                        <section class="dz-gw-panel">
                            <p class="font-bold text-slate-700">{{ __('giveaway.closed') }}</p>
                        </section>
                    @endif
                @endguest
            @endunless
        </div>
    </div>
</div>
@endsection

@if($giveaway)
@push('scripts')
<script>
(function () {
    const root = document.getElementById('gw-countdown');
    if (root) {
        const ends = parseInt(root.dataset.ends || '0', 10);
        const starts = parseInt(root.dataset.starts || '0', 10);
        const note = document.getElementById('gw-countdown-note');
        const endedLabel = @json(__('giveaway.ended'));
        const soonLabel = @json(__('giveaway.not_started'));

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
                if (el) el.textContent = String(map[u]);
            });
        }
        tick();
        setInterval(tick, 1000);
    }

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

    const upload = document.getElementById('gw-upload');
    const input = document.getElementById('gw-screenshot');
    const preview = document.getElementById('gw-preview');
    const nameEl = document.getElementById('gw-preview-name');
    if (upload && input && preview) {
        const showPreview = (file) => {
            if (!file || !file.type.startsWith('image/')) return;
            preview.src = URL.createObjectURL(file);
            upload.classList.add('has-preview');
            if (nameEl) {
                nameEl.textContent = file.name;
                nameEl.classList.remove('hidden');
            }
        };
        input.addEventListener('change', () => {
            if (input.files && input.files[0]) showPreview(input.files[0]);
        });
        ['dragenter', 'dragover'].forEach((evt) => {
            upload.addEventListener(evt, (e) => { e.preventDefault(); upload.classList.add('is-drag'); });
        });
        ['dragleave', 'drop'].forEach((evt) => {
            upload.addEventListener(evt, (e) => { e.preventDefault(); upload.classList.remove('is-drag'); });
        });
        upload.addEventListener('drop', (e) => {
            const file = e.dataTransfer?.files?.[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        });
    }
})();
</script>
@endpush
@endif
