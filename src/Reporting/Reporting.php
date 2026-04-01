<?php

namespace Alfinprdht\QueryPulse\Reporting;

use Alfinprdht\QueryPulse\DTO\AnalysisResultDto;

class Reporting
{
    public function __construct(
        public string $url,
        public AnalysisResultDto $analysisResult,
    ) {}
}
