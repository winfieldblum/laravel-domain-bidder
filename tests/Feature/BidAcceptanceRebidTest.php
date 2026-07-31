<?php

use App\Enums\BidStatus;
use App\Mail\OutbidRebidMail;
use App\Mail\VerifiedBidNotificationMail;
use App\Models\Bid;
use App\Models\Domain;
use App\Models\RebidToken;
use App\Services\BidService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->domain = Domain::factory()->create([
        'hostname' => 'agentic.io',
        'mail_from_address' => 'noreply@agentic.io',
        'mail_from_name' => 'Agentic.io',
        'notification_email' => 'owner@example.com',
    ]);
});

test('accepting a bid emails lower verified bidders with a rebid token', function () {
    $lower = Bid::factory()->for($this->domain)->verified()->create([
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'amount' => 10000,
        'status' => BidStatus::Pending,
    ]);

    Bid::factory()->for($this->domain)->verified()->create([
        'email' => 'alex@example.com',
        'amount' => 8000,
        'status' => BidStatus::Pending,
    ]);

    $winner = Bid::factory()->for($this->domain)->verified()->create([
        'email' => 'winner@example.com',
        'amount' => 15000,
        'status' => BidStatus::Pending,
    ]);

    app(BidService::class)->accept($winner);

    expect($winner->fresh()->status)->toBe(BidStatus::Accepted);

    $token = RebidToken::query()->where('email', 'alex@example.com')->first();

    expect($token)
        ->not->toBeNull()
        ->name->toBe('Alex Rivera')
        ->used_at->toBeNull()
        ->and($token->expires_at->greaterThan(now()->addHours(23)))->toBeTrue();

    expect(RebidToken::query()->where('email', 'alex@example.com')->count())->toBe(1);
    expect(RebidToken::query()->where('email', 'winner@example.com')->count())->toBe(0);

    Mail::assertQueued(OutbidRebidMail::class, function (OutbidRebidMail $mail) use ($lower) {
        return $mail->hasTo($lower->email)
            && $mail->envelope()->from->address === 'noreply@agentic.io';
    });
});

test('verified rebid link creates an already-verified bid and consumes the token', function () {
    $accepted = Bid::factory()->for($this->domain)->accepted()->create(['amount' => 12000]);

    $token = RebidToken::factory()->create([
        'domain_id' => $this->domain->id,
        'triggered_by_bid_id' => $accepted->id,
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'token' => 'validrebidtoken123',
        'expires_at' => now()->addDay(),
    ]);

    $this->post('http://agentic.io/offer/verified/validrebidtoken123', [
        'name' => 'Alex Rivera',
        'amount' => 13000,
    ])->assertRedirect('http://agentic.io/offer/verified/validrebidtoken123');

    $bid = Bid::query()
        ->where('email', 'alex@example.com')
        ->where('amount', 13000)
        ->first();

    expect($bid)
        ->not->toBeNull()
        ->status->toBe(BidStatus::Pending)
        ->email_verified_at->not->toBeNull()
        ->verification_token->toBeNull();

    expect($token->fresh()->used_at)->not->toBeNull();

    Mail::assertQueued(VerifiedBidNotificationMail::class);
});

test('expired rebid tokens cannot place a verified bid', function () {
    $accepted = Bid::factory()->for($this->domain)->accepted()->create(['amount' => 12000]);

    RebidToken::factory()->expired()->create([
        'domain_id' => $this->domain->id,
        'triggered_by_bid_id' => $accepted->id,
        'email' => 'alex@example.com',
        'token' => 'expiredtoken123456',
    ]);

    $this->from('http://agentic.io/offer/verified/expiredtoken123456')
        ->post('http://agentic.io/offer/verified/expiredtoken123456', [
            'name' => 'Alex Rivera',
            'amount' => 13000,
        ])
        ->assertSessionHasErrors('token');

    expect(Bid::query()->where('email', 'alex@example.com')->count())->toBe(0);
});

test('verified rebid must beat the current highest accepted bid', function () {
    $accepted = Bid::factory()->for($this->domain)->accepted()->create(['amount' => 12000]);

    RebidToken::factory()->create([
        'domain_id' => $this->domain->id,
        'triggered_by_bid_id' => $accepted->id,
        'email' => 'alex@example.com',
        'token' => 'toolowtoken1234567',
    ]);

    $this->from('http://agentic.io/offer/verified/toolowtoken1234567')
        ->post('http://agentic.io/offer/verified/toolowtoken1234567', [
            'name' => 'Alex Rivera',
            'amount' => 12000,
        ])
        ->assertSessionHasErrors('amount');
});

test('a later accept only emails newly outbid bidders who lack a valid invite', function () {
    $bidderA = Bid::factory()->for($this->domain)->verified()->create([
        'name' => 'Bidder A',
        'email' => 'a@example.com',
        'amount' => 1000,
        'status' => BidStatus::Pending,
    ]);

    $bidderB = Bid::factory()->for($this->domain)->verified()->create([
        'name' => 'Bidder B',
        'email' => 'b@example.com',
        'amount' => 800,
        'status' => BidStatus::Pending,
    ]);

    $bidderC = Bid::factory()->for($this->domain)->verified()->create([
        'name' => 'Bidder C',
        'email' => 'c@example.com',
        'amount' => 700,
        'status' => BidStatus::Pending,
    ]);

    app(BidService::class)->accept($bidderA);

    $tokenB = RebidToken::query()->where('email', 'b@example.com')->first();
    $tokenC = RebidToken::query()->where('email', 'c@example.com')->first();

    expect($tokenB)->not->toBeNull();
    expect($tokenC)->not->toBeNull();
    expect(RebidToken::query()->where('email', 'a@example.com')->count())->toBe(0);

    Mail::assertQueued(OutbidRebidMail::class, 2);

    // B uses their invite to place a higher verified bid, which is then accepted.
    $biddersNewBid = app(BidService::class)->placeVerifiedOffer($tokenB, [
        'name' => 'Bidder B',
        'amount' => 1500,
    ]);

    Mail::fake();

    app(BidService::class)->accept($biddersNewBid);

    // Only previous winner A is newly outbid without an invite.
    expect(RebidToken::query()->where('email', 'a@example.com')->count())->toBe(1);
    expect(RebidToken::query()->where('email', 'c@example.com')->valid()->count())->toBe(1);
    expect($tokenC->fresh()->token)->toBe($tokenC->token);
    expect(RebidToken::query()->where('email', 'b@example.com')->valid()->count())->toBe(0);

    Mail::assertQueued(OutbidRebidMail::class, 1);
    Mail::assertQueued(OutbidRebidMail::class, function (OutbidRebidMail $mail) {
        return $mail->hasTo('a@example.com');
    });

    // C's existing invite still works and reflects B's accepted amount.
    $this->get('http://agentic.io/offer/verified/'.$tokenC->token)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('domains/VerifiedOffer')
            ->where('valid', true)
            ->where('highestBid', 1500)
            ->where('minimumBid', 1600)
        );
});
