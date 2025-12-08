<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Seller;

class SellerModelTest extends TestCase
{
    public function test_fillable_contains_simulation_columns()
    {
        $fillable = (new Seller())->getFillable();

        $this->assertNotContains('is_flexy', $fillable);
        $this->assertNotContains('is_website', $fillable);
        // flexy_number and flexy_instruction should remain
        $this->assertContains('flexy_number', $fillable);
        $this->assertContains('flexy_instruction', $fillable);
    }

    public function test_casts_contains_simulation_columns()
    {
        $seller = new Seller();
        $casts = method_exists($seller, 'getCasts') ? $seller->getCasts() : [];

        $this->assertArrayNotHasKey('is_flexy', $casts);
        $this->assertArrayNotHasKey('is_website', $casts);
        $this->assertArrayHasKey('website_enabled', $casts);
        $this->assertArrayHasKey('flexy_enabled', $casts);
    }

    // getStoreUrl relies on framework URL generator and is tested in integration tests.
}
