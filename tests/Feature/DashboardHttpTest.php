<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\Http\Middleware\Authenticate;

class DashboardHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            Authenticate::class,
            VerifyCsrfToken::class,
        ]);
    }

    public function test_dashboard_index_renders(): void
    {
        DB::table('query_pulse')->insert([
            'url' => 'GET api/ping',
            'query_executed' => '[]',
            'total_query_time' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withBasicAuth('admin', 'secret')
            ->get('/query-pulse/')
            ->assertOk()
            ->assertSee('GET api/ping', false);
    }

    public function test_report_page_renders_for_known_hash(): void
    {
        $url = 'GET api/report-me';
        $queries = [
            ['sql' => 'select 1', 'bindings_hashed' => 'a', 'time' => 1.0, 'trace' => null],
        ];

        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => json_encode($queries),
            'total_query_time' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = md5($url);

        $this->withBasicAuth('admin', 'secret')
            ->get('/query-pulse/report/'.$id)
            ->assertOk()
            ->assertSeeText('report-me');
    }

    public function test_delete_removes_rows(): void
    {
        $url = 'GET api/delete-me';
        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => '[]',
            'total_query_time' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = md5($url);

        $this->withBasicAuth('admin', 'secret')
            ->postJson('/query-pulse/delete/'.$id)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, DB::table('query_pulse')->where('url', $url)->count());
    }

    public function test_delete_forbidden_for_ignored_endpoint(): void
    {
        $url = 'GET query-pulse/secret';
        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => '[]',
            'total_query_time' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = md5($url);

        $this->withBasicAuth('admin', 'secret')
            ->postJson('/query-pulse/delete/'.$id)
            ->assertStatus(403);
    }

    public function test_report_returns_404_for_unknown_hash(): void
    {
        $this->withBasicAuth('admin', 'secret')
            ->get('/query-pulse/report/'.str_repeat('a', 32))
            ->assertNotFound();
    }
}
