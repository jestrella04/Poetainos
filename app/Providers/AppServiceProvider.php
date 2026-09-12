<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

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
                    $setting = Setting::where('name', 'site')->first();

                    if ($setting === null) {
                        throw new RuntimeException('Settings not configured');
                    }

                    $settings = $setting->pluck('data');

                    config([
                        'writerhood' => $settings[0],
                    ]);
                }
            } catch (QueryException|RuntimeException $th) {
                // Expected before /init runs: the `settings` table may not exist
                // yet, or exist with no `site` row.
                Log::warning($th);

                $route = request()->getRequestUri();

                if (! str_starts_with($route, '/init')) {
                    abort(503, 'App not configured');
                }
            }
        }

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
