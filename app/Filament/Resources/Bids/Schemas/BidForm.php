<?php

namespace App\Filament\Resources\Bids\Schemas;

use App\Enums\BidStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('domain_id')
                    ->relationship('domain', 'hostname')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(100)
                    ->prefix('$'),
                Select::make('status')
                    ->options(collect(BidStatus::cases())->mapWithKeys(
                        fn (BidStatus $status): array => [$status->value => $status->label()]
                    ))
                    ->required(),
            ]);
    }
}
