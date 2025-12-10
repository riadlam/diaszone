<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabling_flexy_requires_number_and_instruction()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => null,
        ]);

        // Restrict this seller to mobilelegends only to make the test deterministic
        $seller->allowed_games = ['mobilelegends'];
        $seller->save();
        $this->actingAs($seller, 'seller');

        // Try enabling with neither number nor instruction
        $resp = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '',
            'flexy_instruction' => '',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['flexy_number', 'flexy_instruction']);

        $seller->refresh();
        $this->assertFalse($seller->flexy_enabled);
        $this->assertNull($seller->flexy_number);

        // Try enabling with number but no instruction
        $resp2 = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '0666000000',
            'flexy_instruction' => '',
        ]);
        $resp2->assertStatus(302);
        $resp2->assertSessionHasErrors(['flexy_instruction']);
        $seller->refresh();
        $this->assertFalse($seller->flexy_enabled);

        // Try enabling with instruction but no number
        $resp3 = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '',
            'flexy_instruction' => 'Send payment',
        ]);
        $resp3->assertStatus(302);
        $resp3->assertSessionHasErrors(['flexy_number']);
        $seller->refresh();
        $this->assertFalse($seller->flexy_enabled);
    }

    public function test_enabling_flexy_with_number_and_instruction_saves()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => null,
        ]);

        $this->actingAs($seller, 'seller');

        // Create a pack and seller flexy price so enabling Flexy isn't blocked by pricing rules
        $pack = \App\Models\DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack 5',
            'code' => 'ML205',
            'diamonds' => 100,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 120,
            'custom_price_usd' => 1.2,
            'flexy_price' => 130,
            'is_active' => true,
        ]);

        // ensure any other packs in DB are populated with flexy price for this seller
        foreach (\App\Models\DiamondPack::where('game_type', 'mobilelegends')->get() as $p) {
            if (!\App\Models\SellerGamePrice::where('seller_id', $seller->id)->where('diamond_pack_id', $p->id)->exists()) {
                \App\Models\SellerGamePrice::create([
                    'seller_id' => $seller->id,
                    'diamond_pack_id' => $p->id,
                    'custom_price_dzd' => $p->price_dzd ?? ($p->base_price_dzd ?? 1),
                    'custom_price_usd' => $p->price ?? 0.0,
                    'flexy_price' => ($p->price_dzd ?? ($p->base_price_dzd ?? 0)) + 10,
                    'is_active' => true,
                ]);
            }
        }

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

    public function test_enabling_website_requires_logo_and_banner()
    {
        $seller = Seller::factory()->create([
            'website_enabled' => false,
            'website_url' => null,
            'store_logo' => null,
            'store_logo_thumb' => null,
            'store_banner' => null,
            'store_banner_resized' => null,
        ]);

        $this->actingAs($seller, 'seller');

        // Try enabling with nothing
        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'mywebslug',
        ]);
        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['store_logo', 'store_banner']);
        $seller->refresh();
        $this->assertFalse($seller->website_enabled);

        // Add only logo
        $logo = \Illuminate\Http\UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $resp2 = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'mywebslug',
            'store_logo' => $logo,
        ]);
        $resp2->assertStatus(302);
        $resp2->assertSessionHasErrors(['store_banner']);
        $seller->refresh();
        $this->assertFalse($seller->website_enabled);

        // Add only banner
        $banner = \Illuminate\Http\UploadedFile::fake()->create('banner.jpg', 400, 'image/jpeg');
        $resp3 = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'mywebslug',
            'store_banner' => $banner,
        ]);
        $resp3->assertStatus(302);
        $resp3->assertSessionHasErrors(['store_logo']);
        $seller->refresh();
        $this->assertFalse($seller->website_enabled);

        // Add both logo and banner
        $logo2 = \Illuminate\Http\UploadedFile::fake()->create('logo2.png', 100, 'image/png');
        $banner2 = \Illuminate\Http\UploadedFile::fake()->create('banner2.jpg', 400, 'image/jpeg');
        $resp4 = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'mywebslug',
            'store_logo' => $logo2,
            'store_banner' => $banner2,
        ]);
        $resp4->assertStatus(302);
        $resp4->assertSessionHasNoErrors();
        $seller->refresh();
        $this->assertTrue($seller->website_enabled);
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
        \Illuminate\Support\Facades\Storage::fake('public');

        $logo = \Illuminate\Http\UploadedFile::fake()->create('logo.png', 100, 'image/png');
        $banner = \Illuminate\Http\UploadedFile::fake()->create('banner.jpg', 400, 'image/jpeg');

        $seller = Seller::factory()->create([
            'website_enabled' => false,
            'website_url' => null,
            'username' => 'olduser'
        ]);

        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'website_enabled' => 1,
            'website_url' => 'my-store-123',
            'store_logo' => $logo,
            'store_banner' => $banner,
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

    public function test_enabling_flexy_requires_flexy_price_for_all_packs()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => '0611111111',
        ]);

        // Create a couple of packs for mobilelegends and freefire
        $pack1 = \App\Models\DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack 1',
            'code' => 'ML100',
            'diamonds' => 100,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $pack2 = \App\Models\DiamondPack::create([
            'game_type' => 'freefire',
            'name' => 'FF Pack',
            'code' => 'FF100',
            'diamonds' => 50,
            'price' => 1.0,
            'price_dzd' => 50,
            'base_price_dzd' => 40,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($seller, 'seller');

        // No SellerGamePrice entries => enabling flexy should fail
        $resp = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '0611111111',
            'flexy_instruction' => 'Pay using Flexy',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['flexy_enabled']);
        $seller->refresh();
        $this->assertFalse($seller->flexy_enabled);
    }

    public function test_enabling_flexy_succeeds_when_all_packs_have_flexy_price()
    {
        $seller = Seller::factory()->create([
            'flexy_enabled' => false,
            'flexy_number' => '0611111111',
        ]);

        // Create packs and seller prices with flexy_price set
        $pack1 = \App\Models\DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Pack 1',
            'code' => 'ML100',
            'diamonds' => 100,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack1->id,
            'custom_price_dzd' => 120,
            'custom_price_usd' => 1.2,
            'flexy_price' => 130,
            'is_active' => true,
        ]);

        // Ensure all packs present in DB have a flexy price set for this seller
        foreach (\App\Models\DiamondPack::all() as $p) {
            if (!\App\Models\SellerGamePrice::where('seller_id', $seller->id)->where('diamond_pack_id', $p->id)->exists()) {
                \App\Models\SellerGamePrice::create([
                    'seller_id' => $seller->id,
                    'diamond_pack_id' => $p->id,
                    'custom_price_dzd' => $p->price_dzd ?? ($p->base_price_dzd ?? 100),
                    'custom_price_usd' => $p->price ?? 0.0,
                    'flexy_price' => ($p->price_dzd ?? ($p->base_price_dzd ?? 100)) + 10,
                    'is_active' => true,
                ]);
            }
        }

        $seller->allowed_games = ['mobilelegends'];
        $seller->save();
        $this->actingAs($seller, 'seller');

        $resp = $this->post(route('seller.settings.update'), [
            'flexy_enabled' => 1,
            'flexy_number' => '0611111111',
            'flexy_instruction' => 'Pay using Flexy',
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasNoErrors();
        $seller->refresh();
        $this->assertTrue($seller->flexy_enabled);
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
        // Preview image should be present and point to the storage_public path
        $resp->assertSee('id="store-banner-preview"', false);
        $resp->assertSee('/storage_public/seller-banners/resized/banner_resized.jpg', false);
        $resp->assertSee('banner.jpg', false);
        $resp->assertSee('id="choose-logo-btn"', false);
        $resp->assertSee('id="choose-banner-btn"', false);
    }
}
