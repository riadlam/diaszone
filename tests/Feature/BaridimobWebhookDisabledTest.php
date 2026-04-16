<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaridimobWebhookDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_baridimob_webhook_returns_410(): void
    {
        $response = $this->postJson(route('baridimob.webhook'), []);

        $response->assertStatus(410);
        $response->assertJson([
            'message' => 'Chargily webhook is disabled. Baridimob uses SofizPay CIB return verification.',
        ]);
    }
}
