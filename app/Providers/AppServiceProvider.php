<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
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

        $this->configureRateLimiting();

        // Every form that sets a password (registration, reset) applies the same policy
        Password::defaults(fn (): Password => Password::min(8)->rules(['regex:'.User::PASSWORD_PATTERN]));
    }

    /**
     * Register the package resources
     */
    protected function registerResources(): void
    {
        // Loading project's custom language strings
        $this->loadJSONTranslationsFrom(base_path('lang/poetainos'));
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by((string) ($request->user()->id ?? $request->ip()));
        });

        // The email-first login has to say whether an address has an account;
        // these limits keep that answer from being harvested in bulk
        // Anyone may report content, so cap how much one visitor can file
        RateLimiter::for('complaints', fn (Request $request): array => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perDay(30)->by('daily|'.$request->ip()),
        ]);

        RateLimiter::for('email-check', fn (Request $request): array => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perDay(50)->by('daily|'.$request->ip()),
        ]);
    }
}
