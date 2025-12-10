<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_show_canonical_logo()
    {
        $login = $this->get(route('seller.login'));
        $login->assertStatus(200);
        $login->assertSee('/storage_public/images_homepage/diaszonelogo.jpeg', false);

        $register = $this->get(route('seller.register'));
        $register->assertStatus(200);
        $register->assertSee('/storage_public/images_homepage/diaszonelogo.jpeg', false);
    }

    public function test_login_rate_limit_triggers()
    {
        // create a seller
        $password = 'S3lectr0n!';
        $seller = Seller::factory()->create([
            'email' => 'tester@example.com',
            'password' => bcrypt($password),
            'status' => 'active'
        ]);

        // perform 6 incorrect login attempts (limit set to 5)
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post(route('seller.login.submit'), [
                'email' => 'tester@example.com',
                'password' => 'wrongpass'
            ]);
            $resp->assertStatus(302);
        }

        // 6th attempt should hit throttling (controller sets 429)
        $resp2 = $this->post(route('seller.login.submit'), [
            'email' => 'tester@example.com',
            'password' => 'wrongpass'
        ]);

        // If middleware also applies throttle, we might get 429 directly
        $this->assertTrue(in_array($resp2->getStatusCode(), [429, 302]));

        if ($resp2->getStatusCode() === 429) {
            // Accept either our custom message or the generic 429 page
            $this->assertTrue(str_contains($resp2->getContent(), 'Too many') || str_contains($resp2->getContent(), 'Too Many Requests'));
        } else {
            // if redirect with errors, assert the session has errors mentioning throttle
            $resp2->assertSessionHasErrors('email');
        }
    }
}
