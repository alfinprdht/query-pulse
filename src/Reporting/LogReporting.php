<?php

namespace Alfinprdht\QueryPulse\Reporting;

use Alfinprdht\QueryPulse\Reporting\Reporting;
use Illuminate\Console\Command;

class LogReporting extends Reporting
{
    public function report(Command $command): void
    {
        $metrics = $this->analysisResult->metrics;
        $headers = [
            'Metrics',
            'Value',
        ];
        $rows = [
            ['Slow Query', $metrics->slowQueryTime],
            ['Duplicate Burst', $metrics->duplicateBurst],
            ['Probable N+1', $metrics->probableNPlus1],
            ['Supicious Wildcard Fetch', $metrics->supiciousWildcardFetch],
            ['Total Query Time', $metrics->totalQueryTime . ' ms'],
            ['Total Query Count', $metrics->totalQueryCount],
        ];
        $command->table($headers, $rows);

        $command->info('Score: ' . $this->analysisResult->score);
        $command->warn('Status: ' . $this->analysisResult->status);
    }
}
