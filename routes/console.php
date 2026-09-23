<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('aura:update')->daily();
Schedule::command('karma:update')->daily();
Schedule::command('sitemap:generate')->daily();
Schedule::command('writing:pick-of-the-day')->daily();

// Posting to X (writing:post-of-the-day, author:random, category:random) was removed in
// September 2026 when the X API moved to paid, pay-per-use credits. To bring it back, restore
// SocialPostNotification and its subclasses, the three commands and their schedule entries
// from commit 478dc92, and reinstall laravel-notification-channels/twitter.
