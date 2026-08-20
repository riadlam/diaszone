<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_hero_slides_appear_on_home_with_media_url_and_link(): void
    {
        HeroSlide::create([
            'title' => 'ML Promo',
            'image_path' => 'hero-slides/ml-promo.webp',
            'link_url' => '/mobilelegends',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'title' => 'Hidden',
            'image_path' => 'hero-slides/hidden.webp',
            'link_url' => '/freefire',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('diaszone-swiper', false);
        $response->assertSee('/media/hero-slides/ml-promo.webp', false);
        $response->assertSee('href="/mobilelegends"', false);
        $response->assertDontSee('hero-slides/hidden.webp', false);
        $response->assertDontSee('dz-hero__title', false);
    }

    public function test_external_links_open_in_new_tab(): void
    {
        $slide = HeroSlide::create([
            'title' => 'External',
            'image_path' => 'hero-slides/ext.webp',
            'link_url' => 'https://example.com/deal',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($slide->opensInNewTab());
        $this->assertStringContainsString('/media/hero-slides/ext.webp', $slide->imageUrl());

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('target="_blank"', false);
        $response->assertSee('https://example.com/deal', false);
    }
}
