<?php

namespace Alfinprdht\QueryPulse\Reporting;

use Alfinprdht\QueryPulse\Reporting\Reporting;

class WebReporting extends Reporting
{

    protected const ANOMALY_TYPES = [
        'slow_query' => 'Slow Query',
        'duplicate_burst' => 'Duplicate Burst',
        'probable_n_plus_1' => 'Probable N+1',
        'suspicious_wildcard_fetch' => 'suspicious Wildcard Fetch',
    ];
    public function result()
    {
        $anomalies = collect($this->analysisResult->issues);
        $anomalies = $anomalies->groupBy('type')->transform(function ($issue) {
            $count = $issue->sum('count');
            $type = $issue->first()['type'];

            if ($type == 'slow_query') {

                $description = $issue->pluck('fingerprint')->implode("\n\n");
            } elseif ($type == 'duplicate_burst') {

                $description = 'Identical query checksum detected ' . $count . ' times in ' . $issue->sum('time') . 'ms window.';
            } elseif ($type == 'probable_n_plus_1') {

                $description = 'Probable N+1 detected in ' . $count . ' queries.';
            } elseif ($type == 'suspicious_wildcard_fetch') {
                $tables = $issue->transform(function ($item) {
                    $fingerprints = explode('`', $item['fingerprint']);
                    return count($fingerprints) > 1 ? $fingerprints[1] : null;
                })->filter(fn($table) => $table !== null)
                    ->unique();

                if ($tables->count() < 5) {
                    $description = 'suspicious wildcard fetch detected in ' . $tables->implode(', ') . ' tables. This may cause performance issues and should be investigated.';
                } else {
                    $description = 'suspicious wildcard fetch detected in ' . $tables->take(5)->implode(', ') . ', and more tables. This may cause performance issues and should be investigated.';
                }
            } else {
                $description = 'Unknown anomaly detected in ' . $count . ' queries. Please investigate.';
            }

            return [
                'title' => self::ANOMALY_TYPES[$type],
                'type' => $type,
                'count' => $count,
                'description' => $description,
            ];
        });

        $issues = collect($this->analysisResult->issues)
            ->groupBy('unique_id')->transform(function ($group) {
                $firstData = $group->first();
                return [
                    'fingerprint' => $firstData['fingerprint'],
                    'type' => $group->pluck('type')->unique()->toArray(),
                    'count' => $group->count(),
                    'time' => $firstData['time'],
                    'suggestion' => $group->pluck('suggestion')->unique()->toArray(),
                    'trace' => $firstData['trace'],
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
