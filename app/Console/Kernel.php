<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('aura:update')->daily();
        $schedule->command('karma:update')->daily();
        $schedule->command('sitemap:generate')->daily();
        $schedule->command('writing:pick-of-the-day')->daily();
        $schedule->command('writing:post-of-the-day')->dailyAt('13:00');
        $schedule->command('author:random')->dailyAt('20:00');
        $schedule->command('category:random')->dailyAt('23:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
