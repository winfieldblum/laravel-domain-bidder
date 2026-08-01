<?php

use App\Models\User;

beforeEach(function () {
    config([
        'app.url' => 'https://domain-bidder.ddev.site',
        // Filament 5 only enforces FilamentUser outside local
        'app.env' => 'production',
    ]);
});

test('authenticated users can access the filament admin panel in production', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('http://domain-bidder.ddev.site/admin')
        ->assertOk();
});
