<?php

namespace Alfinprdht\QueryPulse;

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
        $this->commands([
            \Alfinprdht\QueryPulse\Commands\QueryPulseReportCommand::class
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(
            \Alfinprdht\QueryPulse\Middleware\QueryPulseMiddleware::class
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/query-pulse.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'query-pulse');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/query-pulse'),
        ], 'query-pulse-views');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/query-pulse.php' => config_path('query-pulse.php'),
            ], 'query-pulse-config');
        }
    }
}
