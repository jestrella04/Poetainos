<?php

use function Pest\Laravel\get;

describe('the application', function (): void {
    it('returns a successful response', function (): void {
        // When
        $response = get('/');

        // Then
        $response->assertStatus(200);
    });
});
