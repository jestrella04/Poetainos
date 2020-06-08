<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Getting App settings from database
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::where('name', 'site')->first()->pluck('data');

                config([
                    'writerhood' => $settings[0]
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Debugging SQL queries
        if (env('APP_DEBUG')) {
            DB::listen(function($sql) {
                Log::info($sql->sql);
                Log::info($sql->bindings);
                Log::info($sql->time);
            });
        }
    }
}
