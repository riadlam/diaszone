@extends('layouts.app')

@section('title', __('event.meta_title', ['game' => $gameTitle]) . ' - DiasZone')

@section('content')
@php
    $displayImage = !empty($gameImage) ? asset($gameImage) : asset('storage_public/images_homepage/mobilelegends.webp');
    $requiresLogin = $requiresLogin ?? false;
    $available = $available ?? false;
    $snapshot = $snapshot ?? null;
    $event = $event ?? null;
    $nextEvent = $nextEvent ?? null;
    $prizes = $prizes ?? [];
    $rewardSegments = collect($prizes)->filter(fn ($prize) => ! empty($prize['reward_id']))->keyBy('reward_id');
    $spinsLeft = $snapshot['available_spins'] ?? ($spinsLeft ?? 0);
    $unlimitedSpins = $snapshot['unlimited_spins'] ?? ($unlimitedSpins ?? false);
    $claims = $snapshot['claims'] ?? collect();
    $backdrop = $available
        ? ($event?->backgroundUrl() ?? $nextEvent?->backgroundUrl() ?? \App\Support\PublicMedia::url('event-backgrounds/mlbb-jujutsu-kaisen-skins.png'))
        : null;
@endphp

<div class="event-page min-h-[70vh] {{ $backdrop ? 'has-backdrop' : 'bg-white' }}">
    @if($backdrop)
        <div class="event-backdrop" aria-hidden="true">
            <div class="event-backdrop__image" style="background-image: url('{{ $backdrop }}');"></div>
            <div class="event-backdrop__veil"></div>
        </div>
    @endif

    <div class="event-page__content">
    <div class="border-b border-gray-200 bg-white/85 backdrop-blur-md" style="margin-top: 15px;">
        <div class="container mx-auto px-4 py-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-lg p-1 border border-gray-200 overflow-hidden">
                    <img src="{{ $displayImage }}" alt="{{ $gameTitle }}" class="w-full h-full object-contain rounded">
                </div>
                <h1 class="min-w-0 flex-1 truncate text-lg lg:text-xl font-bold text-gray-900">
                    {{ __('event.title', ['game' => $gameTitle]) }}
                </h1>
                @if($event)
                    <span class="event-countdown inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100 whitespace-nowrap"
                          data-event-countdown
                          data-target-at="{{ $event->ends_at->toIso8601String() }}"
                          data-ends-label="{{ __('event.ends_in', ['time' => '%s']) }}"
                          data-ended-label="{{ __('event.event_ended') }}"
                          title="{{ __('event.event_window', [
                              'start' => $event->starts_at->format('Y-m-d'),
                              'end' => $event->ends_at->format('Y-m-d'),
                          ]) }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                        <span data-countdown-value>{{ $event->ends_at->format('d M') }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 pt-8 lg:pt-10 pb-6">
        @if(! $available)
            <div class="max-w-xl mx-auto text-center bg-gray-50 border border-gray-200 rounded-2xl p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('event.not_available') }}</h2>
                <p class="text-gray-600 text-sm mb-6">{{ __('event.not_available_hint') }}</p>
                <a href="{{ route('event.show', 'mobilelegends') }}" class="inline-flex bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg">
                    Mobile Legends
                </a>
            </div>
        @elseif(! $event)
            @if($nextEvent)
                <div class="next-event-card max-w-2xl mx-auto text-center bg-gray-50 border border-gray-200 rounded-2xl p-6 sm:p-9 shadow-xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-900">
                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                        {{ __('event.next_event') }}
                    </span>
                    <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900">{{ $nextEvent->name }}</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm sm:text-base leading-relaxed text-gray-600">
                        {{ filled($nextEvent->description) ? $nextEvent->description : __('event.next_event_fallback') }}
                    </p>

                    <div class="mt-7">
                        <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-purple-700">{{ __('event.starts_in') }}</p>
                        <div class="next-event-countdown grid grid-cols-4 gap-2 sm:gap-3"
                             data-next-event-countdown
                             data-target-at="{{ $nextEvent->starts_at->toIso8601String() }}">
                            @foreach([
                                'days' => __('event.days_short'),
                                'hours' => __('event.hours_short'),
                                'minutes' => __('event.minutes_short'),
                                'seconds' => __('event.seconds_short'),
                            ] as $unit => $label)
                                <div class="rounded-xl border border-purple-100 bg-purple-50 px-2 py-3 sm:px-4">
                                    <strong class="block text-xl sm:text-3xl tabular-nums text-purple-700" data-countdown-unit="{{ $unit }}">00</strong>
                                    <span class="mt-1 block text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <p class="mt-5 text-xs text-gray-500">
                        {{ $nextEvent->starts_at->timezone('Africa/Algiers')->format('d M Y · H:i') }}
                    </p>
                    <a href="{{ $gameUrl }}" class="inline-flex mt-6 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg">
                        {{ __('event.go_to_game', ['game' => $gameTitle]) }}
                    </a>
                </div>
            @else
                <div class="max-w-xl mx-auto text-center bg-gray-50 border border-gray-200 rounded-2xl p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('event.no_active_event') }}</h2>
                    <p class="text-gray-600 text-sm">{{ __('event.next_event_fallback') }}</p>
                    <a href="{{ $gameUrl }}" class="inline-flex mt-5 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg">
                        {{ __('event.go_to_game', ['game' => $gameTitle]) }}
                    </a>
                </div>
            @endif
        @else
            <div class="max-w-3xl mx-auto mb-6 flex flex-wrap gap-3 items-center justify-between">
                <div class="flex flex-wrap gap-3 text-sm">
                    <div class="px-3 py-2 bg-white/90 backdrop-blur-md border border-gray-200 rounded-lg">
                        <span class="text-gray-500">{{ __('event.spins_left') }}:</span>
                        <strong id="progressSpins" class="text-purple-700 ms-1">{{ $unlimitedSpins ? '∞' : $spinsLeft }}</strong>
                    </div>
                </div>
                <button type="button" id="openMyRewards"
                        class="px-4 py-2 bg-white border border-gray-300 hover:border-purple-400 text-gray-800 font-semibold rounded-lg text-sm">
                    {{ __('event.my_rewards') }}
                    @if($claims->count())
                        <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-purple-600 text-white text-xs">{{ $claims->count() }}</span>
                    @endif
                </button>
            </div>

            <div class="max-w-3xl mx-auto bg-white/90 backdrop-blur-md border border-gray-200 rounded-2xl px-4 py-8 lg:px-8 lg:py-10 shadow-lg">
                @include('components.spin-wheel', [
                    'prizes' => $prizes,
                    'spinsLeft' => $spinsLeft,
                    'unlimitedSpins' => $unlimitedSpins,
                    'spinsPerDay' => max($spinsLeft, 1),
                    'spinUrl' => route('event.spin', ['gameSlug' => $gameSlug]),
                    'rewardsUrl' => route('event.rewards', ['gameSlug' => $gameSlug]),
                    'gameUrl' => $gameUrl,
                    'loginUrl' => route('login', ['redirect' => request()->fullUrl()]),
                    'requiresLogin' => $requiresLogin,
                    'milestoneMode' => true,
                    'initialClaims' => $claims->map(fn ($c) => app(\App\Services\WheelProgressService::class)->serializeClaim($c))->values(),
                ])
            </div>

            <div class="mt-10 max-w-3xl mx-auto">
                <h2 class="text-xl lg:text-2xl font-bold text-gray-900 mb-1">{{ __('event.prizes_title') }}</h2>
                <p class="text-gray-600 text-sm mb-5">{{ __('event.prizes_subtitle') }}</p>
                <div class="bg-white/90 backdrop-blur-md border border-gray-200 rounded-2xl overflow-hidden">
                    @foreach($event->activeRewards as $i => $reward)
                        @php($segment = $rewardSegments[$reward->id] ?? null)
                        <div class="flex items-center gap-4 px-4 py-3">
                            <span class="w-7 h-7 shrink-0 rounded-lg bg-purple-50 border border-purple-100 text-purple-700 font-bold text-xs flex items-center justify-center">{{ $i + 1 }}</span>
                            @if(!empty($segment['icon']))
                                <div class="w-11 h-11 shrink-0 overflow-hidden rounded-xl border border-purple-100 bg-purple-50 p-1">
                                    <img src="{{ $segment['icon'] }}"
                                         alt="{{ $reward->label }}"
                                         class="w-full h-full rounded-lg {{ ($segment['icon_fit'] ?? 'contain') === 'cover' ? 'object-cover' : 'object-contain' }}">
                                </div>
                            @endif
                            <p class="min-w-0 flex-1 font-semibold text-gray-900 truncate">{{ $reward->label }}</p>
                            @if($reward->isDiscountReward())
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                    -{{ rtrim(rtrim(number_format((float)$reward->discount_percentage, 2, '.', ''), '0'), '.') }}%
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($available)
            <div class="mt-10">
                <h2 class="text-xl lg:text-2xl font-bold text-gray-900 mb-5">{{ __('event.how_title') }}</h2>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach([1, 2, 3] as $step)
                        <div class="p-5 bg-white/90 backdrop-blur-md border border-gray-200 rounded-lg">
                            <span class="inline-flex items-center justify-center w-8 h-8 mb-3 rounded-lg bg-purple-50 text-purple-700 font-bold text-sm border border-purple-100">{{ $step }}</span>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ __('event.how_'.$step.'_title') }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ __('event.how_'.$step.'_text') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <p class="text-sm text-gray-800 leading-relaxed">
                        <strong class="font-semibold text-amber-900">{{ __('game.important_note') }}</strong>
                        {{ __('event.rules') }}
                    </p>
                </div>
                <div class="mt-6 flex justify-center">
                    <a href="{{ $gameUrl }}"
                       class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-md">
                        {{ __('event.go_to_game', ['game' => $gameTitle]) }}
                    </a>
                </div>
            </div>
        @endif
    </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-event-countdown]').forEach(function (chip) {
        var targetAt = new Date(chip.dataset.targetAt).getTime();
        var value = chip.querySelector('[data-countdown-value]');
        if (!value || isNaN(targetAt)) return;

        function renderChip() {
            var diff = targetAt - Date.now();
            if (diff <= 0) {
                value.textContent = chip.dataset.endedLabel;
                return false;
            }

            var minutes = Math.floor(diff / 60000);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);
            var remaining = days > 0
                ? days + 'd ' + (hours % 24) + 'h'
                : (hours > 0 ? hours + 'h ' + (minutes % 60) + 'm' : (minutes % 60) + 'm');

            value.textContent = chip.dataset.endsLabel.replace('%s', remaining);
            return true;
        }

        if (renderChip()) {
            var chipTimer = setInterval(function () {
                if (!renderChip()) clearInterval(chipTimer);
            }, 30000);
        }
    });

    document.querySelectorAll('[data-next-event-countdown]').forEach(function (countdown) {
        var targetAt = new Date(countdown.dataset.targetAt).getTime();
        if (isNaN(targetAt)) return;

        function renderNextEvent() {
            var total = Math.max(0, targetAt - Date.now());
            var totalSeconds = Math.floor(total / 1000);
            var values = {
                days: Math.floor(totalSeconds / 86400),
                hours: Math.floor((totalSeconds % 86400) / 3600),
                minutes: Math.floor((totalSeconds % 3600) / 60),
                seconds: totalSeconds % 60
            };

            Object.keys(values).forEach(function (unit) {
                var element = countdown.querySelector('[data-countdown-unit="' + unit + '"]');
                if (element) element.textContent = String(values[unit]).padStart(2, '0');
            });

            return total > 0;
        }

        if (renderNextEvent()) {
            var eventTimer = setInterval(function () {
                if (!renderNextEvent()) {
                    clearInterval(eventTimer);
                    window.location.reload();
                }
            }, 1000);
        }
    });
})();
</script>
@endpush

