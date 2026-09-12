<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * Policies are auto-discovered via the {Model}Policy naming convention;
     * this array is intentionally left empty.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return auth()->user()?->isAllowed('admin') ?? false;
        });
    }
}
