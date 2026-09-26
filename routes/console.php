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

Schedule::command('writing:pick-of-the-day')->dailyAt('04:00');
Schedule::command('aura:update')->dailyAt('04:00');
Schedule::command('karma:update')->dailyAt('04:00');
Schedule::command('sitemap:generate')->dailyAt('05:00');
Schedule::command('cache:prune-expired')->dailyAt('06:00');
Schedule::command('writing:post-of-the-day')->dailyAt('13:00');

// Posting to X (author:random, category:random and the X side of the writing of the day) was
// removed in September 2026 when the X API moved to paid, pay-per-use credits. To bring it back,
// restore SocialPostNotification and its subclasses, the commands and their schedule entries
// from commit 02c9cac^, and reinstall laravel-notification-channels/twitter.
