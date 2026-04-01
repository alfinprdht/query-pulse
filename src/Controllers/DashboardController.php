<?php

namespace Alfinprdht\QueryPulse\Controllers;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use Alfinprdht\QueryPulse\Reporting\WebReporting;
use Alfinprdht\QueryPulse\Support\Helpers;
use Alfinprdht\QueryPulse\Support\Thresholds;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $latestPerUrl = DB::table('query_pulse')
            ->selectRaw('url, MAX(id) as latest_id')
            ->groupBy('url');

        $endpoints = DB::table('query_pulse as qp')
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
                COUNT(*) as snapshots,
                AVG(qp.total_query_time) as avg_total_query_time,
                MAX(qp.created_at) as last_seen_at,
                latest.total_query_time as latest_total_query_time,
                qpr.status as status')
            ->groupBy('qp.url', 'latest.total_query_time', 'qpr.status')
            ->orderByDesc('last_seen_at')
            ->get()
            ->transform(function ($row) {
                return [
                    'url' => $row->url,
                    'report_id' => md5($row->url),
                    'snapshots' => (int) $row->snapshots,
                    'latest_total_query_time' => (float) $row->latest_total_query_time,
                    'avg_total_query_time' => (float) $row->avg_total_query_time,
                    'last_seen_at' => $row->last_seen_at,
                    'status' => $row->status ?? '',
                ];
            });

        return view('query-pulse::dashboard', [
            'endpoints' => $endpoints,
        ]);
    }

    public function report($reportId)
    {
        $fullUrl = Helpers::getUrlFromReportId($reportId);

        $analyzer = new HeuristicsAnalyzer($fullUrl);
        $analyzer->analyze();

        $reporting = new WebReporting(
            $fullUrl,
            $analyzer->getAnalysisResult()
        );

        $queryPulse = $analyzer->getQueries();

        $transformedQueryPulse = (clone $queryPulse)
            ->transform(function ($item) use ($queryPulse) {
                return [
                    'id' => $item->id,
                    'total_query_time' => $item->total_query_time,
                    'percentage' => $item->total_query_time / $queryPulse->max('total_query_time') * 100,
                    'cross_treshold' => $item->total_query_time > Thresholds::getTotalQueryTime(),
                ];
            })->sortBy('id');


        if (isset($_GET['debug'])) {
            dd($reporting->result());
        }

        return view('query-pulse::report', [
            'fullUrl' => $fullUrl,
            'averageQueryTime' => $analyzer->averageQueryTime,
            'result' => $reporting->result(),
            'transformedQueryPulse' => $transformedQueryPulse,
        ]);
    }
}
