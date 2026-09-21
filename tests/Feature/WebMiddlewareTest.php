<?php

use Illuminate\Support\Facades\View;

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
});

describe('appearance', function (): void {
    it('is shared with views from the appearance cookie', function (string $cookieValue, string $expected): void {
        // When
        withUnencryptedCookie('appearance', $cookieValue)->get('/offline');

        // Then
        expect(View::shared('appearance'))->toBe($expected);
    })->with([
        'a valid value' => ['dark', 'dark'],
        'an unknown value' => ['neon', 'system'],
    ]);
});
