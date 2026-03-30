<?php

namespace Alfinprdht\QueryPulse\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryPulseReportCommand extends Command
{
    protected $signature = 'query-pulse:url {url}';
    protected $description = 'Generate a report on specific URL';

    public function handle()
    {
        $url = $this->argument('url');
        $query = DB::table('query_pulse')->where('url', $url)->orderBy('id', 'desc')->get();
        if ($query->isEmpty()) {
            $this->error('No data found for URL: ' . $url);
            return;
        }

        $queryExecuted = collect(json_decode($query->first()->query_executed, true));
        $this->info('[Query Pulse]');
        $this->info('URL: ' . $url);
        $this->info('--------------------------------------------------');
        $this->info('Average query time: ' . $query->avg('total_query_time') . ' ms');

        $slowQueryTime = 0;
        $duplicateBurst = 0;
        $probableNPlus1 = 0;
        $totalQueryTime = 0;
        $totalQueryCount = 0;
        $supiciousWildcardFetch = 0;

        foreach ($queryExecuted as $query) {
            if ($query['time'] > config('query-pulse.rules.slow_query_time')) {
                $slowQueryTime += 1;
            }
            if (strpos($query['sql'], '*') !== false) {
                $supiciousWildcardFetch += 1;
            }
            $totalQueryTime += $query['time'];
            $totalQueryCount++;
        }

        $issues = [];

        $supiciousWildcardFetchIssues = clone $queryExecuted;

        $supiciousWildcardFetchIssues->filter(function ($query) {
            return strpos($query['sql'], '*') !== false;
        })->groupBy('sql')->each(function ($group) use (&$issues) {
            $issues[] = [
                'type' => 'supicious_wildcard_fetch',
                'fingerprint' => $group->first()['sql'],
                'count' => count($group),
                'suggestion' => 'Try to use pagination or limit the result',
            ];
        });

        $completeQuery = [];
        foreach ($queryExecuted as $query) {
            $completeQuery[] = Str::replaceArray('?', $query['bindings'], $query['sql']);
        }

        collect($completeQuery)->groupBy(function ($item) {
            return $item;
        })->each(function ($group) use (&$duplicateBurst, &$issues) {
            if (count($group) > config('query-pulse.rules.duplicate_burst')) {
                $duplicateBurst++;
                $issues[] = [
                    'type' => 'duplicate_burst',
                    'fingerprint' => $group->first(),
                    'count' => count($group),
                    'suggestion' => 'Try to use pagination or limit the result',
                ];
            }
        });


        foreach ($queryExecuted->groupBy('sql') as $queries) {
            if (count($queries) > 1) {
                $clonedQuery = clone $queries;
                $bindingMd5 = $clonedQuery->transform(function ($query) {
                    return md5(json_encode($query['bindings']));
                })->unique()->count();
                if ($bindingMd5 > config('query-pulse.rules.probable_n_plus_1')) {
                    $probableNPlus1++;
                    $issues[] = [
                        'type' => 'probable_n_plus_1',
                        'fingerprint' => $queries->first()['sql'],
                        'count' => $bindingMd5,
                        'suggestion' => 'Try to use pagination or limit the result',
                    ];
                }
            }
        }

        $this->info('Slow Query: ' . $slowQueryTime);
        $this->info('Duplicate Burst: ' . $duplicateBurst);
        $this->info('Probable N+1: ' . $probableNPlus1);
        $this->info('Supicious Wildcard Fetch: ' . $supiciousWildcardFetch);
        $this->info('Total Query Time: ' . $totalQueryTime . ' ms');
        $this->info('Total Query Count: ' . $totalQueryCount);

        $score = 100;
        if ($slowQueryTime >= 0) {
            $score -= 10 * $slowQueryTime;
        }
        if ($duplicateBurst > 0) {
            $score -= 10;
        }
        if ($supiciousWildcardFetch > 0) {
            $score -= $supiciousWildcardFetch / 5;
        }
        if ($probableNPlus1 > 0) {
            $score -= 10;
        }
        if ($totalQueryTime > config('query-pulse.rules.total_query_time')) {
            $score -= 10;
        }
        if ($totalQueryCount > config('query-pulse.rules.total_query_count')) {
            $score -= 10;
        }

        $scoreCategory = 'CRITICAL';
        if ($score <= 39) {
            $scoreCategory = 'CRITICAL';
        } elseif ($score <= 69) {
            $scoreCategory = 'POOR';
        } elseif ($score <= 89) {
            $scoreCategory = 'WATCH';
        } elseif ($score <= 99) {
            $scoreCategory = 'HEALTHY';
        }

        $this->info('Score: ' . $score . ' (' . $scoreCategory . ')');
    }
}
