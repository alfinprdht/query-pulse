<?php

namespace Alfinprdht\QueryPulse\Controllers;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use Alfinprdht\QueryPulse\Reporting\WebReporting\QueryList;
use Alfinprdht\QueryPulse\Reporting\WebReporting;
use Alfinprdht\QueryPulse\Support\Helpers;
use Alfinprdht\QueryPulse\Support\Thresholds;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('query-pulse::dashboard', [
            'endpoints' => (new QueryList())->get(),
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

        return view('query-pulse::report', [
            'fullUrl' => $fullUrl,
            'averageQueryTime' => $analyzer->averageQueryTime,
            'result' => $reporting->result(),
            'transformedQueryPulse' => $transformedQueryPulse,
        ]);
    }
}
