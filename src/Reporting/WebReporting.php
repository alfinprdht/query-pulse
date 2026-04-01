<?php

namespace Alfinprdht\QueryPulse\Reporting;

use Alfinprdht\QueryPulse\Reporting\Reporting;
use Illuminate\Console\Command;

class WebReporting extends Reporting
{

    protected const ANOMALY_TYPES = [
        'slow_query' => 'Slow Query',
        'duplicate_burst' => 'Duplicate Burst',
        'probable_n_plus_1' => 'Probable N+1',
        'supicious_wildcard_fetch' => 'Supicious Wildcard Fetch',
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
            } elseif ($type == 'supicious_wildcard_fetch') {
                $tables = $issue->transform(function ($item) {
                    return explode('`', $item['fingerprint'])[1];
                })->unique();

                if ($tables->count() < 5) {
                    $description = 'Supicious wildcard fetch detected in ' . $tables->implode(', ') . ' tables. This may cause performance issues and should be investigated.';
                } else {
                    $description = 'Supicious wildcard fetch detected in ' . $tables->take(5)->implode(', ') . ', and more tables. This may cause performance issues and should be investigated.';
                }
            }

            return [
                'title' => self::ANOMALY_TYPES[$type],
                'type' => $type,
                'count' => $count,
                'description' => $description,
            ];
        });

        return [
            'score' => $this->analysisResult->score,
            'status' => $this->analysisResult->status,
            ...collect($this->analysisResult->metrics)->toArray(),
            'anomalies' => $anomalies,
            'issues' => $this->analysisResult->issues,
        ];
    }
}
