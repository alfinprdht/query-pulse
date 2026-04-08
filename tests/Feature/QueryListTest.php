<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Reporting\WebReporting\QueryList;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class QueryListTest extends TestCase
{
    public function test_filters_ignored_urls_and_includes_report_id(): void
    {
        $visible = 'GET api/visible';
        DB::table('query_pulse')->insert([
            'url' => $visible,
            'query_executed' => '[]',
            'total_query_time' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('query_pulse')->insert([
            'url' => 'GET query-pulse/index',
            'query_executed' => '[]',
            'total_query_time' => 5,
            'created_at' => now()->subSecond(),
            'updated_at' => now()->subSecond(),
        ]);

        $list = (new QueryList())->get();

        $this->assertCount(1, $list);
        $first = $list->first();
        $this->assertSame($visible, $first['url']);
        $this->assertSame(md5($visible), $first['report_id']);
    }
}
