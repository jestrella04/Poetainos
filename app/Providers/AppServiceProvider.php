<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Resources
        $this->registerResources();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Check the app is not running in CLI mode
        if (! App::runningInConsole()) {
            // Getting App settings from database
            try {
                if (Schema::hasTable('settings')) {
                    $settings = Setting::where('name', 'site')->first()->pluck('data');

                    config([
                        'writerhood' => $settings[0],
                    ]);
                }
            } catch (\Throwable $th) {
                $route = $this->app->request->getRequestUri();

                if (substr($route, 0, 5) !== '/init') {
                    abort(503, 'App not configured');
                }
            }
        }

        // Debugging SQL queries
        /* if (env('APP_DEBUG')) {
            DB::listen(function($sql) {
                Log::info($sql->sql);
                Log::info($sql->bindings);
                Log::info($sql->time);
            });
        } */

        // Use Bootstrap for pagination
        Paginator::useBootstrap();
    }

    /**
     * Register the package resources
     */
    protected function registerResources(): void
    {
        // Loading project's custom language strings
        $this->loadJSONTranslationsFrom(base_path('lang/poetainos'));
    }
}
