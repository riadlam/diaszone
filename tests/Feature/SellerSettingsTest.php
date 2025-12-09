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
}
