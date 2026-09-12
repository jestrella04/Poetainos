<?php

use function Pest\Laravel\get;

test('the offline page renders', function (): void {
    get('/offline')->assertOk();
});
