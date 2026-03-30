<?php

namespace Alfinprdht\QueryPulse\DTO\AnalysisResult;

/**
 * The details of the query.
 * @package Alfinprdht\QueryPulse\DTO\AnalysisResult
 */
class DetailDto
{
    /**
     * Constructor for the DetailDto class.
     * @param array $slowQueryTime The slow query time.
     * @param array $supiciousWildcardFetch The supicious wildcard fetch.
     * @param array $duplicateBurst The duplicate burst.
     * @param array $probableNPlus1 The probable N+1.
     */
    public function __construct(
        public array $slowQueryTime = [],
        public array $supiciousWildcardFetch = [],
        public array $duplicateBurst = [],
        public array $probableNPlus1 = [],
    ) {}
}
