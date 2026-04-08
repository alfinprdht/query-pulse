<?php

namespace Alfinprdht\QueryPulse\Tests;

use Alfinprdht\QueryPulse\QueryPulseServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            QueryPulseServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('app.url', 'http://localhost');

        $app->booted(function () use ($app) {
            $connection = $app['db']->connection();
            if ($connection->getDriverName() !== 'sqlite') {
                return;
            }

            $pdo = $connection->getPdo();
            if ($pdo instanceof \PDO && method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction(
                    'md5',
                    static fn (mixed $value): string => md5((string) $value),
                    1,
                );
            }
        });

        $app['config']->set('query-pulse.enabled', true);
        $app['config']->set('query-pulse.auth.username', 'admin');
        $app['config']->set('query-pulse.auth.password', 'secret');
        $app['config']->set('query-pulse.auto_generate_report_every', 0);
        $app['config']->set('query-pulse.enabled_url_stack_trace', []);

        $app['config']->set('query-pulse.thresholds', [
            'slow_query_time' => 100,
            'duplicate_burst' => 10,
            'probable_n_plus_1' => 5,
            'total_query_time' => 300,
            'total_query_count' => 75,
        ]);

        $app['config']->set('query-pulse.ignored_urls', [
            'query-pulse',
            'query-pulse/*',
            '.well-known/*',
            'vendor/*',
        ]);

        $app['config']->set('query-pulse.allowed_vpn_ips', '10.0.0.1,10.0.0.2');
    }
}
