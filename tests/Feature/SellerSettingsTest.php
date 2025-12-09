<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabling_flexy_requires_number()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => null,
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '',
        ]);

        // Should redirect back with validation errors
        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['flexy_number']);

        $seller->refresh();
        $this->assertFalse($seller->flexy_enabled, 'flexy_enabled should remain false when validation fails');
        $this->assertNull($seller->flexy_number, 'flexy_number should not be set when validation fails');
    }

    public function test_enabling_flexy_with_number_saves()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => null,
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '0673771763',
            'flexy_instruction' => 'Use reference ABC',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasNoErrors();

        $seller->refresh();
        $this->assertTrue($seller->flexy_enabled);
        $this->assertEquals('0673771763', $seller->flexy_number);
        $this->assertEquals('Use reference ABC', $seller->flexy_instruction);
    }

    public function test_website_enabled_requires_slug()
    {
        $seller = Seller::factory()->create([
            'website_enabled' => false,
            'website_url' => null,
            'username' => 'originaluser',
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => '',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['website_url']);

        $seller->refresh();
        $this->assertFalse($seller->website_enabled);
        $this->assertNull($seller->website_url);
        $this->assertEquals('originaluser', $seller->username);
    }

    public function test_website_slug_saves_and_updates_username()
    {
        $seller = Seller::factory()->create([
            'website_enabled' => false,
            'website_url' => null,
            'username' => 'olduser'
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'my-store-123',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasNoErrors();

        $seller->refresh();
        $this->assertTrue($seller->website_enabled);
        $this->assertEquals('my-store-123', $seller->website_url);
        // username should be updated to match slug
        $this->assertEquals('my-store-123', $seller->username);

        // public store should be reachable by new username
        $get = $this->get(route('seller.store.home', ['username' => 'my-store-123']));
        $get->assertStatus(200);

        // old username should no longer resolve to storefront
        $getOld = $this->get(route('seller.store.home', ['username' => 'olduser']));
        $getOld->assertStatus(404);
    }

    public function test_upload_logo_and_banner_saved_and_shown_on_storefront()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $seller = Seller::factory()->create([
            'website_enabled' => true,
            'website_url' => 'myslug',
            'username' => 'myslug',
            'store_name' => 'My Store'
        ]);

        $this->actingAs($seller, 'seller');

        $logo = \Illuminate\Http\UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $banner = \Illuminate\Http\UploadedFile::fake()->create('banner.jpg', 400, 'image/jpeg');

        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'myslug',
            'store_logo' => $logo,
            'store_banner' => $banner,
        ]);

        $resp->assertStatus(302);

        $seller->refresh();

        $this->assertNotNull($seller->store_logo);
        $this->assertNotNull($seller->store_banner);
        $this->assertNotNull($seller->store_logo_thumb);
        $this->assertNotNull($seller->store_banner_resized);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($seller->store_logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($seller->store_banner);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($seller->store_logo_thumb);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($seller->store_banner_resized);

        // The storefront home should include the asset URLs (mobile markup still present in HTML)
        $home = $this->get(route('seller.store.home', ['username' => $seller->username]));
        $home->assertStatus(200);
        $home->assertSee($seller->store_logo);
        $home->assertSee($seller->store_banner);
    }

    public function test_remove_logo_and_banner_via_ajax()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        // prepare fake files
        \Illuminate\Support\Facades\Storage::disk('public')->put('seller-logos/logo.png', 'file');
        \Illuminate\Support\Facades\Storage::disk('public')->put('seller-logos/thumbs/logo_thumb.png', 'file');
        \Illuminate\Support\Facades\Storage::disk('public')->put('seller-banners/banner.jpg', 'file');
        \Illuminate\Support\Facades\Storage::disk('public')->put('seller-banners/resized/banner_resized.jpg', 'file');

        $seller = Seller::factory()->create([
            'website_enabled' => true,
            'website_url' => 'removeslug',
            'username' => 'removeslug',
            'store_logo' => 'seller-logos/logo.png',
            'store_logo_thumb' => 'seller-logos/thumbs/logo_thumb.png',
            'store_banner' => 'seller-banners/banner.jpg',
            'store_banner_resized' => 'seller-banners/resized/banner_resized.jpg',
        ]);

        $this->actingAs($seller, 'seller');

        // remove logo
        $resp = $this->postJson(route('seller.settings.remove-image'), ['type' => 'logo']);
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $seller->refresh();
        $this->assertNull($seller->store_logo);
        $this->assertNull($seller->store_logo_thumb);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('seller-logos/logo.png');
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('seller-logos/thumbs/logo_thumb.png');

        // remove banner
        $resp2 = $this->postJson(route('seller.settings.remove-image'), ['type' => 'banner']);
        $resp2->assertStatus(200)->assertJson(['success' => true]);

        $seller->refresh();
        $this->assertNull($seller->store_banner);
        $this->assertNull($seller->store_banner_resized);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('seller-banners/banner.jpg');
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('seller-banners/resized/banner_resized.jpg');
    }

    public function test_slug_conflict_with_existing_username_fails()
    {
        // another seller has username 'taken'
        Seller::factory()->create(['username' => 'taken', 'website_enabled' => false]);

        $seller = Seller::factory()->create([
            'website_enabled' => false,
            'website_url' => null,
            'username' => 'owner',
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'taken',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['website_url']);

        $seller->refresh();
        $this->assertFalse($seller->website_enabled);
        $this->assertNotEquals('taken', $seller->username);
    }

    public function test_settings_page_shows_filename_and_choose_buttons()
    {
        $seller = Seller::factory()->create([
            'store_logo' => 'seller-logos/logo.png',
            'store_logo_thumb' => 'seller-logos/thumbs/logo_thumb.png',
            'store_banner' => 'seller-banners/banner.jpg',
            'store_banner_resized' => 'seller-banners/resized/banner_resized.jpg',
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->get(route('seller.settings'));
        $resp->assertStatus(200);

        // Filename spans and choose buttons should be present in the settings HTML
        $resp->assertSee('id="store-logo-filename"', false);
        $resp->assertSee('logo.png', false);
        $resp->assertSee('id="store-banner-filename"', false);
        $resp->assertSee('banner.jpg', false);
        $resp->assertSee('id="choose-logo-btn"', false);
        $resp->assertSee('id="choose-banner-btn"', false);
    }
}
