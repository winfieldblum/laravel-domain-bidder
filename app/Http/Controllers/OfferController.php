<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfferRequest;
use App\Models\Domain;
use App\Services\BidService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function create(Request $request, BidService $bids): Response
    {
        /** @var Domain $domain */
        $domain = $request->attributes->get('domain');
        $highest = $bids->highestAcceptedAmount($domain);

        return Inertia::render('domains/Offer', [
            'domain' => [
                'hostname' => $domain->hostname,
                'display_name' => $domain->display_name,
            ],
            'highestBid' => $highest,
            'minimumBid' => max(
                (int) config('bids.minimum_amount'),
                $highest + (int) config('bids.increment'),
            ),
        ]);
    }

    public function store(StoreOfferRequest $request, BidService $bids): RedirectResponse
    {
        /** @var Domain $domain */
        $domain = $request->attributes->get('domain');

        $bids->placeOffer($domain, $request->validated());

        return redirect()
            ->route('offer.create')
            ->with('offer_submitted', true);
    }
}
