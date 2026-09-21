<?php

namespace App\Http\Middleware;

use App\Services\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsConfigured
{
    public function __construct(private SiteSettings $siteSettings) {}

    /**
     * Abort with 503 when the site settings don't exist yet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->siteSettings->load() === false) {
            logger()->warning('Site settings are not configured.');

            abort(503, __('The site has not been configured yet.'));
        }

        return $next($request);
    }
}
