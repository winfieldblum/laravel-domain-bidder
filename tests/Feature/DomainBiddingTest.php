<?php

use App\Enums\BidStatus;
use App\Mail\BidVerificationMail;
use App\Mail\VerifiedBidNotificationMail;
use App\Models\Bid;
use App\Models\Domain;
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

function domainUrl(string $path = '/', ?Domain $domain = null): string
{
    $domain ??= test()->domain;

    return 'http://'.$domain->hostname.$path;
}

test('unknown host returns 404', function () {
    $this->get('http://unknown.test/')
        ->assertNotFound();
});

test('admin routes are only available on the primary app domain', function () {
    config(['app.url' => 'https://domain-bidder.ddev.site']);

    $this->get('http://domain-bidder.ddev.site/admin/login')
        ->assertOk();
});

test('home renders for an active domain host', function () {
    $this->get(domainUrl('/'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('domains/Home')
            ->where('domain.hostname', 'agentic.io')
            ->where('highestBid', 0)
            ->has('otherDomains', 0)
        );
});

test('home lists other active domains for sale', function () {
    $other = Domain::factory()->create([
        'hostname' => 'onlinescrums.com',
        'display_name' => 'Online Scrums',
        'tagline' => 'Own the conversation.',
        'is_active' => true,
    ]);

    Domain::factory()->create([
        'hostname' => 'inactive.test',
        'is_active' => false,
    ]);

    $this->get(domainUrl('/'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('domains/Home')
            ->has('otherDomains', 1)
            ->where('otherDomains.0.hostname', 'onlinescrums.com')
            ->where('otherDomains.0.display_name', 'Online Scrums')
            ->where('otherDomains.0.url', 'http://onlinescrums.com')
        );

    $this->get('http://'.$other->hostname.'.ddev.site/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('otherDomains.0.hostname', 'agentic.io')
            ->where('otherDomains.0.url', 'http://agentic.io.ddev.site')
        );
});

test('ddev-style hostnames resolve to the bare selling hostname', function () {
    $this->get('http://agentic.io.ddev.site/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('domains/Home')
            ->where('domain.hostname', 'agentic.io')
        );
});

test('inactive domain host returns 404', function () {
    $this->domain->update(['is_active' => false]);

    $this->get(domainUrl('/'))
        ->assertNotFound();
});

test('highest bid only counts accepted verified bids for that domain', function () {
    Bid::factory()->for($this->domain)->verified()->create(['amount' => 5000, 'status' => BidStatus::Pending]);
    Bid::factory()->for($this->domain)->accepted()->create(['amount' => 12000]);
    Bid::factory()->for($this->domain)->accepted()->create(['amount' => 8000]);
    Bid::factory()->accepted()->create(['amount' => 50000]); // other domain

    $this->get(domainUrl('/'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('highestBid', 12000));
});

test('placing an offer creates an unverified bid and queues verification mail from the domain', function () {
    $this->post(domainUrl('/offer'), [
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'amount' => 15000,
    ])->assertRedirect('http://'.$this->domain->hostname.'/offer');

    $bid = Bid::query()->first();

    expect($bid)
        ->not->toBeNull()
        ->name->toBe('Alex Rivera')
        ->email_verified_at->toBeNull()
        ->verification_token->not->toBeNull()
        ->status->toBe(BidStatus::Pending);

    Mail::assertQueued(BidVerificationMail::class, function (BidVerificationMail $mail) use ($bid) {
        return $mail->bid->is($bid)
            && $mail->envelope()->from->address === 'noreply@agentic.io';
    });
});

test('offer must beat the highest accepted bid', function () {
    Bid::factory()->for($this->domain)->accepted()->create(['amount' => 10000]);

    $this->from(domainUrl('/offer'))
        ->post(domainUrl('/offer'), [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'amount' => 10000,
        ])
        ->assertSessionHasErrors('amount');

    expect(Bid::query()->whereNull('email_verified_at')->count())->toBe(0);
});

test('verifying a bid marks it verified and notifies the domain owner', function () {
    $bid = Bid::factory()->for($this->domain)->create([
        'verification_token' => 'validtoken123abc',
        'amount' => 22000,
    ]);

    $this->get(domainUrl('/verify/validtoken123abc'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('domains/Verify')
            ->where('success', true)
            ->where('amount', 22000)
        );

    expect($bid->fresh())
        ->email_verified_at->not->toBeNull()
        ->verification_token->toBeNull();

    Mail::assertQueued(VerifiedBidNotificationMail::class, function (VerifiedBidNotificationMail $mail) {
        return $mail->envelope()->from->address === 'noreply@agentic.io'
            && $mail->hasTo('owner@example.com');
    });
});

test('verification token can only be used once', function () {
    Bid::factory()->for($this->domain)->create([
        'verification_token' => 'onetimetokenabc',
    ]);

    $this->get(domainUrl('/verify/onetimetokenabc'))->assertOk();

    $this->get(domainUrl('/verify/onetimetokenabc'))
        ->assertInertia(fn ($page) => $page->where('success', false));
});
