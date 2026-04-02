<?php

namespace Alfinprdht\QueryPulse\DTO;

use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\DTO\AnalysisResult\DetailDto;

/**
 * The analysis result of the query.
 * @package Alfinprdht\QueryPulse\DTO
 */
class AnalysisResultDto
{
    public function __construct(
        public float $score = 0,
        public string $status = '',
        public MetricsDto $metrics,
        public DetailDto $details,
        public array $issues = [],
        public string $lastFetchedAt = '',
    ) {}
}
