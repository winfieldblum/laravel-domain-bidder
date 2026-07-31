<?php

namespace App\Http\Controllers;

use App\Services\BidService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BidVerificationController extends Controller
{
    public function __invoke(Request $request, string $token, BidService $bids): Response
    {
        $bid = $bids->verify($token);

        return Inertia::render('domains/Verify', [
            'success' => $bid !== null,
            'amount' => $bid?->amount,
            'hostname' => $bid?->domain->hostname ?? $request->attributes->get('domain')?->hostname,
        ]);
    }
}
