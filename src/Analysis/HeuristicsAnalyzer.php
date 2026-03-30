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
    private string $url;

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
            ->where('url', $url)
            ->orderBy('id', 'desc')
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
        $this->averageQueryTime = $this->queries->avg('total_query_time');

        $latestQueryPulse = new QueryPulseDto(
            $this->url,
            $this->queries->first()->query_executed ?? '',
        );

        $metrics = new MetricsDto();
        $details = new DetailDto();

        foreach ($latestQueryPulse->queryExecuted as $query) {
            if (
                $query['time'] > Thresholds::getSlowQueryTime()
            ) {
                $metrics->slowQueryTime += 1;
                $details->slowQueryTime[] = $query['sql'];
            }
            if ($this->hasWildcardFetch($query['sql'])) {
                $metrics->supiciousWildcardFetch += 1;
                $details->supiciousWildcardFetch[] = $query['sql'];
            }
            $metrics->totalQueryTime += $query['time'];
            $metrics->totalQueryCount += 1;
        }

        $issues = [];

        $supiciousWildcardFetchData = collect(
            $latestQueryPulse->queryExecuted
        );

        $supiciousWildcardFetchData->filter(function ($query) {
            return $this->hasWildcardFetch($query['sql']);
        })->groupBy('sql')->each(function ($group) use (&$issues) {
            $issues[] = [
                'type' => 'supicious_wildcard_fetch',
                'fingerprint' => $group->first()['sql'],
                'count' => count($group),
                'suggestion' => '',
            ];
        });


        $completeQuery = [];
        foreach ($latestQueryPulse->queryExecuted as $query) {
            $completeQuery[] = Str::replaceArray('?', $query['bindings'], $query['sql']);
        }

        collect($completeQuery)->groupBy(function ($item) {
            return $item;
        })->each(function ($group) use (&$metrics, &$details, &$issues) {
            if (
                count($group) > Thresholds::getDuplicateBurst()
            ) {
                $metrics->duplicateBurst++;
                $details->duplicateBurst[] = $group->first();
                $issues[] = [
                    'type' => 'duplicate_burst',
                    'fingerprint' => $group->first(),
                    'count' => count($group),
                    'suggestion' => '',
                ];
            }
        });


        foreach (
            collect($latestQueryPulse->queryExecuted)->groupBy('sql') as $queries
        ) {
            if (count($queries) > 1) {
                $clonedQuery = clone $queries;
                $bindingMd5 = $clonedQuery->transform(function ($query) {
                    return md5(json_encode($query['bindings']));
                })->unique()->count();
                if (
                    $bindingMd5 > Thresholds::getProbableNPlus1()
                ) {
                    $metrics->probableNPlus1++;
                    $details->probableNPlus1[] = $queries->first()['sql'];
                    $issues[] = [
                        'type' => 'probable_n_plus_1',
                        'fingerprint' => $queries->first()['sql'],
                        'count' => $bindingMd5,
                        'suggestion' => '',
                    ];
                }
            }
        }

        $scoreCalculator = new ScoreCalculator($metrics);

        $this->analysisResult->metrics = $metrics;
        $this->analysisResult->details = $details;
        $this->analysisResult->score = $scoreCalculator->getScore();
        $this->analysisResult->status = $scoreCalculator->getStatus();
    }

    /**
     * Get the analysis result.
     * @return AnalysisResultDto The analysis result.
     */
    public function getAnalysisResult(): AnalysisResultDto
    {
        return $this->analysisResult;
    }
}
