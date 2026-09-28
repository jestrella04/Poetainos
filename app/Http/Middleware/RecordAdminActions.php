<?php

namespace App\Http\Middleware;

use App\Services\SecurityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAdminActions
{
    public function __construct(private SecurityLog $securityLog) {}

    /**
     * Record every change made through the admin panel (anything but a read),
     * with who made it, what it targeted and how it ended.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodSafe() === false) {
            $route = $request->route();

            $this->securityLog->record('Admin action', $request->user(), [
                'action' => $route?->getName(),
                'targets' => array_map(
                    fn (mixed $parameter): mixed => $parameter instanceof Model ? $parameter->getRouteKey() : $parameter,
                    $route?->parameters() ?? [],
                ),
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}
