<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDomain
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*')) {
            if (! $this->isPrimaryAppHost($request)) {
                abort(404);
            }

            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $hostname = $this->normalizeHost($request->getHost());

        $domain = Domain::query()
            ->active()
            ->whereIn('hostname', $this->candidateHostnames($hostname))
            ->first();

        if ($domain === null) {
            abort(404);
        }

        $request->attributes->set('domain', $domain);
        app()->instance(Domain::class, $domain);

        return $next($request);
    }

    protected function shouldSkip(Request $request): bool
    {
        return $request->is('login', 'logout', 'register', 'forgot-password', 'reset-password', 'reset-password/*')
            || $request->is('email/*', 'user/*', 'two-factor-challenge', 'confirm-password')
            || $request->is('settings', 'settings/*')
            || $request->is('dashboard')
            || $request->is('up')
            || $request->is('.well-known/*');
    }

    protected function isPrimaryAppHost(Request $request): bool
    {
        $primaryHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($primaryHost) || $primaryHost === '') {
            return false;
        }

        return $this->normalizeHost($request->getHost()) === $this->normalizeHost($primaryHost);
    }

    protected function normalizeHost(string $host): string
    {
        $host = strtolower($host);

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    /**
     * @return list<string>
     */
    protected function candidateHostnames(string $host): array
    {
        $candidates = [$host];

        if (str_ends_with($host, '.ddev.site')) {
            $candidates[] = substr($host, 0, -strlen('.ddev.site'));
        }

        return array_values(array_unique($candidates));
    }
}
