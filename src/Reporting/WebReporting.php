<?php

namespace Alfinprdht\QueryPulse\Reporting;

use Alfinprdht\QueryPulse\Reporting\Reporting;
use Illuminate\Support\Collection;

class WebReporting extends Reporting
{

    protected const ANOMALY_TYPES = [
        'slow_query' => 'Slow Query',
        'duplicate_burst' => 'Duplicate Burst',
        'probable_n_plus_1' => 'Probable N+1',
        'suspicious_wildcard_fetch' => 'Suspicious Wildcard Fetch',
    ];

    /**
     * Get the anomalies for the issues.
     * @param Collection $issues
     * @return array{count: int, description: string, title: string, type: string}
     */
    protected function getAnomalies(Collection $issues)
    {

        $count = $issues->sum('count');
        $type = $issues->first()->type;

        switch ($type) {
            case 'slow_query':
                $description = $issues->pluck('fingerprint')->implode("\n\n");
                break;
            case 'duplicate_burst':
                $description = 'Identical query checksum detected ' . $count . ' times in ' . $issues->sum('time') . 'ms window.';
                break;
            case 'probable_n_plus_1':
                $description = 'Probable N+1 detected in ' . $count . ' queries.';
                break;
            case 'suspicious_wildcard_fetch':
                $tables = $issues->transform(function ($item) {
                    $fingerprints = explode('`', $item->fingerprint);
                    return count($fingerprints) > 1 ? $fingerprints[1] : null;
                })->filter(fn($table) => $table !== null)
                    ->unique();
                if ($tables->count() < 5) {
                    $description = 'Suspicious wildcard fetch detected in ' . $tables->implode(', ') . ' tables. This may cause performance issues and should be investigated.';
                } else {
                    $description = 'Suspicious wildcard fetch detected in ' . $tables->take(5)->implode(', ') . ', and more tables. This may cause performance issues and should be investigated.';
                }
                break;
            default:
                $description = 'Unknown anomaly detected in ' . $count . ' queries. Please investigate.';
                break;
        }

        return [
            'title' => self::ANOMALY_TYPES[$type] ?? 'Unknown',
            'type' => $type,
            'count' => $count,
            'description' => $description,
        ];
    }

    /**
     * Get the result of the analysis.
     * @return array{score: float, status: string, metrics: array, anomalies: array, issues: array}
     */
    public function result()
    {
        $anomalies = collect($this->analysisResult->issues);
        $anomalies = $anomalies
            ->groupBy('type')
            ->transform(function ($issue) {
                return $this->getAnomalies($issue);
            });

        $issues = collect($this->analysisResult->issues)
            ->groupBy('unique_id')->transform(function ($group) {
                $firstData = $group->first();
                return [
                    'fingerprint' => $firstData->fingerprint,
                    'type' => $group->pluck('type')->unique()->toArray(),
                    'count' => $group->count(),
                    'time' => $firstData->time,
                    'suggestion' => $group->pluck('suggestion')->unique()->toArray(),
                    'trace' => $firstData->trace,
                ];
            })
            ->values()
            ->toArray();

        return [
            'score' => $this->analysisResult->score,
            'status' => $this->analysisResult->status,
            ...collect($this->analysisResult->metrics)->toArray(),
            'anomalies' => $anomalies,
            'issues' => $issues,
        ];
    }
}
