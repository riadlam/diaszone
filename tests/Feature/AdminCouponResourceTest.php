<?php

namespace Tests\Feature;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use App\Models\DiamondPack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCouponResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'status' => 'active',
        ]);
    }

    private function makePack(): DiamondPack
    {
        return DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => '86 Diamonds',
            'code' => 'ml-86-'.uniqid(),
            'diamonds' => 86,
            'price' => 1.5,
            'price_dzd' => 250,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_open_coupons_list(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/coupons')
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(ListCoupons::class)
            ->assertSuccessful();
    }

    public function test_admin_can_create_percentage_sitewide_coupon(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateCoupon::class)
            ->fillForm([
                'code' => 'save10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'applies_to' => 'all',
                'max_uses' => 100,
                'max_uses_per_user' => 1,
                'min_order_amount' => 500,
                'is_active' => true,
                'description' => 'Sitewide 10%',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $coupon = Coupon::findByCode('SAVE10');
        $this->assertNotNull($coupon);
        $this->assertSame('percentage', $coupon->discount_type);
        $this->assertEquals(10.0, (float) $coupon->discount_value);
        $this->assertSame('all', $coupon->applies_to);
        $this->assertNull($coupon->allowed_games);
        $this->assertSame('admin', $coupon->created_by);
        $this->assertTrue($coupon->isValid());
        $this->assertFalse($coupon->isFullDiscount());

        $calc = $coupon->calculateDiscount(1000);
        $this->assertEquals(100.0, $calc['discount_amount']);
        $this->assertEquals(900.0, $calc['final_amount']);
    }

    public function test_admin_can_create_fixed_game_scoped_coupon(): void
    {
        $admin = $this->admin();
        $pack = $this->makePack();

        Livewire::actingAs($admin)
            ->test(CreateCoupon::class)
            ->fillForm([
                'code' => 'ML500',
                'discount_type' => 'fixed',
                'discount_value' => 500,
                'applies_to' => 'specific',
                'allowed_games' => ['mlbb'],
                'allowed_packages' => [$pack->id],
                'max_uses_per_user' => 2,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $coupon = Coupon::findByCode('ML500');
        $this->assertNotNull($coupon);
        $this->assertSame('fixed', $coupon->discount_type);
        $this->assertSame('specific', $coupon->applies_to);
        $this->assertSame(['mlbb'], $coupon->allowed_games);
        $this->assertSame([$pack->id], $coupon->allowed_packages);
        $this->assertTrue($coupon->appliesToPackage('mlbb', $pack->id));
        $this->assertTrue($coupon->appliesToPackage('mobilelegends', $pack->id));
        $this->assertFalse($coupon->appliesToPackage('freefire', $pack->id));
    }

    public function test_admin_can_create_free_100_percent_coupon(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateCoupon::class)
            ->fillForm([
                'code' => 'FREE100',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'applies_to' => 'specific',
                'allowed_games' => ['freefire'],
                'max_uses' => 5,
                'max_uses_per_user' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $coupon = Coupon::findByCode('FREE100');
        $this->assertNotNull($coupon);
        $this->assertTrue($coupon->isFullDiscount());
        $calc = $coupon->calculateDiscount(800);
        $this->assertTrue($calc['is_free']);
        $this->assertEquals(0.0, $calc['final_amount']);
    }

    public function test_specific_coupon_requires_at_least_one_game(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreateCoupon::class)
            ->fillForm([
                'code' => 'NOSCOPE',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'applies_to' => 'specific',
                'allowed_games' => [],
                'max_uses_per_user' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['allowed_games']);

        $this->assertNull(Coupon::findByCode('NOSCOPE'));
    }

    public function test_adm_coupons_redirects_to_filament(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/adm/coupons')
            ->assertRedirect('/admin/coupons');
    }
}
