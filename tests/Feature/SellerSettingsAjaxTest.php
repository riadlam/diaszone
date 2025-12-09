<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerSettingsAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_available_when_unused()
    {
        $seller = Seller::factory()->create(['username' => 'alice']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'unique-slug']);
        $resp->assertStatus(200)->assertJson(['available' => true]);
    }

    public function test_slug_unavailable_when_taken_by_other_username()
    {
        // another seller with username 'taken' exists
        Seller::factory()->create(['username' => 'taken']);

        $seller = Seller::factory()->create(['username' => 'owner']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'taken']);
        $resp->assertStatus(200)->assertJson(['available' => false]);
    }

    public function test_slug_unavailable_when_taken_by_other_website_url()
    {
        Seller::factory()->create(['username' => 'foo', 'website_url' => 'myslug']);

        $seller = Seller::factory()->create(['username' => 'owner2']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'myslug']);
        $resp->assertStatus(200)->assertJson(['available' => false]);
    }
}
