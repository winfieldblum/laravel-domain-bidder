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

test('livewire update endpoint is not blocked by domain resolution on the primary host', function () {
    $uri = app('livewire')->getUpdateUri();

    $status = $this->post('http://domain-bidder.ddev.site'.$uri, [], [
        'X-Livewire' => '1',
    ])->status();

    expect($status)->not->toBe(404);
});
