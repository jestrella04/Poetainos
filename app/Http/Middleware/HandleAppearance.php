<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['light', 'dark', 'system'];
        $appearance = $request->cookie('appearance');
        View::share('appearance', in_array($appearance, $allowed, true) === true ? $appearance : 'system');

        return $next($request);
    }
}
