<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Setting;
use Illuminate\Support\Facades\App;
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
        //Resources
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
                        'writerhood' => $settings[0]
                    ]);
                }
            } catch (\Throwable $th) {
                $route = $this->app->request->getRequestUri();

                if ('/init' !== substr($route, 0, 5)) {
                    abort(403);
                }
            }
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

    /**
     * Register the package resources
     *
     * @return void
     */
    protected function registerResources(): void
    {
        // Loading project's custom language strings
        $this->loadJSONTranslationsFrom(resource_path('lang/Writerhood'));
    }
}
