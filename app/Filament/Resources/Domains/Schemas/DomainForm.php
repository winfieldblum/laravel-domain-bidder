<?php

namespace App\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Domain')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hostname')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Lowercase hostname without www, e.g. agentic.io'),
                        TextInput::make('display_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('tagline')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('Email')
                    ->columns(2)
                    ->schema([
                        TextInput::make('mail_from_address')
                            ->label('From address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('mail_from_name')
                            ->label('From name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('notification_email')
                            ->label('Admin notification email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Falls back to BID_NOTIFICATION_EMAIL when empty.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
