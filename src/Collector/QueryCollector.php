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
     * The limit of the stack trace depth.
     * @var int
     */
    private int $limitStackTraceDepth = 20;

    /**
     * The URL of the request.
     * @var string
     */
    private string $url;

    /**
     * The flag to enable the stack trace.
     * @var bool
     */
    private bool $isEnabledUrlStackTrace;

    /**
     * Constructor for the QueryCollector class.
     * @param \Illuminate\Http\Request $request The request object.
     */

    public function __construct(
        Request $request,
        bool $isEnabledUrlStackTrace = false
    ) {
        $this->request = $request;
        $this->isEnabledUrlStackTrace = $isEnabledUrlStackTrace;
        $this->queries = [];
        $this->totalQueryTime = 0;
        $this->autoGenerateReportEvery = config('query-pulse.auto_generate_report_every');
        $this->url = $this->request->method() . ' ' . $this->request->path();
    }

    /**
     * Listen to the database queries
     * @return void
     */
    public function listen()
    {
        DB::listen(function ($query) {
            $trace = null;
            if ($this->isEnabledUrlStackTrace) {
                $stack = collect(
                    debug_backtrace(
                        DEBUG_BACKTRACE_IGNORE_ARGS,
                        $this->limitStackTraceDepth
                    )
                )
                    ->skip(2)
                    ->first(function ($frame) {
                        return isset($frame['file']) &&
                            str_contains($frame['file'], base_path('app')) &&
                            !str_contains($frame['file'], 'vendor');
                    });
                if ($stack) {
                    $relativePath = str_replace(base_path() . '/', '', $stack['file']);
                    $trace = $relativePath . ':' . $stack['line'];
                }
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
        /**
         * Update the query pulse record with the empty query executed and the updated at to now.
         * Query_executed consume a lot of space in the database, 
         * so we need to update it to empty string and the updated at to now.
         */

        DB::table('query_pulse')
            ->where('url', $this->url)
            ->limit(self::KEEP_LATEST_PER_URL)
            ->orderByDesc('id')
            ->update([
                'query_executed' => '',
                'updated_at' => now(),
            ]);

        /**
         * Because, we don't snapshot the queries executed and we just record total query time
         * On the similar URL of KEEP_LATEST_PER_URL, only one latest record will filled with the queries executed.
         */
        DB::table('query_pulse')->insert([
            'url' => $this->url,
            'query_executed' => json_encode($this->queries),
            'total_query_time' => $this->totalQueryTime,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idsToDelete = DB::table('query_pulse')
            ->where('url', '=', $this->url)
            ->orderByDesc('id')
            ->pluck('id')
            ->skip(self::KEEP_LATEST_PER_URL);

        if ($idsToDelete->isNotEmpty()) {
            DB::table('query_pulse')
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        $this->handleGenerateReport(
            $this->url
        );
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
                ->where('url', $this->url)
                ->orderByDesc('id')
                ->first();

            if (empty($lastQueryPulseReport)) {
                $analyzer = new HeuristicsAnalyzer($url);
                $analyzer->analyze();
                return true;
            }

            $queryPulse = DB::table('query_pulse')
                ->where('url', $this->url)
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
