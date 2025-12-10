<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSellerActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_activation_resets_website_and_flexy_flags()
    {
        // seller has flags set but is pending
        $seller = Seller::factory()->create([
            'status' => 'pending',
            'website_enabled' => true,
            'flexy_enabled' => true,
        ]);

        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $this->actingAs($admin);

        $resp = $this->patch(route('admin.sellers.status', ['seller' => $seller->id]), ['status' => 'active']);
        $resp->assertStatus(302);

        $seller->refresh();
        $this->assertFalse($seller->website_enabled, 'website_enabled should be disabled after admin activation');
        $this->assertFalse($seller->flexy_enabled, 'flexy_enabled should be disabled after admin activation');
    }
}
