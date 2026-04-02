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
     * @param string $createdAt
     */
    public function __construct(
        public string $url,
        public string $queryExecutedString,
        public string $createdAt,
    ) {
        $this->queryExecuted = json_decode($queryExecutedString, true);
    }
}
