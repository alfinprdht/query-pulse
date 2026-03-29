<?php

namespace Alfinprdht\PerformanceQueryInspector;

use Illuminate\Support\ServiceProvider;

class QueryPulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/query-pulse.php',
            'query-pulse'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/query-pulse.php' => config_path('query-pulse.php'),
            ], 'query-pulse-config');
        }
    }
}
