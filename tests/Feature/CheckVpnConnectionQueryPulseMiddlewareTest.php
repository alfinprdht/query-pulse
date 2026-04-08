<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Middleware\CheckVpnConnectionQueryPulseMiddleware;
use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Http\Request;

class CheckVpnConnectionQueryPulseMiddlewareTest extends TestCase
{
    public function test_forbidden_when_ip_not_allowed(): void
    {
        $middleware = new CheckVpnConnectionQueryPulseMiddleware();
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1']);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_allowed_when_ip_matches_list(): void
    {
        $middleware = new CheckVpnConnectionQueryPulseMiddleware();
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);

        $hit = false;
        $response = $middleware->handle($request, function () use (&$hit) {
            $hit = true;

            return response('vpn-ok');
        });

        $this->assertTrue($hit);
        $this->assertSame('vpn-ok', $response->getContent());
    }
}
