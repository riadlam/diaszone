<?php

namespace Tests\Unit;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_seller_defaults_website_and_flexy_disabled()
    {
        // Create a new seller without providing website_enabled or flexy_enabled
        $seller = Seller::create([
            'name' => 'Default Seller',
            'username' => 'default-seller',
            'email' => 'default@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($seller->website_enabled);
        $this->assertFalse($seller->flexy_enabled);
    }
}
