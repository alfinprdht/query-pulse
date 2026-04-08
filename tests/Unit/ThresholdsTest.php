<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\Support\Thresholds;
use Alfinprdht\QueryPulse\Tests\TestCase;

class ThresholdsTest extends TestCase
{
    public function test_reads_config_thresholds(): void
    {
        $this->assertSame(100, Thresholds::getSlowQueryTime());
        $this->assertSame(10, Thresholds::getDuplicateBurst());
        $this->assertSame(5, Thresholds::getProbableNPlus1());
        $this->assertSame(300, Thresholds::getTotalQueryTime());
        $this->assertSame(75, Thresholds::getTotalQueryCount());
    }
}
