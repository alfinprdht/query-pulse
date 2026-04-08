<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Middleware\QueryPulseMiddleware;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueryPulseMiddlewareTest extends TestCase
{
    public function test_disabled_skips_collection(): void
    {
        config(['query-pulse.enabled' => false]);

        $middleware = new QueryPulseMiddleware();
        $called = false;

        $response = $middleware->handle(Request::create('/api/demo', 'GET'), function () use (&$called) {
            $called = true;

            return response('ok');
        });

        $this->assertTrue($called);
        $this->assertSame('ok', $response->getContent());
        $this->assertSame(0, DB::table('query_pulse')->count());
    }

    public function test_ignored_route_skips_collection(): void
    {
        config(['query-pulse.enabled' => true]);

        $middleware = new QueryPulseMiddleware();

        $response = $middleware->handle(Request::create('/query-pulse', 'GET'), function () {
            return response('dashboard');
        });

        $this->assertSame('dashboard', $response->getContent());
        $this->assertSame(0, DB::table('query_pulse')->count());
    }

    public function test_collects_queries_and_persists_row(): void
    {
        config(['query-pulse.enabled' => true]);

        $middleware = new QueryPulseMiddleware();

        $response = $middleware->handle(Request::create('/api/demo', 'GET'), function () {
            DB::select('select 1');

            return response('done');
        });

        $this->assertSame('done', $response->getContent());

        $row = DB::table('query_pulse')->where('url', 'GET api/demo')->first();
        $this->assertNotNull($row);
        $this->assertGreaterThan(0, $row->total_query_time);

        $decoded = json_decode($row->query_executed, true);
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);
    }
}
