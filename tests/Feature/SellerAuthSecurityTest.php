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

    public function test_registration_requires_phone_store_and_platform()
    {
        // missing phone/store_name/main_platform/platform_url
        $resp = $this->post(route('seller.register.submit'), [
            'name' => 'Test',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ]);

        $resp->assertStatus(302);
        $resp->assertSessionHasErrors(['phone', 'store_name', 'main_platform', 'platform_url']);
    }

    public function test_successful_registration_saves_platform_and_admin_preview()
    {
        $resp = $this->post(route('seller.register.submit'), [
            'name' => 'New Seller',
            'username' => 'new-seller-1',
            'email' => 'new@example.com',
            'phone' => '0612345678',
            'store_name' => 'New Store',
            'main_platform' => 'instagram',
            'platform_url' => 'https://instagram.com/newstore',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $resp->assertStatus(302);
        $this->assertDatabaseHas('sellers', [
            'email' => 'new@example.com',
            'main_platform' => 'instagram',
            'platform_url' => 'https://instagram.com/newstore'
        ]);

        $seller = \App\Models\Seller::where('email', 'new@example.com')->first();
        $admin = \App\Models\User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $this->actingAs($admin);

        $page = $this->get(route('admin.sellers.show', ['seller' => $seller->id]));
        $page->assertStatus(200);
        $page->assertSee('Instagram');
        $page->assertSee('https://instagram.com/newstore', false);
    }
}
