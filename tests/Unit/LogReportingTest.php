<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\DTO\AnalysisResultDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\Reporting\LogReporting;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Console\Command;

class LogReportingTest extends TestCase
{
    public function test_report_outputs_table_score_and_link(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->exactly(2))->method('info')->with($this->isType('string'));
        $command->expects($this->once())->method('table')->with(
            $this->equalTo(['Metrics', 'Value']),
            $this->isType('array'),
        );
        $command->expects($this->once())->method('warn')->with($this->stringContains('WATCH'));
        $command->expects($this->once())->method('line')->with($this->stringContains('/query-pulse/report/'));

        $reporting = new LogReporting(
            'GET api/ping',
            new AnalysisResultDto(
                score: 88,
                status: 'WATCH',
                metrics: new MetricsDto(
                    slowQueryTime: 0,
                    suspiciousWildcardFetch: 0,
                    duplicateBurst: 0,
                    probableNPlus1: 0,
                    totalQueryTime: 12.5,
                    totalQueryCount: 2,
                ),
            ),
        );

        $reporting->report($command);
    }
}
