<?php

namespace Alfinprdht\QueryPulse\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class QueryPulseMiddleware
{
    public function handle($request, Closure $next)
    {
        $queries = [];
        $totalQueryTime = 0;
        DB::listen(function ($query) use (&$queries, &$totalQueryTime) {
            $time = $query->time;
            $queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $time
            ];
            $totalQueryTime += $time;
        });

        $response = $next($request);

        $data = [
            'url' => $request->method() . ' ' . $request->path(),
            'query_executed' => json_encode($queries),
            'total_query_time' => $totalQueryTime,
        ];
        DB::table('query_pulse')->insert($data);

        return $response;
    }
}
