@php
    $slides = [
        [
            'image' => url('storage_public/images_homepage/testslide.webp'),
            'badge' => __('home.new_season'),
            'title' => __('home.top_up_now'),
            'text' => __('home.best_deals'),
            'cta' => __('common.shop_now'),
            'href' => '#home-games',
        ],
    ];
@endphp

<section class="dz-hero gaming-hero-slider" aria-label="{{ __('home.hero_title') }}">
    <div class="dz-hero__shell">
        <div class="dz-hero__viewport slider-wrapper">
            <div id="slider-container" class="dz-hero__track slider-container">
                @foreach($slides as $index => $slide)
                    <article class="dz-hero__slide slide" data-slide-index="{{ $index }}">
                        <div class="dz-hero__media">
                            <img src="{{ $slide['image'] }}"
                                 alt="{{ $slide['title'] }}"
                                 class="dz-hero__image"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                 decoding="async">
                            <div class="dz-hero__scrim" aria-hidden="true"></div>
                            <div class="dz-hero__glow" aria-hidden="true"></div>
                        </div>

                        <div class="dz-hero__content">
                            <span class="dz-hero__badge">{{ $slide['badge'] }}</span>
                            <h2 class="dz-hero__title">{{ $slide['title'] }}</h2>
                            <p class="dz-hero__text">{{ $slide['text'] }}</p>
                            <a href="{{ $slide['href'] }}" class="dz-hero__cta">{{ $slide['cta'] }}</a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if(count($slides) > 1)
                <button type="button"
                        class="dz-hero__nav dz-hero__nav--prev slider-prev"
                        aria-label="{{ __('event.previous_image') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <button type="button"
                        class="dz-hero__nav dz-hero__nav--next slider-next"
                        aria-label="{{ __('event.next_image') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
                    </svg>
                </button>

                <div class="dz-hero__dots" role="tablist" aria-label="Slides">
                    @foreach($slides as $index => $slide)
                        <button type="button"
                                class="dz-hero__dot slider-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-slide="{{ $index }}"
                                aria-label="Slide {{ $index + 1 }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
