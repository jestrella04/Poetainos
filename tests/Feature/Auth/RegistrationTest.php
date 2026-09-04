<?php

// The GET /register "screen" route is commented out in routes/auth.php —
// only POST /register (store) exists in this app.
test('new users can register', function (): void {
    // RegisteredUserController requires a seeded "user" role and a password
    // matching a custom complexity regex (upper + lower + digit/symbol, 8+ chars).
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
        'service_agreement' => true,
        'privacy_agreement' => true,
    ]);

    $this->assertAuthenticated();
    // RegisteredUserController::store() renders the verify-email prompt directly
    // rather than redirecting.
    $response->assertOk();
});
