<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerLogoDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_store_logo_when_set()
    {
        $seller = Seller::factory()->create([
            'store_logo' => 'seller-logos/logo.png',
            'store_logo_thumb' => 'seller-logos/thumbs/logo_thumb.png',
            'store_name' => 'My Store'
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->get(route('seller.dashboard'));
        $resp->assertStatus(200);

        // sidebar and header should include storage_public path for logo
        $resp->assertSee('/storage_public/seller-logos/thumbs/logo_thumb.png', false);
        $resp->assertSee('/storage_public/seller-logos/thumbs/logo_thumb.png', false);
    }

    public function test_storefront_header_uses_storage_public_paths_and_no_public_storage_segment()
    {
        $seller = Seller::factory()->create([
            'store_logo' => 'seller-logos/logo.png',
            'store_logo_thumb' => 'seller-logos/thumbs/logo_thumb.png',
            'store_banner' => 'seller-banners/banner.jpg',
            'store_banner_resized' => 'seller-banners/resized/banner_resized.jpg',
            'website_enabled' => true,
            'username' => 'teststore'
        ]);

        $resp = $this->get(route('seller.store.home', ['username' => $seller->username]));
        $resp->assertStatus(200);

        // correct public path present
        $resp->assertSee('/storage_public/seller-logos/thumbs/logo_thumb.png', false);
        $resp->assertSee('/storage_public/seller-banners/resized/banner_resized.jpg', false);

        // ensure the wrong /public/storage path is not present
        $resp->assertDontSee('/public/storage/', false);
    }
}
