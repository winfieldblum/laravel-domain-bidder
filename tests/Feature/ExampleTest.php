<?php

use App\Models\Domain;

test('returns a successful response for a resolved domain host', function () {
    $domain = Domain::factory()->create(['hostname' => 'agentic.io']);

    $this->get('http://'.$domain->hostname.'/')
        ->assertOk();
});
