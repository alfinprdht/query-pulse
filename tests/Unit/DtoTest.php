<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\DTO\AnalysisResult\DetailDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResultDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\IssuesDto;
use Alfinprdht\QueryPulse\DTO\QueryPulseDto;
use PHPUnit\Framework\TestCase;

class DtoTest extends TestCase
{
    public function test_query_pulse_dto_decodes_json(): void
    {
        $payload = json_encode([
            ['sql' => 'select 1', 'bindings_hashed' => 'x', 'time' => 1.0, 'trace' => null],
        ]);

        $dto = new QueryPulseDto('GET x', (string) $payload, '2026-01-01');

        $this->assertSame('GET x', $dto->url);
        $this->assertCount(1, $dto->queryExecuted);
        $this->assertSame('select 1', $dto->queryExecuted[0]['sql']);
    }

    public function test_query_pulse_dto_invalid_json_becomes_empty_array(): void
    {
        $dto = new QueryPulseDto('GET x', '', '2026-01-01');

        $this->assertSame([], $dto->queryExecuted);
    }

    public function test_analysis_result_dto_hydrates_nested_arrays(): void
    {
        $dto = new AnalysisResultDto(
            score: 90,
            status: 'WATCH',
            metrics: [
                'slowQueryTime' => 1,
                'suspiciousWildcardFetch' => 0,
                'duplicateBurst' => 0,
                'probableNPlus1' => 0,
                'totalQueryTime' => 10.0,
                'totalQueryCount' => 3,
            ],
            details: [
                'slowQueryTime' => ['select 1'],
                'suspiciousWildcardFetch' => [],
                'duplicateBurst' => [],
                'probableNPlus1' => [],
            ],
            issues: [
                [
                    'type' => 'slow_query',
                    'count' => 1,
                    'time' => 50.0,
                    'data' => ['sql' => 'select 1', 'trace' => null, 'bindings_hashed' => 'abc'],
                ],
            ],
            lastFetchedAt: '2026-01-01',
        );

        $this->assertInstanceOf(MetricsDto::class, $dto->metrics);
        $this->assertInstanceOf(DetailDto::class, $dto->details);
        $this->assertCount(1, $dto->issues);
        $this->assertInstanceOf(IssuesDto::class, $dto->issues[0]);
    }

    public function test_issues_dto_sets_fingerprint_from_data(): void
    {
        $issue = new IssuesDto(
            type: 'slow_query',
            count: 1,
            time: 10.0,
            data: ['sql' => 'select * from t', 'trace' => 'app/Foo.php:1', 'bindings_hashed' => 'x'],
        );

        $this->assertSame('select * from t', $issue->fingerprint);
        $this->assertSame('app/Foo.php:1', $issue->trace);
        $this->assertNotNull($issue->suggestion);
    }
}
