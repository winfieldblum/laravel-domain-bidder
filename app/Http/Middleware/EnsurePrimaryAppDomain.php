<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrimaryAppDomain
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $primaryHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($primaryHost) || $primaryHost === '') {
            abort(404);
        }

        $requestHost = strtolower($request->getHost());
        $primaryHost = strtolower($primaryHost);

        if (str_starts_with($requestHost, 'www.')) {
            $requestHost = substr($requestHost, 4);
        }

        if (str_starts_with($primaryHost, 'www.')) {
            $primaryHost = substr($primaryHost, 4);
        }

        if ($requestHost !== $primaryHost) {
            abort(404);
        }

        return $next($request);
    }
}
