<?php

namespace Alfinprdht\QueryPulse\Middleware;

use Closure;
use Illuminate\Http\Request;

class QueryPulseDashboardMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $username = (string) config('query-pulse.auth.username', '');
        $password = (string) config('query-pulse.auth.password', '');

        /**
         * Should set the username and password in the config file
         */
        if ($username === '' || $password === '') {
            return response('Unauthorized, please set the username and password in the config file', 401);
        }

        [
            $providedUser,
            $providedPass
        ] = $this->getBasicAuthCredentials($request);

        if (!$this->compareBasicAuthCredentials($username, $password, $providedUser, $providedPass)) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => $this->getWwwAuthenticateHeaderValue(),
            ]);
        }

        return $next($request);
    }

    /**
     * Get the WWW-Authenticate header value.
     * @return string
     */
    private function getWwwAuthenticateHeaderValue(): string
    {
        $hourBucket = intdiv(time(), 3600);
        $realm = 'Query Pulse (' . $hourBucket . ')';
        return 'Basic realm="' . $realm . '", charset="UTF-8"';
    }

    /**
     * Compare the basic auth credentials.
     * @param string $username
     * @param string $password
     * @param string $providedUser
     * @param string $providedPass
     * @return bool
     */
    private function compareBasicAuthCredentials(string $username, string $password, ?string $providedUser, ?string $providedPass): bool
    {
        return is_string($providedUser) && hash_equals($username, $providedUser)
            && is_string($providedPass) && hash_equals($password, $providedPass);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function getBasicAuthCredentials(Request $request): array
    {
        $user = $request->getUser();
        $pass = $request->getPassword();

        if (is_string($user) && $user !== '') {
            return [$user, $pass];
        }

        $header = $request->header('Authorization');
        if (!is_string($header) || $header === '') {
            return [null, null];
        }

        if (!preg_match('/^\s*Basic\s+(?<b64>.+)\s*$/i', $header, $m)) {
            return [null, null];
        }

        $decoded = base64_decode($m['b64'], true);
        if (!is_string($decoded) || $decoded === '' || !str_contains($decoded, ':')) {
            return [null, null];
        }

        [$username, $password] = explode(':', $decoded, 2);
        return [$username, $password];
    }
}
