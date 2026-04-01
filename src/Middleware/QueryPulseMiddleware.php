<?php

namespace Alfinprdht\QueryPulse\Middleware;

use Alfinprdht\QueryPulse\Collector\QueryCollector;
use Closure;

/**
 * The query pulse middleware.
 * @package Alfinprdht\QueryPulse\Middleware
 */
class QueryPulseMiddleware
{
    /**
     * Handle the request.
     * @param \Illuminate\Http\Request $request The request object.
     * @param \Closure $next The next closure.
     * @return mixed The response.
     */
    public function handle($request, \Closure $next)
    {
        if (
            config('query-pulse.enabled') === false
            || $request->is('query-pulse') || $request->is('query-pulse/*')
        ) {
            return $next($request);
        }

        $collector = new QueryCollector($request);
        $collector->listen();

        $response = $next($request);

        $collector->save();

        return $response;
    }
}
