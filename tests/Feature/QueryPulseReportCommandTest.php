<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class QueryPulseReportCommandTest extends TestCase
{
    public function test_command_runs_without_uncaught_exception(): void
    {
        $url = 'GET cli/url';
        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => json_encode([
                ['sql' => 'select 1', 'bindings_hashed' => 'z', 'time' => 1.0, 'trace' => null],
            ]),
            'total_query_time' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('query-pulse:url', ['url' => $url])->assertSuccessful();
    }
}
