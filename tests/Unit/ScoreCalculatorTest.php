<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\Analysis\ScoreCalculator;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\Tests\TestCase;

class ScoreCalculatorTest extends TestCase
{
    public function test_healthy_score_when_no_penalties(): void
    {
        $calc = new ScoreCalculator(new MetricsDto());

        $this->assertSame(100, $calc->getScore());
        $this->assertSame('HEALTHY', $calc->getStatus());
    }

    public function test_slow_query_penalty(): void
    {
        $calc = new ScoreCalculator(new MetricsDto(slowQueryTime: 2));

        $this->assertSame(80, $calc->getScore());
        $this->assertSame('WATCH', $calc->getStatus());
    }

    public function test_duplicate_burst_and_probable_n_plus_1_penalty(): void
    {
        $calc = new ScoreCalculator(new MetricsDto(
            duplicateBurst: 1,
            probableNPlus1: 1,
        ));

        $this->assertSame(80, $calc->getScore());
        $this->assertSame('WATCH', $calc->getStatus());
    }

    public function test_total_query_time_and_count_penalties_use_thresholds(): void
    {
        $calc = new ScoreCalculator(new MetricsDto(
            totalQueryTime: 400,
            totalQueryCount: 80,
        ));

        $this->assertSame(80, $calc->getScore());
    }

    public function test_score_clamps_to_zero(): void
    {
        $calc = new ScoreCalculator(new MetricsDto(
            slowQueryTime: 20,
            duplicateBurst: 1,
            probableNPlus1: 1,
            totalQueryTime: 400,
            totalQueryCount: 200,
        ));

        $this->assertSame(0, $calc->getScore());
        $this->assertSame('CRITICAL', $calc->getStatus());
    }

    public function test_critical_poor_watch_boundaries(): void
    {
        $this->assertSame('CRITICAL', (new ScoreCalculator(new MetricsDto(slowQueryTime: 7)))->getStatus());
        $this->assertSame('POOR', (new ScoreCalculator(new MetricsDto(slowQueryTime: 4)))->getStatus());
        $this->assertSame('WATCH', (new ScoreCalculator(new MetricsDto(slowQueryTime: 2)))->getStatus());
    }
}
