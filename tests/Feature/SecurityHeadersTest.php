<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_security_headers_are_sent(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_csp_nonce_matches_inline_scripts(): void
    {
        $response = $this->get('/');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9]+)'/", $csp);

        preg_match("/'nonce-([A-Za-z0-9]+)'/", $csp, $m);
        $response->assertSee('<script nonce="'.$m[1].'">', false);
    }

    public function test_error_pages_also_receive_security_headers(): void
    {
        $this->get('/definitely-missing')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
