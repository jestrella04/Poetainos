<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
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
            } catch (QueryException|\Error $th) {
                // Expected before /init runs: the `settings` table may not exist
                // yet, or exist with no `site` row (Setting::first() returns null,
                // and calling ->pluck() on it throws an \Error).
                Log::warning($th);

                $route = $this->app->request->getRequestUri();

                if (! str_starts_with($route, '/init')) {
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
