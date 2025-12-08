<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\SellerPayoutRequest;
use App\Models\SellerWalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerPayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_payout_request_and_funds_are_reserved()
    {
        $this->markTestSkipped('Payout feature removed - tests deprecated.');
    }

    public function test_seller_cannot_request_more_than_balance()
    {
        $this->markTestSkipped('Payout feature removed - tests deprecated.');
    }
}
