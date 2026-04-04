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
use Alfinprdht\QueryPulse\DTO\AnalysisResult\IssuesDto;

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
            ->select('query_executed', 'total_query_time', 'url', 'id', 'created_at')
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
        $cleanSql = preg_replace('/(--.*)|(\/\*[\s\S]*?\*\/)/', '', $sql);
        return (bool) preg_match('/select\s+(\*|(\w+\.\*))/i', $cleanSql);
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
    public function analyze(): bool
    {

        $queryPulseReport = DB::table('query_pulse_report')
            ->select('analysis_result', 'updated_at', 'created_at')
            ->where('url', $this->url)
            ->first();

        $firstQueryPulse = $this->queries->first();
        if (empty($firstQueryPulse)) {
            return false;
        }

        /**
         * Prevent re-analyzing if the query pulse report is already up to date.
         * If the query pulse report is not empty and the updated at is greater than the created at, 
         * then set the analysis result from the query pulse report.
         */
        if (
            !empty($queryPulseReport)
        ) {
            if ($queryPulseReport->updated_at >= $firstQueryPulse?->created_at) {

                /**
                 * Handle possible corrupted analysis result.
                 */
                $decodedAnalysisResult = json_decode($queryPulseReport->analysis_result, true);
                if (!empty($decodedAnalysisResult)) {
                    $this->analysisResult = new AnalysisResultDto(...$decodedAnalysisResult);
                    return true;
                }
            }
        }

        $this->averageQueryTime = round(
            $this->queries
                ->avg('total_query_time') ?? 0,
            2
        );

        $latestQueryPulse = new QueryPulseDto(
            $this->url,
            $firstQueryPulse->query_executed ?? '',
            $firstQueryPulse->created_at ?? '',
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
                $issues[] = new IssuesDto(
                    type: 'slow_query',
                    count: 1,
                    time: $query['time'],
                    data: $query,
                );
            }
            if ($this->hasWildcardFetch($query['sql'])) {
                $metrics->suspiciousWildcardFetch += 1;
                $details->suspiciousWildcardFetch[] = $query['sql'];
            }
            $metrics->totalQueryTime += $query['time'];
            $metrics->totalQueryCount += 1;
        }

        $suspiciousWildcardFetchData = collect(
            $latestQueryPulse->queryExecuted
        );

        $suspiciousWildcardFetchData->filter(function ($query) {
            return $this->hasWildcardFetch($query['sql']);
        })->transform(function ($query) {
            $query['unique_id'] = md5($query['sql'] . $query['trace']);
            return $query;
        })->groupBy('unique_id')->each(function ($group) use (&$issues) {
            $firstData = $group->first();
            $issues[] = new IssuesDto(
                type: 'suspicious_wildcard_fetch',
                count: count($group),
                time: $group->sum('time'),
                data: $firstData,
            );
        });

        $completeQuery = [];
        foreach ($latestQueryPulse->queryExecuted as $query) {
            $sqlWithBindings = Str::replaceArray('?', $query['bindings'], $query['sql']);
            $completeQuery[] = [
                'sql_with_bindings' => $sqlWithBindings,
                'sql' => $query['sql'],
                'time' => $query['time'],
                'unique_id_bindings' => md5($sqlWithBindings . $query['trace']),
                'unique_id_fingerprint' => md5($query['sql'] . $query['trace']),
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
                    $firstData = $group->first();
                    $issues[] = new IssuesDto(
                        type: 'duplicate_burst',
                        count: count($group),
                        time: $group->sum('time'),
                        data: $firstData,
                    );
                }
            });

        foreach (
            collect($completeQuery)->groupBy('unique_id_fingerprint') as $queries
        ) {
            if (count($queries) > 1) {
                $countBindingMd5 = count($queries->groupBy('bindings_md5'));
                if (
                    $countBindingMd5 > Thresholds::getProbableNPlus1()
                ) {
                    $metrics->probableNPlus1++;
                    $details->probableNPlus1[] = $queries->first()['sql'];
                    $firstData = $queries->first();
                    $issues[] = new IssuesDto(
                        type: 'probable_n_plus_1',
                        count: $countBindingMd5,
                        time: $queries->sum('time'),
                        data: $firstData,
                    );
                }
            }
        }

        $scoreCalculator = new ScoreCalculator($metrics);


        $this->analysisResult->metrics = $metrics;
        $this->analysisResult->details = $details;
        $this->analysisResult->issues = $issues;
        $this->analysisResult->score = $scoreCalculator->getScore();
        $this->analysisResult->status = $scoreCalculator->getStatus();
        $this->analysisResult->lastFetchedAt = $latestQueryPulse->createdAt;
        DB::table('query_pulse_report')->updateOrInsert([
            'url' => $this->url,
        ], [
            'average_query_time' => $this->averageQueryTime,
            'status' => $this->analysisResult->status,
            'analysis_result' => json_encode($this->analysisResult),
            'updated_at' => now(),
        ]);
        return true;
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
