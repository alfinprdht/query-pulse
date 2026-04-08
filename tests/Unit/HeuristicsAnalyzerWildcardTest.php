<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HeuristicsAnalyzerWildcardTest extends TestCase
{
    /**
     * @dataProvider wildcardSqlProvider
     */
    public function test_has_wildcard_fetch_detection(string $sql, bool $expected): void
    {
        $ref = new ReflectionClass(HeuristicsAnalyzer::class);
        $instance = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('hasWildcardFetch');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($instance, $sql));
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function wildcardSqlProvider(): array
    {
        return [
            'select star' => ['SELECT * FROM users', true],
            'table dot star' => ['select u.* from users u', true],
            'named columns' => ['select id, name from users', false],
            'comment hides star' => ["select id -- *\nfrom users", false],
            'block comment' => ['select /* * */ id from users', false],
        ];
    }
}
