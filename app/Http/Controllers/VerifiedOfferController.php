<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerifiedOfferRequest;
use App\Models\Domain;
use App\Models\RebidToken;
use App\Services\BidService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerifiedOfferController extends Controller
{
    public function create(Request $request, string $token, BidService $bids): Response
    {
        /** @var Domain|null $resolvedDomain */
        $resolvedDomain = $request->attributes->get('domain');

        $rebidToken = RebidToken::query()
            ->with('domain')
            ->where('token', $token)
            ->first();

        if (
            $rebidToken === null
            || ! $rebidToken->isValid()
            || $resolvedDomain === null
            || $rebidToken->domain_id !== $resolvedDomain->id
        ) {
            return Inertia::render('domains/VerifiedOffer', [
                'valid' => false,
                'domain' => [
                    'hostname' => $resolvedDomain?->hostname ?? 'Domain',
                    'display_name' => $resolvedDomain?->display_name ?? 'Domain',
                ],
                'bidder' => null,
                'highestBid' => 0,
                'minimumBid' => (int) config('bids.minimum_amount'),
                'token' => $token,
            ]);
        }

        $domain = $rebidToken->domain;
        $highest = $bids->highestAcceptedAmount($domain);

        return Inertia::render('domains/VerifiedOffer', [
            'valid' => true,
            'domain' => [
                'hostname' => $domain->hostname,
                'display_name' => $domain->display_name,
            ],
            'bidder' => [
                'name' => $rebidToken->name,
                'email' => $rebidToken->email,
            ],
            'highestBid' => $highest,
            'minimumBid' => max(
                (int) config('bids.minimum_amount'),
                $highest + (int) config('bids.increment'),
            ),
            'token' => $rebidToken->token,
            'expiresAt' => $rebidToken->expires_at?->toIso8601String(),
        ]);
    }

    public function store(
        StoreVerifiedOfferRequest $request,
        string $token,
        BidService $bids,
    ): RedirectResponse {
        $rebidToken = RebidToken::query()
            ->where('token', $token)
            ->firstOrFail();

        $bids->placeVerifiedOffer($rebidToken, $request->validated());

        return redirect()
            ->route('offer.verified.create', ['token' => $token])
            ->with('offer_submitted', true);
    }
}
