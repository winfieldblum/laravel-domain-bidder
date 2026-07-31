<?php

namespace App\Filament\Resources\Domains\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('icon')
                    ->options([
                        'Globe' => 'Globe',
                        'Shield' => 'Shield',
                        'TrendingUp' => 'Trending Up',
                        'Zap' => 'Zap',
                        'Users' => 'Users',
                        'CheckCircle2' => 'Check Circle',
                        'Sparkles' => 'Sparkles',
                        'Rocket' => 'Rocket',
                        'Building2' => 'Building',
                        'Laptop' => 'Laptop',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('color')
                    ->placeholder('text-blue-500')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->sortable(),
                TextColumn::make('icon'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('color')
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
