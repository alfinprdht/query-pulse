<?php

namespace Alfinprdht\QueryPulse\Commands;

use Alfinprdht\QueryPulse\Analysis\HeuristicsAnalyzer;
use Alfinprdht\QueryPulse\Reporting\LogReporting;
use Illuminate\Console\Command;

class QueryPulseReportCommand extends Command
{
    /**
     * The signature of the command.
     * @var string
     */
    protected $signature = 'query-pulse:url {url}';

    /**
     * The description of the command.
     * @var string
     */
    protected $description = 'Generate a report on specific URL';
    public function handle()
    {
        $this->info('Generating report for URL: ' . $this->argument('url'));

        $url = $this->argument('url');

        $analyzer = new HeuristicsAnalyzer($url);
        $analyzer->analyze();

        $analysisResult = $analyzer->getAnalysisResult();

        $reporting = new LogReporting($analysisResult);
        $reporting->report($this);
    }
}
