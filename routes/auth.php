<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::whereIn('service', ['google'])->group(function (): void {
        Route::get('/login/{service}', [SocialAuthController::class, 'redirectToProvider'])->name('social.login');
        Route::get('/login/{service}/callback', [SocialAuthController::class, 'handleProviderCallback']);

        Route::get('/login/{service}/confirm', [SocialAuthController::class, 'showLinkConfirmation'])
            ->name('social.confirm');

        Route::post('/login/{service}/confirm', [SocialAuthController::class, 'confirmProviderLink'])
            ->middleware('throttle:6,1')
            ->name('social.confirm.verify');

        Route::post('/login/{service}/confirm/resend', [SocialAuthController::class, 'resendLinkCode'])
            ->middleware('throttle:6,1')
            ->name('social.confirm.resend');
    });

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::post('email', [AuthenticatedSessionController::class, 'check'])
        ->middleware('throttle:10,1')
        ->name('email.check');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('verify-email', VerifyEmailController::class)
        ->middleware('throttle:6,1')
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->name('password.confirmer');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
