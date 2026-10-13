<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers plus a nonce-based Content Security Policy.
 *
 * The nonce is generated before the response is rendered so that @vite
 * tags and the inline theme script can carry it. The CSP is skipped while
 * the Vite dev server is running (its scripts come from another origin).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (config('portfolio.security.csp') && ! Vite::isRunningHot()) {
            $headers->set('Content-Security-Policy', $this->policy($nonce, $request->isSecure()));
        }

        if (config('portfolio.seo.block_non_production') && ! app()->isProduction()) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private function policy(string $nonce, bool $secure): string
    {
        $directives = [
            "default-src 'self'",
            // googletagmanager.com: gtag.js injects further script tags of its own at
            // runtime, which don't carry our nonce, so it needs the origin allowed too.
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com",
            // Inline style attributes are used for animation delays and CSS custom properties.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            // google-analytics.com: where gtag.js actually sends hits (googletagmanager.com
            // only serves the library itself).
            "connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com https://www.googletagmanager.com",
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "manifest-src 'self'",
        ];

        if ($secure) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
