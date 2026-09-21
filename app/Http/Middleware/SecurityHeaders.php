<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = '';

        if (app()->isProduction()) {
            // Generate nonce before the request is handled so views can access it via app('csp-nonce')
            $nonce = $this->nonce();
            app()->instance('csp-nonce', $nonce);
            Vite::useCspNonce($nonce);
        }

        $response = $next($request);

        // Baseline headers applied in every environment
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        if (app()->isProduction()) {
            $response->headers->set(
                'Content-Security-Policy',
                implode('; ', [
                    "default-src 'self'",
                    "script-src 'self' 'nonce-{$nonce}' https://connect.facebook.net",
                    "style-src 'self' 'nonce-{$nonce}'",
                    "img-src 'self' data: blob: https:",
                    "font-src 'self'",
                    "connect-src 'self' https://graph.facebook.com",
                    'frame-src https://www.openstreetmap.org',
                    "frame-ancestors 'none'",
                    "form-action 'self'",
                    "object-src 'none'",
                    "base-uri 'self'",
                    'upgrade-insecure-requests',
                    'report-uri /csp-report',
                ]),
            );
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        } else {
            $viteUrl = $this->viteDevServerUrl();
            $scriptSrc = "script-src 'self' 'unsafe-inline' 'unsafe-eval'".($viteUrl ? " {$viteUrl}" : '');
            $styleSrc = "style-src 'self' 'unsafe-inline'".($viteUrl ? " {$viteUrl}" : '');
            $connectSrc = "connect-src 'self'".($viteUrl ? " {$viteUrl} ".preg_replace('/^http/', 'ws', $viteUrl) : '');
            $fontSrc = "font-src 'self' data:".($viteUrl ? " {$viteUrl}" : '');

            $response->headers->set(
                'Content-Security-Policy-Report-Only',
                implode('; ', [
                    "default-src 'self'",
                    $scriptSrc,
                    $styleSrc,
                    "img-src 'self' data: blob: https:",
                    $fontSrc,
                    $connectSrc,
                    'frame-src https://www.openstreetmap.org',
                    "frame-ancestors 'none'",
                    "form-action 'self'",
                    "object-src 'none'",
                    "base-uri 'self'",
                ]),
            );
        }

        return $response;
    }

    protected function nonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    protected function viteDevServerUrl(): ?string
    {
        $hotFile = public_path('hot');
        if (! file_exists($hotFile)) {
            return null;
        }

        $contents = file_get_contents($hotFile);

        return $contents === false ? null : rtrim($contents, "/\n\r");
    }
}
