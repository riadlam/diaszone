<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class DigiflazzCekSaldoTest extends TestCase
{
    public function test_cek_saldo_parses_deposit_correctly()
    {
        // Arrange: configure service and fake HTTP response
        config(['services.digiflazz.username' => 'testuser', 'services.digiflazz.sign' => 'testkey']);

        $fakeResponse = [
            'data' => [
                'deposit' => 123456789,
            ],
        ];

        Http::fake([
            '*' => Http::response($fakeResponse, 200),
        ]);

        $service = app(\App\Services\DigiflazzService::class);

        // Act
        $result = $service->cekSaldo();

        // Assert
        $this->assertTrue($result['result']);
        $this->assertArrayHasKey('deposit', $result);
        $this->assertEquals(123456789, $result['deposit']);
        $this->assertEquals($fakeResponse['data'], $result['data']);
    }
}
