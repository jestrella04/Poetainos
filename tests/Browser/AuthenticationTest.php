<?php

use Illuminate\Support\Facades\Hash;
use Tests\Browser\Pages\LoginPage;

describe('login happy path', function (): void {
    it('logs a user in through the real UI flow and lands authenticated', function (): void {
        $password = fake()->password();
        $user = createUserWithPassword($password);

        LoginPage::open()
            ->loginWithEmail($user->email, $password)
            ->browser()
            ->assertPathIs('/')
            ->assertNoJavaScriptErrors();
    });
});

describe('the route table sent to the browser', function (): void {
    it('leaves the admin routes out for visitors', function (): void {
        $browser = LoginPage::open()->browser();

        expect($browser->script("route().has('admin.index')"))->toBeFalse();
    });

    it('gains the admin routes once an admin signs in, without a reload', function (): void {
        $password = fake()->password();
        $admin = actingAsAdmin(['password' => Hash::make($password)]);
        auth()->logout();

        $browser = LoginPage::open()
            ->loginWithEmail($admin->email, $password)
            ->browser()
            ->assertPathIs('/');

        // Both route() helpers: the global one composables call, and the one templates call
        expect($browser->script("route('admin.index')"))->toBe(route('admin.index'))
            ->and($browser->script("document.querySelector('#app').__vue_app__.config.globalProperties.route('admin.index')"))->toBe(route('admin.index'));
        $browser->assertNoJavaScriptErrors();
    });
});
