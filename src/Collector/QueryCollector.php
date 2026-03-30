<?php

namespace Alfinprdht\QueryPulse\Collector;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QueryCollector
{

    /**
     * The queries executed.
     * @var array
     */
    private array $queries;

    /**
     * The total time of the queries executed.
     * @var float
     */
    private float $totalQueryTime;

    /**
     * The request object.
     * @var Request
     */
    private Request $request;

    /**
     * Constructor for the QueryCollector class.
     * @param \Illuminate\Http\Request $request The request object.
     */
    public function __construct(Request $request)
    {
        $this->queries = [];
        $this->totalQueryTime = 0;
        $this->request = $request;
    }

    /**
     * Listen to the database queries
     * @return void
     */
    public function listen()
    {
        DB::listen(function ($query) {
            $time = $query->time;
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $time
            ];
            $this->totalQueryTime += $time;
        });
    }

    /**
     * Save the queries to the database
     * @return void
     */
    public function save()
    {
        $data = [
            'url' => $this->request->method() . ' ' . $this->request->path(),
            'query_executed' => json_encode($this->queries),
            'total_query_time' => $this->totalQueryTime,
        ];
        DB::table('query_pulse')->insert($data);
    }
}
