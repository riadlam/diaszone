<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DigiflazzWebhookTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_valid_signature_is_accepted()
    {
        $secret = 'testsecret123';
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=' . $secret);

        $payload = ['data' => ['ref_id' => 'test123', 'customer_no' => '08123', 'status' => 'Sukses', 'rc' => '00']];
        $raw = json_encode($payload);
        $sig = 'sha1=' . hash_hmac('sha1', $raw, $secret);

        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-Hub-Signature' => $sig,
            'X-Digiflazz-Event' => 'create',
            'User-Agent' => 'Digiflazz-Hookshot',
        ])->postJson(route('digiflazz.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    public function test_missing_signature_is_rejected()
    {
        $secret = 'testsecret456';
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=' . $secret);

        $payload = ['data' => ['ref_id' => 'test456']];

        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-Digiflazz-Event' => 'create',
            'User-Agent' => 'Digiflazz-Hookshot',
        ])->postJson(route('digiflazz.webhook'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Missing signature']);
    }

    public function test_missing_secret_in_production_is_rejected()
    {
        // Simulate production environment
        config(['app.env' => 'production']);
        putenv('DIGIFLAZZ_WEBHOOK_SECRET=');

        $payload = ['data' => ['ref_id' => 'test789']];

        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'X-Digiflazz-Event' => 'create',
            'User-Agent' => 'Digiflazz-Hookshot',
        ])->postJson(route('digiflazz.webhook'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Webhook secret not configured']);
    }
}
