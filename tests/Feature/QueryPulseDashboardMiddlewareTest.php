<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Middleware\QueryPulseDashboardMiddleware;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Http\Request;

class QueryPulseDashboardMiddlewareTest extends TestCase
{
    public function test_missing_credentials_returns_401_with_message(): void
    {
        config([
            'query-pulse.auth.username' => '',
            'query-pulse.auth.password' => '',
        ]);

        $middleware = new QueryPulseDashboardMiddleware();
        $next = fn () => response('next');

        $response = $middleware->handle(Request::create('/query-pulse', 'GET'), $next);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString('config', $response->getContent());
    }

    public function test_invalid_basic_auth_requests_challenge(): void
    {
        $middleware = new QueryPulseDashboardMiddleware();
        $request = Request::create('/query-pulse', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'wrong',
            'PHP_AUTH_PW' => 'wrong',
        ]);

        $response = $middleware->handle($request, fn () => response('next'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertTrue($response->headers->has('WWW-Authenticate'));
    }

    public function test_valid_authorization_header_passes(): void
    {
        $middleware = new QueryPulseDashboardMiddleware();
        $token = base64_encode('admin:secret');
        $request = Request::create('/query-pulse', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Basic '.$token,
        ]);

        $seen = false;
        $response = $middleware->handle($request, function () use (&$seen) {
            $seen = true;

            return response('inside');
        });

        $this->assertTrue($seen);
        $this->assertSame('inside', $response->getContent());
    }
}
