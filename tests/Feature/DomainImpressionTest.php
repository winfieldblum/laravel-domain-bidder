<?php

use App\Models\Domain;
use App\Models\DomainImpression;
use App\Services\ImpressionService;

beforeEach(function () {
    $this->domain = Domain::factory()->create([
        'hostname' => 'agentic.io',
    ]);
});

test('visiting the domain home page increments today\'s impression rollup', function () {
    $this->get('http://'.$this->domain->hostname.'/')
        ->assertOk();

    $impression = DomainImpression::query()
        ->whereBelongsTo($this->domain)
        ->whereDate('date', today())
        ->first();

    expect($impression)->not->toBeNull()
        ->and($impression->count)->toBe(1);
});

test('a second home visit increments the same day\'s rollup again', function () {
    $this->get('http://'.$this->domain->hostname.'/')->assertOk();
    $this->get('http://'.$this->domain->hostname.'/')->assertOk();

    expect(
        DomainImpression::query()
            ->whereBelongsTo($this->domain)
            ->whereDate('date', today())
            ->value('count')
    )->toBe(2);
});

test('impressions for one domain do not affect another domain', function () {
    $other = Domain::factory()->create([
        'hostname' => 'onlinescrums.com',
    ]);

    $this->get('http://'.$this->domain->hostname.'/')->assertOk();
    $this->get('http://'.$other->hostname.'/')->assertOk();
    $this->get('http://'.$other->hostname.'/')->assertOk();

    expect(
        DomainImpression::query()
            ->whereBelongsTo($this->domain)
            ->whereDate('date', today())
            ->value('count')
    )->toBe(1);

    expect(
        DomainImpression::query()
            ->whereBelongsTo($other)
            ->whereDate('date', today())
            ->value('count')
    )->toBe(2);
});

test('bot user agents are not recorded as impressions', function () {
    $this->get('http://'.$this->domain->hostname.'/', [
        'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
    ])->assertOk();

    expect(
        DomainImpression::query()
            ->whereBelongsTo($this->domain)
            ->exists()
    )->toBeFalse();
});

test('impression service records atomically for the same day', function () {
    $service = app(ImpressionService::class);

    $service->record($this->domain, 'Mozilla/5.0');
    $service->record($this->domain, 'Mozilla/5.0');

    expect(DomainImpression::query()->count())->toBe(1)
        ->and(DomainImpression::query()->value('count'))->toBe(2);
});
