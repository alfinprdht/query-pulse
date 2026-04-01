<?php

namespace Alfinprdht\QueryPulse\Collector;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QueryCollector
{
    private const KEEP_LATEST_PER_URL = 10;

    /**
     * The queries executed.
     * @var array
     */
    private array $queries;

    /**
     * The total time of the queries executed.
     * @var float
     */
    private float $totalQueryTime;

    /**
     * The request object.
     * @var Request
     */
    private Request $request;

    /**
     * Constructor for the QueryCollector class.
     * @param \Illuminate\Http\Request $request The request object.
     */
    public function __construct(Request $request)
    {
        $this->queries = [];
        $this->totalQueryTime = 0;
        $this->request = $request;
    }

    /**
     * Listen to the database queries
     * @return void
     */
    public function listen()
    {
        DB::listen(function ($query) {
            $stack = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
                ->first(function ($frame) {
                    return isset($frame['file']) &&
                        str_contains($frame['file'], base_path('app')) &&
                        !str_contains($frame['file'], 'vendor');
                });
            $trace = null;
            if ($stack) {
                $relativePath = str_replace(base_path() . '/', '', $stack['file']);
                $trace = $relativePath . ':' . $stack['line'];
            }
            $time = $query->time;
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $time,
                'trace' => $trace,
            ];
            $this->totalQueryTime += $time;
        });
    }

    /**
     * Save the queries to the database
     * @return void
     */
    public function save()
    {
        $url = $this->request->method() . ' ' . $this->request->path();

        DB::table('query_pulse')
            ->where('url', $url)
            ->update([
                'query_executed' => ''
            ]);

        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => json_encode($this->queries),
            'total_query_time' => $this->totalQueryTime,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idsToDelete = DB::table('query_pulse')
            ->where('url', '=', $url)
            ->orderByDesc('id')
            ->pluck('id')
            ->skip(self::KEEP_LATEST_PER_URL);

        if ($idsToDelete->isNotEmpty()) {
            DB::table('query_pulse')
                ->whereIn('id', $idsToDelete)
                ->delete();
        }
    }
}
