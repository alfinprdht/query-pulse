<?php

namespace Alfinprdht\QueryPulse\Collector;

use Illuminate\Support\Facades\DB;

class QueryCollector
{
    public function run()
    {
        DB::listen(function ($query) {
            echo $query->sql . '<br/>';
        });
    }
}
