<?php

namespace App\Services;

use App\Enums\BidStatus;
use App\Mail\BidVerificationMail;
use App\Mail\OutbidRebidMail;
use App\Mail\VerifiedBidNotificationMail;
use App\Models\Bid;
use App\Models\Domain;
use App\Models\RebidToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BidService
{
    public function highestAcceptedAmount(Domain $domain): int
    {
        return (int) ($domain->bids()->accepted()->max('amount') ?? 0);
    }

    /**
     * @param  array{name: string, email: string, amount: int}  $data
     */
    public function placeOffer(Domain $domain, array $data): Bid
    {
        $highest = $this->highestAcceptedAmount($domain);

        if ($data['amount'] <= $highest) {
            throw ValidationException::withMessages([
                'amount' => 'Bid must be higher than the current highest bid.',
            ]);
        }

        $bid = $domain->bids()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'amount' => $data['amount'],
            'status' => BidStatus::Pending,
            'verification_token' => Str::random(64),
        ]);

        try {
            Mail::to($bid->email)->send(new BidVerificationMail($bid));
        } catch (\Throwable $e) {
            Log::error('Failed to send bid verification email.', [
                'bid_id' => $bid->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $bid;
    }

    public function verify(string $token): ?Bid
    {
        return DB::transaction(function () use ($token): ?Bid {
            $bid = Bid::query()
                ->where('verification_token', $token)
                ->whereNull('email_verified_at')
                ->lockForUpdate()
                ->first();

            if ($bid === null) {
                return null;
            }

            $bid->forceFill([
                'email_verified_at' => now(),
                'verification_token' => null,
            ])->save();

            $bid->load('domain');

            $recipient = $bid->domain->notificationRecipient();

            if ($recipient !== '') {
                try {
                    Mail::to($recipient)->send(new VerifiedBidNotificationMail($bid));
                } catch (\Throwable $e) {
                    Log::error('Failed to send verified bid notification.', [
                        'bid_id' => $bid->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $bid;
        });
    }

    public function accept(Bid $bid): Bid
    {
        if (! $bid->isVerified() || $bid->status !== BidStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only verified pending bids can be accepted.',
            ]);
        }

        $bid->update(['status' => BidStatus::Accepted]);
        $bid->load('domain');

        $this->notifyOutbidVerifiedBidders($bid);

        return $bid;
    }

    /**
     * @param  array{amount: int, name?: string}  $data
     */
    public function placeVerifiedOffer(RebidToken $token, array $data): Bid
    {
        return DB::transaction(function () use ($token, $data): Bid {
            $token = RebidToken::query()
                ->whereKey($token->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $token->isValid()) {
                throw ValidationException::withMessages([
                    'token' => 'This rebid link is invalid or has expired.',
                ]);
            }

            $domain = $token->domain()->firstOrFail();
            $highest = $this->highestAcceptedAmount($domain);

            if ($data['amount'] <= $highest) {
                throw ValidationException::withMessages([
                    'amount' => 'Bid must be higher than the current highest bid.',
                ]);
            }

            $bid = $domain->bids()->create([
                'name' => $data['name'] ?? $token->name,
                'email' => $token->email,
                'amount' => $data['amount'],
                'status' => BidStatus::Pending,
                'email_verified_at' => now(),
                'verification_token' => null,
            ]);

            $token->forceFill(['used_at' => now()])->save();

            $recipient = $domain->notificationRecipient();

            if ($recipient !== '') {
                try {
                    Mail::to($recipient)->send(new VerifiedBidNotificationMail($bid));
                } catch (\Throwable $e) {
                    Log::error('Failed to send verified bid notification for rebid.', [
                        'bid_id' => $bid->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $bid;
        });
    }

    protected function notifyOutbidVerifiedBidders(Bid $acceptedBid): void
    {
        $bidders = $this->outbidVerifiedBidders($acceptedBid);

        foreach ($bidders as $bidder) {
            // Keep an existing unused invite so bidders aren't re-emailed on
            // every subsequent accept — their link already shows the new high bid.
            $existingToken = RebidToken::query()
                ->where('domain_id', $acceptedBid->domain_id)
                ->where('email', $bidder->email)
                ->valid()
                ->first();

            if ($existingToken !== null) {
                continue;
            }

            $rebidToken = RebidToken::query()->create([
                'domain_id' => $acceptedBid->domain_id,
                'triggered_by_bid_id' => $acceptedBid->id,
                'name' => $bidder->name,
                'email' => $bidder->email,
                'token' => Str::random(64),
                'expires_at' => now()->addHours((int) config('bids.rebid_token_hours', 24)),
            ]);

            try {
                Mail::to($bidder->email)->send(new OutbidRebidMail($rebidToken, $acceptedBid));
            } catch (\Throwable $e) {
                Log::error('Failed to send outbid rebid email.', [
                    'email' => $bidder->email,
                    'accepted_bid_id' => $acceptedBid->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return Collection<int, Bid>
     */
    protected function outbidVerifiedBidders(Bid $acceptedBid): Collection
    {
        return Bid::query()
            ->where('domain_id', $acceptedBid->domain_id)
            ->verified()
            ->where('amount', '<', $acceptedBid->amount)
            ->where('email', '!=', $acceptedBid->email)
            ->orderByDesc('amount')
            ->get()
            ->unique(fn (Bid $bid): string => strtolower($bid->email))
            ->values();
    }
}
