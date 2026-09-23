<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

use function Pest\Laravel\get;
use function Pest\Laravel\withUnencryptedCookie;

describe('security headers', function (): void {
    it('are sent with every web response', function (): void {
        // When
        $response = get('/offline');

        // Then
        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('Strict-Transport-Security')
            ->assertHeader('Content-Security-Policy-Report-Only');
    });

    it('lets the Ziggy routes script run under the production policy', function (): void {
        // Given
        app()->detectEnvironment(fn (): string => 'production');

        // When
        $response = get('/offline');

        // Then
        $nonce = Str::match("/script-src 'self' 'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy') ?? '');
        expect($nonce)->not->toBeEmpty();
        // Ziggy emits the full script on its first render per process and a merge script after that
        expect($response->getContent())->toMatch('/<script type="text\/javascript" nonce="'.preg_quote($nonce, '/').'">(const Ziggy=|Object\.assign\(Ziggy\.routes)/');
    });

    it('allows runtime-injected inline styles under the production policy', function (): void {
        // Given
        app()->detectEnvironment(fn (): string => 'production');

        // When
        $response = get('/offline');

        // Then
        expect($response->headers->get('Content-Security-Policy'))->toContain("style-src 'self' 'unsafe-inline';");
    });
});

describe('document head', function (): void {
    it('declares the Vuetify cascade layer order before any other stylesheet', function (): void {
        // When
        $response = get('/offline');

        // Then
        $firstStylesheet = Str::match('/<style[^>]*>.*?<\/style>|<link[^>]*rel="stylesheet"[^>]*>/s', $response->getContent());
        expect($firstStylesheet)->toBe('<style>@layer vuetify-core, vuetify-components, vuetify-overrides, vuetify-utilities, vuetify-final;</style>');
    });
});

describe('appearance', function (): void {
    it('is shared with views from the appearance cookie', function (string $cookieValue, string $expected): void {
        // When
        withUnencryptedCookie('appearance', $cookieValue)->get('/offline');

        // Then
        expect(View::shared('appearance'))->toBe($expected);
    })->with([
        'a valid value' => ['dark', 'dark'],
        'an unknown value' => [fn (): string => fake()->lexify('appearance-????'), 'system'],
    ]);
});
