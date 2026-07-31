<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Services\BidService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainHomeController extends Controller
{
    public function __invoke(Request $request, BidService $bids): Response
    {
        /** @var Domain $domain */
        $domain = $request->attributes->get('domain');
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
            'highestBid' => $bids->highestAcceptedAmount($domain),
            'minimumBid' => max(
                (int) config('bids.minimum_amount'),
                $bids->highestAcceptedAmount($domain) + (int) config('bids.increment'),
            ),
        ]);
    }
}
