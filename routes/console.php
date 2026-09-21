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
Schedule::command('writing:post-of-the-day')->dailyAt('13:00');
Schedule::command('author:random')->dailyAt('20:00');
Schedule::command('category:random')->dailyAt('23:00');
