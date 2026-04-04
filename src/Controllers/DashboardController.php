<?php

namespace Alfinprdht\QueryPulse\Controllers;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use Alfinprdht\QueryPulse\Reporting\WebReporting\QueryList;
use Alfinprdht\QueryPulse\Reporting\WebReporting;
use Alfinprdht\QueryPulse\Support\Helpers;
use Alfinprdht\QueryPulse\Support\Thresholds;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $endpoints = (new QueryList())->get();

        $criticalCount = $endpoints->filter(function ($endpoint) {
            return ($endpoint['status'] ?? '') === 'CRITICAL';
        })->count();

        $poorCount = $endpoints->filter(function ($endpoint) {
            return ($endpoint['status'] ?? '') === 'POOR';
        })->count();

        $highest = $endpoints
            ->sortByDesc(function ($endpoint) {
                return (float) ($endpoint['latest_total_query_time'] ?? 0);
            })
            ->first();

        return view('query-pulse::dashboard', [
            'endpoints' => $endpoints,
            'summary' => [
                'critical_count' => $criticalCount,
                'poor_count' => $poorCount,
                'highest_latest_total_query_time' => (float) ($highest['latest_total_query_time'] ?? 0),
                'highest_url' => $highest['url'] ?? '',
                'highest_report_id' => $highest['report_id'] ?? null,
            ],
        ]);
    }

    public function report($reportId)
    {
        $fullUrl = Helpers::getUrlFromReportId($reportId);

        $analyzer = new HeuristicsAnalyzer($fullUrl);
        $analyzer->analyze();

        $analysisResult = $analyzer->getAnalysisResult();

        $reporting = new WebReporting(
            $fullUrl,
            $analysisResult
        );

        $queryPulse = $analyzer->getQueries();

        $maxTotalQueryTime = $queryPulse->max('total_query_time');

        $transformedQueryPulse = (clone $queryPulse)
            ->transform(function ($item) use ($maxTotalQueryTime) {
                return [
                    'id' => $item->id,
                    'total_query_time' => $item->total_query_time,
                    'percentage' => $item->total_query_time / $maxTotalQueryTime * 100,
                    'cross_treshold' => $item->total_query_time > Thresholds::getTotalQueryTime(),
                ];
            })->sortBy('id');

        return view('query-pulse::report', [
            'fullUrl' => $fullUrl,
            'averageQueryTime' => $analyzer->averageQueryTime,
            'result' => $reporting->result(),
            'transformedQueryPulse' => $transformedQueryPulse,
            'lastSync' => $analysisResult->lastFetchedAt,
        ]);
    }

    public function delete($reportId)
    {
        $fullUrl = Helpers::getUrlFromReportId($reportId);
        if ($fullUrl === null) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
            ], 404);
        }

        if (Helpers::isUrlIgnored($fullUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is ignored and cannot be deleted',
            ], 403);
        }

        DB::table('query_pulse')->where('url', $fullUrl)->delete();
        DB::table('query_pulse_report')->where('url', $fullUrl)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
