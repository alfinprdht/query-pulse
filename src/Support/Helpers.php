<?php

namespace Alfinprdht\QueryPulse\Support;

use Illuminate\Support\Facades\DB;

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
}
