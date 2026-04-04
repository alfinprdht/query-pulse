<?php

namespace Alfinprdht\QueryPulse\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Get the URL from the report ID.
     *
     * @param string $reportId
     * @return string|null
     */
    public static function getUrlFromReportId($reportId): string|null
    {
        return DB::table('query_pulse')
            ->select('url')
            ->whereRaw('md5(url) = ?', [$reportId])
            ->first()
            ?->url ?? null;
    }

    /**
     * True if URL should be hidden (config query-pulse.ignored_urls).
     * Supports:
     * - exact path: "query-pulse"
     * - wildcard path: "query-pulse/*" matches "query-pulse/test", "query-pulse/ok", …
     * - full request key: "GET query-pulse/*" (method + space + path pattern)
     *
     * Stored URLs follow "METHOD path" (e.g. GET query-pulse/report/abc).
     * @param string $url
     * @return bool
     */
    public static function isUrlIgnored(string $url): bool
    {
        $patterns = config('query-pulse.ignored_urls', []);
        if ($patterns === null || $patterns === []) {
            return false;
        }

        $path = $url;
        if (preg_match('/^[A-Z]+\s+(.+)$/u', $url, $m)) {
            $path = $m[1];
        }

        foreach ($patterns as $pattern) {
            if ($pattern === null || $pattern === '') {
                continue;
            }

            if (preg_match('/^[A-Z]+\s+/u', $pattern)) {
                if (Str::is($pattern, $url)) {
                    return true;
                }
                continue;
            }

            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the suggestion for the issue.
     * @param string $type
     * @return string
     */
    public static function getSuggestion(string $type): string
    {
        switch ($type) {
            case 'slow_query':
                return 'Review filters, joins, selected columns, and indexes.';
            case 'duplicate_burst':
                return 'Avoid repeated lookup queries inside loops or transformers.';
            case 'probable_n_plus_1':
                return 'Use eager loading via with() on the parent query.';
            case 'suspicious_wildcard_fetch':
                return 'Avoid using wildcard fetches in queries.';
        }
        return '';
    }
}
