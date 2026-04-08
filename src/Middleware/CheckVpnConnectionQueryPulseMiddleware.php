<?php

namespace Alfinprdht\QueryPulse\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckVpnConnectionQueryPulseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedVpnIps = explode(',', config('query-pulse.allowed_vpn_ips'));

        if (!in_array($request->ip(), $allowedVpnIps)) {
            return response('Forbidden, you must be connected to VPN to access this resource', 403);
        }

        return $next($request);
    }
}
