<?php

use function Pest\Laravel\get;

describe('the offline page', function (): void {
    it('renders', function (): void {
        // When
        $response = get('/offline');

        // Then
        $response->assertOk();
    });
});
