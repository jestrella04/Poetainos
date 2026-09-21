<?php

namespace App\Providers;

use App\Services\SiteSettings;
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
     */
    public function boot(): void
    {
        // Console commands and queue workers never pass through EnsureSiteIsConfigured
        $this->app->make(SiteSettings::class)->load();
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
