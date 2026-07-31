<?php

use App\Models\Domain;

test('domain:add creates an active domain with default from address', function () {
    $this->artisan('domain:add', [
        'hostname' => 'https://WWW.Example.io/',
        '--tagline' => 'A great domain',
        '--notify' => 'ops@example.com',
    ])->assertSuccessful();

    $domain = Domain::query()->where('hostname', 'example.io')->first();

    expect($domain)
        ->not->toBeNull()
        ->is_active->toBeTrue()
        ->tagline->toBe('A great domain')
        ->mail_from_address->toBe('noreply@example.io')
        ->notification_email->toBe('ops@example.com');
});

test('domain:add fails when hostname already exists', function () {
    Domain::factory()->create(['hostname' => 'agentic.io']);

    $this->artisan('domain:add', ['hostname' => 'agentic.io'])
        ->assertFailed();
});
