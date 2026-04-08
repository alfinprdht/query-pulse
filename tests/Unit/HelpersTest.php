<?php

namespace Alfinprdht\QueryPulse\Tests\Unit;

use Alfinprdht\QueryPulse\Support\Helpers;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class HelpersTest extends TestCase
{
    public function test_get_url_from_report_id_returns_matching_url(): void
    {
        $url = 'GET api/v1/users';
        DB::table('query_pulse')->insert([
            'url' => $url,
            'query_executed' => '[]',
            'total_query_time' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame($url, Helpers::getUrlFromReportId(md5($url)));
    }

    public function test_get_url_from_report_id_returns_null_when_missing(): void
    {
        $this->assertNull(Helpers::getUrlFromReportId(md5('missing')));
    }

    public function test_is_url_ignored_matches_path_and_method_patterns(): void
    {
        $this->assertTrue(Helpers::isUrlIgnored('GET query-pulse/report/abc'));
        $this->assertTrue(Helpers::isUrlIgnored('query-pulse/foo'));

        $this->assertFalse(Helpers::isUrlIgnored('GET api/orders'));
        $this->assertFalse(Helpers::isUrlIgnored('api/orders'));
    }

    public function test_is_url_ignored_returns_false_for_empty_config(): void
    {
        config(['query-pulse.ignored_urls' => []]);

        $this->assertFalse(Helpers::isUrlIgnored('GET anything'));
    }

    public function test_get_suggestion_known_types(): void
    {
        $this->assertNotSame('', Helpers::getSuggestion('slow_query'));
        $this->assertNotSame('', Helpers::getSuggestion('duplicate_burst'));
        $this->assertNotSame('', Helpers::getSuggestion('probable_n_plus_1'));
        $this->assertNotSame('', Helpers::getSuggestion('suspicious_wildcard_fetch'));
    }

    public function test_get_suggestion_unknown_type_empty_string(): void
    {
        $this->assertSame('', Helpers::getSuggestion('unknown'));
    }
}
