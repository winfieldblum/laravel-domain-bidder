<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Domains\DomainResource;
use App\Models\Domain;
use App\Services\DomainMetrics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DomainPerformanceTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Domain performance';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => app(DomainMetrics::class)->activeDomainsWithPerformance())
            ->columns([
                TextColumn::make('hostname')
                    ->label('Domain')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Domain $record): string => DomainResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('impressions_7d')
                    ->label('Impressions (7d)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impressions_30d')
                    ->label('Impressions (30d)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impressions_all')
                    ->label('Impressions (all)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unique_bidders_count')
                    ->label('Unique bidders')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('highest_accepted')
                    ->label('Highest accepted')
                    ->money('USD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('pending_bids_count')
                    ->label('Pending')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accepted_bids_count')
                    ->label('Accepted')
                    ->numeric()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
