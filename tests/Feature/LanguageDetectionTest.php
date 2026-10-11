<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_follows_accept_language_french(): void
    {
        $this->withHeader('Accept-Language', 'fr-DZ,fr;q=0.9,en;q=0.8')
            ->get('/')
            ->assertOk();

        $this->assertSame('fr', app()->getLocale());
        $this->assertSame('fr', session('locale'));
    }

    public function test_first_visit_follows_accept_language_arabic(): void
    {
        $this->withHeader('Accept-Language', 'ar-DZ,ar;q=0.9')
            ->get('/')
            ->assertOk();

        $this->assertSame('ar', app()->getLocale());
    }

    public function test_session_locale_overrides_phone_language(): void
    {
        $this->withSession(['locale' => 'en'])
            ->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
            ->get('/')
            ->assertOk();

        $this->assertSame('en', app()->getLocale());
    }
}
