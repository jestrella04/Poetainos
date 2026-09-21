<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Only admins may pass. Guests are turned away by the `auth` middleware before this one.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAllowed('admin') === true, 403);

        return $next($request);
    }
}
