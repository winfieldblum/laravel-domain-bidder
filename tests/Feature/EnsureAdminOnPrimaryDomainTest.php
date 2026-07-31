<?php

use App\Models\Domain;

beforeEach(function () {
    config(['app.url' => 'https://domain-bidder.ddev.site']);

    Domain::factory()->create([
        'hostname' => 'agentic.io',
    ]);
});

test('admin is available on the primary app domain', function () {
    $this->get('http://domain-bidder.ddev.site/admin/login')
        ->assertOk();
});

test('admin is not available on a selling domain', function () {
    $this->get('http://agentic.io/admin/login')
        ->assertNotFound();
});

test('admin is not available on a ddev-style selling domain', function () {
    $this->get('http://agentic.io.ddev.site/admin/login')
        ->assertNotFound();
});
