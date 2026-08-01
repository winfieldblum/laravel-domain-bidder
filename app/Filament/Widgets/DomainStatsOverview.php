<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Domains\DomainResource;
use App\Models\Domain;
use App\Services\DomainMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DomainStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $domains = app(DomainMetrics::class)->activeDomainsWithPerformance()->get();

        return $domains
            ->flatMap(fn (Domain $domain): array => [
                Stat::make('Impressions today', number_format((int) $domain->impressions_today))
                    ->description($domain->hostname)
                    ->url(DomainResource::getUrl('edit', ['record' => $domain])),
                Stat::make('Impressions (7d)', number_format((int) $domain->impressions_7d))
                    ->description($domain->hostname)
                    ->url(DomainResource::getUrl('edit', ['record' => $domain])),
                Stat::make('Pending bids', number_format((int) $domain->pending_bids_count))
                    ->description($domain->hostname)
                    ->url(DomainResource::getUrl('edit', ['record' => $domain])),
                Stat::make('Highest accepted', '$'.number_format((int) $domain->highest_accepted))
                    ->description($domain->hostname)
                    ->url(DomainResource::getUrl('edit', ['record' => $domain])),
            ])
            ->all();
    }
}
