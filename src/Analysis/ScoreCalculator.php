<?php

namespace Alfinprdht\QueryPulse\Analysis;

use Alfinprdht\QueryPulse\DTO\AnalysisResult\MetricsDto;
use Alfinprdht\QueryPulse\Support\Thresholds;

class ScoreCalculator
{

    protected int $score = 100;

    protected const SCORE_CRITICAL = 39;

    protected const SCORE_POOR = 69;

    protected const SCORE_WATCH = 89;


    /**
     * Constructor for the ScoreCalculator class.
     * @param MetricsDto $metrics The metrics of the query.
     */
    public function __construct(
        public MetricsDto $metrics,
    ) {
        $this->calculate();
    }

    /**
     * Calculate the score of the query.
     */
    protected function calculate()
    {
        if ($this->metrics->slowQueryTime > 0) {
            $this->score -= 10 * $this->metrics->slowQueryTime;
        }
        if ($this->metrics->suspiciousWildcardFetch > 0) {
            $this->score -= $this->metrics->suspiciousWildcardFetch / 5;
        }
        if ($this->metrics->duplicateBurst > 0) {
            $this->score -= 10;
        }
        if ($this->metrics->probableNPlus1 > 0) {
            $this->score -= 10;
        }
        if ($this->metrics->totalQueryTime > Thresholds::getTotalQueryTime()) {
            $this->score -= 10;
        }
        if ($this->metrics->totalQueryCount > Thresholds::getTotalQueryCount()) {
            $this->score -= 10;
        }
        $this->score = $this->score > 0 ? $this->score : 0;
    }

    /**
     * Get the score of the query.
     * @return int The score of the query.
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * Get the status of the query.
     * @return string The status of the query.
     */
    public function getStatus(): string
    {
        if ($this->score <= self::SCORE_CRITICAL) {
            return 'CRITICAL';
        } elseif ($this->score <= self::SCORE_POOR) {
            return 'POOR';
        } elseif ($this->score <= self::SCORE_WATCH) {
            return 'WATCH';
        }
        return 'HEALTHY';
    }
}
