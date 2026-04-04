<?php

namespace Alfinprdht\QueryPulse\DTO\AnalysisResult;

/**
 * The metrics of the query.
 * @package Alfinprdht\QueryPulse\DTO\AnalysisResult
 */
class MetricsDto
{
    public function __construct(
        public int $slowQueryTime = 0,
        public int $suspiciousWildcardFetch = 0,
        public int $duplicateBurst = 0,
        public int $probableNPlus1 = 0,
        public float $totalQueryTime = 0,
        public int $totalQueryCount = 0,
    ) {}
}
