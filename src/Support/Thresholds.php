<?php

namespace Alfinprdht\QueryPulse\Support;

class Thresholds
{
    protected static function config($key)
    {
        return config('query-pulse.thresholds')[$key];
    }

    public static function getSlowQueryTime()
    {
        return self::config('slow_query_time');
    }

    public static function getDuplicateBurst()
    {
        return self::config('duplicate_burst');
    }

    public static function getProbableNPlus1()
    {
        return self::config('probable_n_plus_1');
    }

    public static function getTotalQueryTime()
    {
        return self::config('total_query_time');
    }

    public static function getTotalQueryCount()
    {
        return self::config('total_query_count');
    }
}
