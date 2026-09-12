<?php

use function Pest\Laravel\get;

test('the application returns a successful response', function (): void {
    $response = get('/');

    $response->assertStatus(200);
});
