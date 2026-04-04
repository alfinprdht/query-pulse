<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable query pulse
    |--------------------------------------------------------------------------
    */
    'enabled' => env('QUERY_PULSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'slow_query_time' => env('QUERY_PULSE_SLOW_QUERY_TIME', 100), //
        'duplicate_burst' => env('QUERY_PULSE_DUPLICATE_BURST', 10),
        'probable_n_plus_1' => env('QUERY_PULSE_PROBABLE_N_PLUS_1', 5),
        'total_query_time' => env('QUERY_PULSE_TOTAL_QUERY_TIME', 300),
        'total_query_count' => env('QUERY_PULSE_TOTAL_QUERY_COUNT', 75),
    ],

    /**
     * Ignored URLS
     *
     * @var array<string>
     */
    'ignored_urls' => [
        'query-pulse',
        'query-pulse/*',
        '.well-known/*',
    ],

    /**
     * Auto Generate Report every n request
     */
    'auto_generate_report_every' => env('QUERY_PULSE_AUTO_GENERATE_REPORT_EVERY', 100),

    /**
     * Set speficif url stack trace to be enabled
     * Be aware of performance impact if you enable this for too many urls
     * @var array<string>
     */
    'enabled_url_stack_trace' => [
        '*',
    ]
];
