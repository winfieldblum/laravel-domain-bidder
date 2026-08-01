<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Services\BidService;
use App\Services\ImpressionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainHomeController extends Controller
{
    public function __invoke(Request $request, BidService $bids, ImpressionService $impressions): Response
    {
        /** @var Domain $domain */
        $domain = $request->attributes->get('domain');

        if ($request->isMethod('GET')) {
            $impressions->record($domain, $request->userAgent());
        }

        $domain->load(['features', 'sellingPoints']);

        return Inertia::render('domains/Home', [
            'domain' => [
                'hostname' => $domain->hostname,
                'display_name' => $domain->display_name,
                'tagline' => $domain->tagline,
                'description' => $domain->description,
                'features' => $domain->features->map(fn ($feature) => [
                    'icon' => $feature->icon,
                    'title' => $feature->title,
                    'description' => $feature->description,
                    'color' => $feature->color,
                ]),
                'selling_points' => $domain->sellingPoints->pluck('text'),
            ],
            'otherDomains' => $this->otherDomainsForSale($domain, $request),
            'highestBid' => $bids->highestAcceptedAmount($domain),
            'minimumBid' => max(
                (int) config('bids.minimum_amount'),
                $bids->highestAcceptedAmount($domain) + (int) config('bids.increment'),
            ),
        ]);
    }

    /**
     * @return list<array{hostname: string, display_name: string, tagline: string, url: string}>
     */
    protected function otherDomainsForSale(Domain $current, Request $request): array
    {
        return Domain::query()
            ->active()
            ->whereKeyNot($current->id)
            ->orderBy('hostname')
            ->get(['hostname', 'display_name', 'tagline'])
            ->map(fn (Domain $domain): array => [
                'hostname' => $domain->hostname,
                'display_name' => $domain->display_name,
                'tagline' => $domain->tagline,
                'url' => $this->publicUrlForDomain($domain, $request),
            ])
            ->values()
            ->all();
    }

    protected function publicUrlForDomain(Domain $domain, Request $request): string
    {
        $scheme = $request->secure() ? 'https' : 'http';
        $host = $domain->hostname;

        if (str_ends_with(strtolower($request->getHost()), '.ddev.site')) {
            $host .= '.ddev.site';
        }

        return "{$scheme}://{$host}";
    }
}
