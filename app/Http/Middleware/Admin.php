<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() === null) {
            return redirect(route('login'));
        }

        if ($request->user()->isAllowed('admin') !== true) {
            abort(403);
        }

        return $next($request);
    }
}