@if($backdrop)
@push('styles')
<style>
.event-page {
    position: relative;
    background: #06070d;
}

.event-page__content {
    position: relative;
    z-index: 1;
}

.event-backdrop {
    position: fixed;
    inset: 0;
    z-index: 0;
    overflow: hidden;
    pointer-events: none;
}

.event-backdrop__image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    opacity: 1;
}

.event-backdrop__veil {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(6, 7, 13, 0.45) 0%, rgba(6, 7, 13, 0.72) 55%, rgba(6, 7, 13, 0.9) 100%);
}

/* ---------- Dark theme for the wheel route ---------- */

.event-page .text-gray-900 { color: #f9fafb; }
.event-page .text-gray-800 { color: #e5e7eb; }
.event-page .text-gray-600,
.event-page .text-gray-500 { color: #9ca3af; }

.event-page .bg-white\/85,
.event-page .bg-white\/90,
.event-page .bg-white { background-color: rgba(15, 17, 24, 0.78); }
.event-page .bg-gray-50 { background-color: rgba(255, 255, 255, 0.06); }
.event-page .border-gray-200 { border-color: rgba(255, 255, 255, 0.12); }
.event-page .border-gray-300 { border-color: rgba(255, 255, 255, 0.2); }

.event-page .bg-purple-50 { background-color: rgba(139, 92, 246, 0.18); }
.event-page .border-purple-100 { border-color: rgba(139, 92, 246, 0.35); }
.event-page .text-purple-700 { color: #c4b5fd; }

.event-page .bg-amber-50 { background-color: rgba(245, 158, 11, 0.14); }
.event-page .border-amber-200 { border-color: rgba(245, 158, 11, 0.35); }
.event-page .text-amber-900 { color: #fcd34d; }

.event-page .wheel-frame {
    background: #0f1118;
    border-color: rgba(255, 255, 255, 0.12);
    box-shadow: 0 22px 55px -22px rgba(0, 0, 0, 0.95);
}

.event-page .wheel-hub {
    box-shadow: 0 0 0 6px #0f1118, 0 10px 22px -6px rgba(109, 40, 217, 0.75);
}

.event-page .wheel-hub:disabled {
    background: linear-gradient(180deg, #4b5563, #374151);
    box-shadow: 0 0 0 6px #0f1118, 0 6px 14px -8px rgba(0, 0, 0, 0.7);
}

.event-page .wheel-spins,
.event-page .wheel-sound {
    color: #d1d5db;
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
}

.event-page .wheel-spins__value { color: #c4b5fd; }

.event-page .wheel-sound:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ddd6fe;
}

.event-page .wheel-result__backdrop,
.event-page .wheel-rewards__backdrop,
.event-page .wheel-icon-preview__backdrop,
.event-page .wheel-login__backdrop { background: rgba(0, 0, 0, 0.72); }

.event-page .wheel-result__card,
.event-page .wheel-rewards__card,
.event-page .wheel-icon-preview__card,
.event-page .wheel-login__card {
    background: #0f1118;
    border-color: rgba(255, 255, 255, 0.12);
    box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.9);
}

.event-page .wheel-result__title,
.event-page .wheel-result__note-title,
.event-page .wheel-rewards__title,
.event-page .wheel-icon-preview__title,
.event-page .wheel-login__title,
.event-page .wheel-code__value,
.event-page .wheel-reward-item__label { color: #f9fafb; }

.event-page .wheel-result__prize { color: #c4b5fd; }

.event-page .wheel-result.is-progress .wheel-result__prize,
.event-page .wheel-result__note,
.event-page .wheel-login__text { color: #cbd5e1; }

.event-page .wheel-result__meta,
.event-page .wheel-rewards__empty,
.event-page .wheel-reward-item__code,
.event-page .wheel-icon-preview__sub { color: #9ca3af; }

.event-page .wheel-result__dismiss:hover,
.event-page .wheel-rewards__dismiss:hover,
.event-page .wheel-login__dismiss:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #f9fafb;
}

.event-page .wheel-code {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.22);
}

.event-page .wheel-code__copy,
.event-page .wheel-btn--ghost {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    color: #e5e7eb;
}

.event-page .wheel-code__copy:hover,
.event-page .wheel-btn--ghost:hover {
    background: rgba(255, 255, 255, 0.14);
    color: #ffffff;
}

.event-page .wheel-rewards__head {
    background: rgba(255, 255, 255, 0.05);
    border-bottom-color: rgba(255, 255, 255, 0.12);
}

.event-page .wheel-reward-item {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.12);
}

.event-page .wheel-reward-item:hover {
    background: rgba(139, 92, 246, 0.16);
    border-color: #a78bfa;
}

.event-page .wheel-reward-item__icon {
    background: rgba(139, 92, 246, 0.2);
    border-color: rgba(139, 92, 246, 0.35);
    color: #ddd6fe;
}

.event-page .wheel-icon-preview__image-wrap {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
}
</style>
@endpush
@endif
@endsection
