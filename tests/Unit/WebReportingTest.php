<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\DTO\AnalysisResultDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\Reporting\WebReporting;
use PHPUnit\Framework\TestCase;

class WebReportingTest extends TestCase
{
    public function test_result_groups_anomalies_and_issues(): void
    {
        $first = ['sql' => 'select 1', 'trace' => null, 'bindings_hashed' => 'b1'];
        $second = ['sql' => 'select 2', 'trace' => null, 'bindings_hashed' => 'b2'];

        $reporting = new WebReporting('GET feed', new AnalysisResultDto(
            score: 70,
            status: 'POOR',
            metrics: new MetricsDto(),
            issues: [
                [
                    'type' => 'slow_query',
                    'count' => 1,
                    'time' => 120.0,
                    'data' => $first,
                ],
                [
                    'type' => 'slow_query',
                    'count' => 1,
                    'time' => 80.0,
                    'data' => $second,
                ],
                [
                    'type' => 'duplicate_burst',
                    'count' => 3,
                    'time' => 9.0,
                    'data' => $first,
                ],
            ],
        ));

        $result = $reporting->result();

        $this->assertSame(70.0, $result['score']);
        $this->assertSame('POOR', $result['status']);
        $this->assertArrayHasKey('slow_query', $result['anomalies']);
        $this->assertArrayHasKey('duplicate_burst', $result['anomalies']);
        $this->assertSame(2, $result['anomalies']['slow_query']['count']);
        $this->assertSame(3, $result['anomalies']['duplicate_burst']['count']);
        $this->assertNotEmpty($result['issues']);
    }
}
