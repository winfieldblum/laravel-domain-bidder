<?php

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Models\Domain;
use Database\Seeders\DomainSeeder;
use Database\Seeders\LegacyAgenticOffersSeeder;

test('imports legacy agentic.io offers into bids', function () {
    $this->seed(DomainSeeder::class);
    $this->seed(LegacyAgenticOffersSeeder::class);

    $domain = Domain::query()->where('hostname', 'agentic.io')->firstOrFail();

    expect(Bid::query()->where('domain_id', $domain->id)->count())->toBe(20);

    $highestAccepted = Bid::query()
        ->where('domain_id', $domain->id)
        ->accepted()
        ->orderByDesc('amount')
        ->first();

    expect($highestAccepted)->not->toBeNull()
        ->and($highestAccepted->amount)->toBe(20400)
        ->and($highestAccepted->email)->toBe('admin@insanedomainer.com')
        ->and($highestAccepted->status)->toBe(BidStatus::Accepted);

    $verified = Bid::query()->findOrFail(10);

    expect($verified->email_verified_at)->not->toBeNull()
        ->and($verified->verification_token)->toBeNull()
        ->and($verified->created_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-01-02 16:41:58');

    $unverified = Bid::query()->findOrFail(26);

    expect($unverified->email_verified_at)->toBeNull()
        ->and($unverified->verification_token)->toBe('80cb5a2edf028a5ac32af4a0de38eed34f3b10b90451affb704bf3d92a0ee0b2')
        ->and($unverified->status)->toBe(BidStatus::Pending);
});

test('legacy offers seeder is idempotent', function () {
    $this->seed(DomainSeeder::class);
    $this->seed(LegacyAgenticOffersSeeder::class);
    $this->seed(LegacyAgenticOffersSeeder::class);

    expect(Bid::query()->count())->toBe(20);
});
