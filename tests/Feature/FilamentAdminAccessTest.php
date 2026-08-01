<?php

use App\Filament\Widgets\DomainPerformanceTable;
use App\Filament\Widgets\DomainStatsOverview;
use App\Filament\Widgets\ImpressionTrendChart;
use App\Models\User;
use Filament\Facades\Filament;

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

test('analytics dashboard widgets are registered on the admin panel', function () {
    $widgets = Filament::getCurrentOrDefaultPanel()->getWidgets();

    expect($widgets)->toContain(DomainStatsOverview::class)
        ->and($widgets)->toContain(ImpressionTrendChart::class)
        ->and($widgets)->toContain(DomainPerformanceTable::class);
});
