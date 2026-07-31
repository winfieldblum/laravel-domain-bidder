<?php

namespace App\Filament\Resources\Bids\Tables;

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Services\BidService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('domain.hostname')
                    ->label('Domain')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('USD', divideBy: 1)
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn (Bid $record): bool => $record->isVerified()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BidStatus $state): string => $state->label())
                    ->color(fn (BidStatus $state): string => match ($state) {
                        BidStatus::Accepted => 'success',
                        BidStatus::Rejected => 'danger',
                        BidStatus::Pending => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('domain_id')
                    ->label('Domain')
                    ->relationship('domain', 'hostname')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(collect(BidStatus::cases())->mapWithKeys(
                        fn (BidStatus $status): array => [$status->value => $status->label()]
                    )),
                TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    )
                    ->default(true),
            ])
            ->recordActions([
                Action::make('accept')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Bid $record): bool => $record->isVerified() && $record->status === BidStatus::Pending)
                    ->action(function (Bid $record): void {
                        app(BidService::class)->accept($record);
                        Notification::make()->title('Bid accepted')->success()->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Bid $record): bool => $record->isVerified() && $record->status === BidStatus::Pending)
                    ->action(function (Bid $record): void {
                        $record->update(['status' => BidStatus::Rejected]);
                        Notification::make()->title('Bid rejected')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
