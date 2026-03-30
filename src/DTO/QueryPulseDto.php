<?php

namespace Alfinprdht\QueryPulse\DTO;

/**
 * The query pulse DTO.
 * @package Alfinprdht\QueryPulse\DTO
 */
class QueryPulseDto
{
    public array $queryExecuted;
    /**
     * Summary of __construct
     * @param string $url
     * @param string $queryExecuted
     */
    public function __construct(
        public string $url,
        public string $queryExecutedString,
    ) {
        $this->queryExecuted = json_decode($queryExecutedString, true);
    }
}
