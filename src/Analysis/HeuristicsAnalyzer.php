<?php

namespace Alfinprdht\QueryPulse\Analysis;

use Illuminate\Support\Facades\DB;
use Alfinprdht\QueryPulse\DTO\QueryPulseDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResultDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\DetailDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Alfinprdht\QueryPulse\Support\Thresholds;
use Alfinprdht\QueryPulse\Analysis\ScoreCalculator;

class HeuristicsAnalyzer
{
    public string $url;

    public float $averageQueryTime;

    private Collection $queries;

    private AnalysisResultDto $analysisResult;

    /**
     * Constructor for the HeuristicsAnalyzer class.
     * @param string $url The URL to analyze.
     */
    public function __construct(string $url = '')
    {
        $this->url = $url;
        $this->averageQueryTime = 0;
        $this->queries = DB::table('query_pulse')
            ->select('query_executed', 'total_query_time', 'url', 'id')
            ->where('url', $url)
            ->where('total_query_time', '>', 0)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        $this->analysisResult = new AnalysisResultDto(
            score: 0,
            status: '',
            metrics: new MetricsDto(),
            details: new DetailDto(),
            issues: [],
        );
    }

    /**
     * Check if the query contains a wildcard fetch.
     * @param string $sql The query to check.
     * @return bool True if the query contains a wildcard fetch, false otherwise.
     */
    protected function hasWildcardFetch(string $sql): bool
    {
        return strpos($sql, '*') !== false;
    }

    /**
     * Replace the bindings in the query with the actual values.
     * @param array $query
     * @return string The query with the bindings replaced.
     */
    protected function queryWithBindings(array $query): string
    {
        return Str::replaceArray('?', $query['bindings'], $query['sql']);
    }

    /**
     * Analyze the queries and set the analysis result.
     */
    public function analyze(): void
    {
        $this->averageQueryTime = round(
            $this->queries
                ->avg('total_query_time'),
            2
        );

        $latestQueryPulse = new QueryPulseDto(
            $this->url,
            $this->queries->first()->query_executed ?? '',
        );

        $metrics = new MetricsDto();
        $details = new DetailDto();
        $issues = [];

        foreach ($latestQueryPulse->queryExecuted as $query) {
            if (
                $query['time'] > Thresholds::getSlowQueryTime()
            ) {
                $metrics->slowQueryTime += 1;
                $details->slowQueryTime[] = $query['sql'];
                $issues[] = [
                    'type' => 'slow_query',
                    'fingerprint' => $query['sql'],
                    'count' => 1,
                    'time' => $query['time'],
                    'suggestion' => 'Review filters, joins, selected columns, and indexes.',
                    'trace' => $query['trace'],
                ];
            }
            if ($this->hasWildcardFetch($query['sql'])) {
                $metrics->supiciousWildcardFetch += 1;
                $details->supiciousWildcardFetch[] = $query['sql'];
            }
            $metrics->totalQueryTime += $query['time'];
            $metrics->totalQueryCount += 1;
        }

        $supiciousWildcardFetchData = collect(
            $latestQueryPulse->queryExecuted
        );

        $supiciousWildcardFetchData->filter(function ($query) {
            return $this->hasWildcardFetch($query['sql']);
        })->transform(function ($query) {
            $query['unique_id'] = md5(json_encode($query['sql'] . $query['trace']));
            return $query;
        })->groupBy('unique_id')->each(function ($group) use (&$issues) {
            $issues[] = [
                'type' => 'supicious_wildcard_fetch',
                'fingerprint' => $group->first()['sql'],
                'count' => count($group),
                'time' => $group->sum('time'),
                'suggestion' => 'Avoid using wildcard fetches in queries',
                'trace' => $group->first()['trace'],
            ];
        });

        $completeQuery = [];
        foreach ($latestQueryPulse->queryExecuted as $query) {
            $sqlWithBindings = Str::replaceArray('?', $query['bindings'], $query['sql']);
            $completeQuery[] = [
                'sql_with_bindings' => $sqlWithBindings,
                'sql' => $query['sql'],
                'time' => $query['time'],
                'unique_id_bindings' => md5(json_encode($sqlWithBindings . $query['trace'])),
                'unique_id_fingerprint' => md5(json_encode($query['sql'] . $query['trace'])),
                'trace' => $query['trace'],
                'bindings' => $query['bindings'],
                'bindings_md5' => md5(json_encode($query['bindings'])),
            ];
        }

        collect($completeQuery)->groupBy('unique_id_bindings')
            ->each(function ($group) use (&$metrics, &$details, &$issues) {
                if (
                    count($group) > Thresholds::getDuplicateBurst()
                ) {
                    $metrics->duplicateBurst++;
                    $details->duplicateBurst[] = $group->first()['sql'] ?? '';
                    $issues[] = [
                        'type' => 'duplicate_burst',
                        'fingerprint' => $group->first()['sql'] ?? '',
                        'count' => count($group),
                        'time' => $group->sum('time'),
                        'suggestion' => 'Avoid repeated lookup queries inside loops or transformers',
                        'trace' => $group->first()['trace'],
                    ];
                }
            });

        foreach (
            collect($completeQuery)->groupBy('unique_id_fingerprint') as $queries
        ) {
            if (count($queries) > 1) {
                $bindingMd5 = count($queries->groupBy('bindings_md5'));
                if (
                    $bindingMd5 > Thresholds::getProbableNPlus1()
                ) {
                    $metrics->probableNPlus1++;
                    $details->probableNPlus1[] = $queries->first()['sql'];
                    $issues[] = [
                        'type' => 'probable_n_plus_1',
                        'fingerprint' => $queries->first()['sql'],
                        'count' => $bindingMd5,
                        'time' => $queries->sum('time'),
                        'suggestion' => 'Use eager loading via with() on the parent query',
                        'trace' => $queries->first()['trace'],
                    ];
                }
            }
        }

        $scoreCalculator = new ScoreCalculator($metrics);


        $this->analysisResult->metrics = $metrics;
        $this->analysisResult->details = $details;
        $this->analysisResult->issues = $issues;
        $this->analysisResult->score = $scoreCalculator->getScore();
        $this->analysisResult->status = $scoreCalculator->getStatus();

        DB::table('query_pulse_report')->updateOrInsert([
            'url' => $this->url,
        ], [
            'average_query_time' => $this->averageQueryTime,
            'status' => $this->analysisResult->status,
            'analysis_result' => json_encode($this->analysisResult),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get the analysis result.
     * @return AnalysisResultDto The analysis result.
     */
    public function getAnalysisResult(): AnalysisResultDto
    {
        return $this->analysisResult;
    }

    public function getQueries(): Collection
    {
        return $this->queries;
    }
}
