<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class HeuristicsAnalyzerTest extends TestCase
{
    public function test_analyze_returns_false_when_no_pulse_rows(): void
    {
        $analyzer = new HeuristicsAnalyzer('GET missing');

        $this->assertFalse($analyzer->analyze());
    }

    public function test_analyze_persists_report_for_slow_queries(): void
    {
        $url = 'GET api/orders';
        $queries = [
            [
                'sql' => 'select * from orders where id = ?',
                'bindings_hashed' => md5(json_encode([1])),
                'time' => 150.0,
                'trace' => null,
            ],
        ];

        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => json_encode($queries),
            'total_query_time' => 150,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $analyzer = new HeuristicsAnalyzer($url);

        $this->assertTrue($analyzer->analyze());

        $report = DB::table('query_pulse_report')->where('url', $url)->first();
        $this->assertNotNull($report);
        $this->assertNotEmpty($report->analysis_result);

        $result = $analyzer->getAnalysisResult();
        $this->assertGreaterThanOrEqual(1, $result->metrics->slowQueryTime);
        $this->assertNotEmpty($result->status);
    }

    public function test_analyze_detects_duplicate_burst(): void
    {
        $url = 'GET api/items';
        $queries = [];
        for ($i = 0; $i < 12; $i++) {
            $queries[] = [
                'sql' => 'select * from items where id = ?',
                'bindings_hashed' => md5(json_encode([42])),
                'time' => 1.0,
                'trace' => null,
            ];
        }

        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => json_encode($queries),
            'total_query_time' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $analyzer = new HeuristicsAnalyzer($url);
        $this->assertTrue($analyzer->analyze());

        $this->assertGreaterThanOrEqual(1, $analyzer->getAnalysisResult()->metrics->duplicateBurst);
    }
}
