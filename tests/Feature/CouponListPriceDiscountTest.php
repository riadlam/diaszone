<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\DiamondPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponListPriceDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_percentage_applies_to_list_price_not_sale_price(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $pack = DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'Sale Pack',
            'code' => 'ml-sale-'.uniqid(),
            'diamonds' => 100,
            'price' => 4,
            'price_dzd' => 1000,
            'discount_percentage' => 10, // sale total = 900
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Coupon::create([
            'code' => 'SAVE20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'applies_to' => 'all',
            'max_uses_per_user' => 5,
            'is_active' => true,
            'created_by' => 'admin',
        ]);

        // Even if client sends the sale amount (900), server must use list price (1000).
        $response = $this->actingAs($user)->postJson(route('api.coupon.validate'), [
            'code' => 'SAVE20',
            'game_code' => 'mlbb',
            'package_id' => $pack->id,
            'quantity' => 1,
            'amount' => 900,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('discount.original_amount', 1000)
            ->assertJsonPath('discount.discount_amount', 200) // 20% of list 1000
            ->assertJsonPath('discount.final_amount', 800); // list − coupon only (pack sale not stacked)
    }
}
