<?php

namespace Alfinprdht\QueryPulse\Collector;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
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
     * The number of requests to generate a report.
     * @var int
     */

    private int $autoGenerateReportEvery;

    /**
     * Constructor for the QueryCollector class.
     * @param \Illuminate\Http\Request $request The request object.
     */

    public function __construct(Request $request)
    {
        $this->queries = [];
        $this->totalQueryTime = 0;
        $this->request = $request;
        $this->autoGenerateReportEvery = config('query-pulse.auto_generate_report_every');
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

        $this->handleGenerateReport($url);
    }

    /**
     * Handle the generation of the report.
     * @param string $url
     * @return bool
     */
    private function handleGenerateReport(string $url): bool
    {
        if ($this->autoGenerateReportEvery > 0) {

            $lastQueryPulseReport = DB::table('query_pulse_report')
                ->where('url', $this->request->method() . ' ' . $this->request->path())
                ->orderByDesc('id')
                ->first();

            if (empty($lastQueryPulseReport)) {
                $analyzer = new HeuristicsAnalyzer($url);
                $analyzer->analyze();
                return true;
            }

            $queryPulse = DB::table('query_pulse')
                ->where('url', $this->request->method() . ' ' . $this->request->path())
                ->orderByDesc('id')
                ->where('created_at', '>', $lastQueryPulseReport->updated_at)
                ->get();

            if (count($queryPulse) >= $this->autoGenerateReportEvery) {
                $analyzer = new HeuristicsAnalyzer($url);
                $analyzer->analyze();
                return true;
            }
            return false;
        }
        return false;
    }
}
