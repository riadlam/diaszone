<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappFabTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_fab_is_rendered_on_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="whatsapp-fab"', false);
        $response->assertSee('wa.me/213556988175');
    }
}
