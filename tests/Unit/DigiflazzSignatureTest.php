<?php

namespace Tests\Unit;

use Tests\TestCase;

class DigiflazzSignatureTest extends TestCase
{
    public function test_compute_sign_matches_expected()
    {
        $service = app(\App\Services\DigiflazzService::class);

        // Use known values
        $username = 'voyemio37aPo';
        $key = '8ca38a94-c599-549b-b01d-e37898b23408';
        $ref = 'order-151-SE8THjLx';

        // Temporarily set service properties
        \Closure::bind(function () use ($service, $username, $key) {
            $service->username = $username;
            $service->sign = $key;
        }, null, $service)->__invoke();

        $expected = md5($username . $key . $ref);
        $this->assertEquals($expected, $service->computeSign($ref));
    }
}
