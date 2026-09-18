<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsConfigured
{
    /**
     * Load the site settings into config, or abort with 503 when they don't exist yet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->loadSiteSettings() === false) {
            abort(503, __('The site has not been configured yet.'));
        }

        return $next($request);
    }

    /**
     * Returns whether the settings are available, loading them from the database if needed.
     */
    private function loadSiteSettings(): bool
    {
        if (config('writerhood') !== null) {
            return true;
        }

        try {
            $setting = Setting::where('name', 'site')->first();
        } catch (QueryException $exception) {
            // Expected before the installer runs: the `settings` table may not exist yet.
            Log::warning($exception);

            return false;
        }

        if ($setting === null) {
            Log::warning('Site settings are not configured.');

            return false;
        }

        config(['writerhood' => $setting->data]);

        return true;
    }
}
