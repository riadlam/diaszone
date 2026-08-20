@php
    /** @var \Illuminate\Support\Collection<\App\Models\HeroSlide> $heroSlides */
    $heroSlides = $heroSlides ?? collect();
@endphp

@if($heroSlides->isNotEmpty())
<section id="diaszone-hero-slider" class="dz-coverflow relative w-full overflow-hidden py-6 md:py-10" aria-label="{{ __('home.hero_title') }}">
    <div class="swiper diaszone-swiper relative z-0 mx-auto rounded-xl max-w-[95%] xs:max-w-[380px] lg:max-w-[906px] h-[162px] lg:h-[385px]"
         style="overflow: visible;">
        <div class="swiper-wrapper">
            @foreach($heroSlides as $index => $slide)
                @php
                    $href = $slide->href();
                    $hasLink = $href !== '#';
                    $img = $slide->imageUrl();
                    $alt = $slide->title ?: ('Slide '.($index + 1));
                @endphp
                <div class="swiper-slide">
                    @if($hasLink)
                        <a class="dz-coverflow__link relative block h-full"
                           href="{{ $href }}"
                           @if($slide->opensInNewTab()) target="_blank" rel="noopener noreferrer" @endif>
                    @else
                        <div class="dz-coverflow__link relative block h-full">
                    @endif
                        @if($img)
                            <img src="{{ $img }}"
                                 alt="{{ $alt }}"
                                 class="dz-coverflow__img object-cover rounded-xl w-full h-[162px] lg:h-[385px]"
                                 width="906"
                                 height="385"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                 fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                                 decoding="async">
                        @else
                            <div class="rounded-xl w-full h-[162px] lg:h-[385px] bg-gradient-to-br from-purple-700 to-fuchsia-700"></div>
                        @endif
                        <div class="dz-coverflow__dim absolute inset-0 rounded-xl bg-black/60 pointer-events-none" aria-hidden="true"></div>
                    @if($hasLink)
                        </a>
                    @else
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>
@endif
