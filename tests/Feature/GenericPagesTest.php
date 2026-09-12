<?php

test('the offline page renders', function (): void {
    $this->get('/offline')->assertOk();
});
