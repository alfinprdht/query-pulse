<?php

namespace Alfinprdht\QueryPulse\Reporting\WebReporting;

use Alfinprdht\QueryPulse\Support\Helpers;
use Illuminate\Support\Facades\DB;

class QueryList
{
    /**
     * Get the list of endpoints.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {

        $latestPerUrl = DB::table('query_pulse')
            ->selectRaw('url, MAX(id) as latest_id')
            ->groupBy('url');

        return DB::table('query_pulse as qp')
            ->joinSub($latestPerUrl, 'lu', function ($join) {
                $join->on('qp.url', '=', 'lu.url');
            })
            ->join('query_pulse as latest', function ($join) {
                $join->on('latest.id', '=', 'lu.latest_id');
            })
            ->leftJoin('query_pulse_report as qpr', function ($join) {
                $join->on('qpr.url', '=', 'qp.url');
            })
            ->selectRaw('qp.url,
                AVG(qp.total_query_time) as avg_total_query_time,
                MAX(qp.created_at) as last_seen_at,
                latest.total_query_time as latest_total_query_time,
                qpr.status as status')
            ->groupBy('qp.url', 'latest.total_query_time', 'qpr.status')
            ->orderByDesc('last_seen_at')
            ->get()
            ->filter(function ($row) {
                return ! Helpers::isUrlIgnored($row->url);
            })
            ->transform(function ($row) {
                return [
                    'url' => $row->url,
                    'report_id' => md5($row->url),
                    'latest_total_query_time' => (float) $row->latest_total_query_time,
                    'avg_total_query_time' => (float) $row->avg_total_query_time,
                    'last_seen_at' => $row->last_seen_at,
                    'status' => $row->status ?? '',
                ];
            });
    }
}
