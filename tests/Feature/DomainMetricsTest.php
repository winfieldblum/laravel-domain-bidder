<?php

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Models\Domain;
use App\Models\DomainImpression;
use App\Services\DomainMetrics;

beforeEach(function () {
    $this->domain = Domain::factory()->create([
        'hostname' => 'agentic.io',
    ]);

    $this->metrics = app(DomainMetrics::class);
});

test('unique bidder count uses distinct emails across all bid statuses', function () {
    Bid::factory()->for($this->domain)->create([
        'email' => 'alice@example.com',
        'status' => BidStatus::Pending,
    ]);
    Bid::factory()->for($this->domain)->accepted()->create([
        'email' => 'alice@example.com',
        'amount' => 5000,
    ]);
    Bid::factory()->for($this->domain)->rejected()->create([
        'email' => 'bob@example.com',
        'amount' => 4000,
    ]);
    Bid::factory()->create([
        'email' => 'carol@example.com',
    ]);

    expect($this->metrics->uniqueBidderCount($this->domain))->toBe(2);
});

test('pending and accepted counts reflect bid status for the domain', function () {
    Bid::factory()->for($this->domain)->count(3)->create(['status' => BidStatus::Pending]);
    Bid::factory()->for($this->domain)->accepted()->count(2)->create();
    Bid::factory()->for($this->domain)->rejected()->create();
    Bid::factory()->accepted()->create();

    expect($this->metrics->pendingCount($this->domain))->toBe(3)
        ->and($this->metrics->acceptedCount($this->domain))->toBe(2)
        ->and($this->metrics->highestAcceptedAmount($this->domain))->toBeInt();
});

test('impression totals cover today and the last seven days per domain', function () {
    $other = Domain::factory()->create(['hostname' => 'onlinescrums.com']);

    DomainImpression::factory()->for($this->domain)->create([
        'date' => today(),
        'count' => 5,
    ]);
    DomainImpression::factory()->for($this->domain)->create([
        'date' => today()->subDays(3),
        'count' => 7,
    ]);
    DomainImpression::factory()->for($this->domain)->create([
        'date' => today()->subDays(10),
        'count' => 100,
    ]);
    DomainImpression::factory()->for($other)->create([
        'date' => today(),
        'count' => 50,
    ]);

    expect($this->metrics->impressionsToday($this->domain))->toBe(5)
        ->and($this->metrics->impressionsLastDays(7, $this->domain))->toBe(12)
        ->and($this->metrics->impressionsToday($other))->toBe(50)
        ->and($this->metrics->impressionsToday())->toBe(55);
});

test('daily impression totals by domain return a series per active domain', function () {
    $other = Domain::factory()->create(['hostname' => 'onlinescrums.com']);
    Domain::factory()->inactive()->create(['hostname' => 'inactive.test']);

    DomainImpression::factory()->for($this->domain)->create([
        'date' => today(),
        'count' => 3,
    ]);
    DomainImpression::factory()->for($other)->create([
        'date' => today()->subDay(),
        'count' => 8,
    ]);

    $byDomain = $this->metrics->dailyImpressionTotalsByDomain(
        now()->subDays(6)->startOfDay(),
        now()->endOfDay(),
    );

    expect($byDomain->keys()->all())->toBe(['agentic.io', 'onlinescrums.com'])
        ->and($byDomain['agentic.io'][today()->toDateString()])->toBe(3)
        ->and($byDomain['onlinescrums.com'][today()->subDay()->toDateString()])->toBe(8)
        ->and($byDomain['agentic.io'])->toHaveCount(7);
});

test('active domains with performance aggregates avoid per-domain N+1 counts', function () {
    $other = Domain::factory()->create(['hostname' => 'onlinescrums.com']);

    DomainImpression::factory()->for($this->domain)->create([
        'date' => today(),
        'count' => 4,
    ]);
    DomainImpression::factory()->for($other)->create([
        'date' => today()->subDays(2),
        'count' => 9,
    ]);

    Bid::factory()->for($this->domain)->create(['email' => 'a@example.com']);
    Bid::factory()->for($this->domain)->accepted()->create([
        'email' => 'b@example.com',
        'amount' => 12000,
    ]);
    Bid::factory()->for($other)->accepted()->create([
        'email' => 'c@example.com',
        'amount' => 8000,
    ]);

    $rows = $this->metrics->activeDomainsWithPerformance()->get()->keyBy('hostname');

    expect($rows)->toHaveCount(2)
        ->and((int) $rows['agentic.io']->impressions_today)->toBe(4)
        ->and((int) $rows['agentic.io']->impressions_7d)->toBe(4)
        ->and((int) $rows['agentic.io']->unique_bidders_count)->toBe(2)
        ->and((int) $rows['agentic.io']->highest_accepted)->toBe(12000)
        ->and((int) $rows['agentic.io']->pending_bids_count)->toBe(1)
        ->and((int) $rows['agentic.io']->accepted_bids_count)->toBe(1)
        ->and((int) $rows['onlinescrums.com']->impressions_7d)->toBe(9)
        ->and((int) $rows['onlinescrums.com']->highest_accepted)->toBe(8000);
});
