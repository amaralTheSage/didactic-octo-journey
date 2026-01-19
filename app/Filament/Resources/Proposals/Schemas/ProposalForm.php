<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('message')
                    ->columnSpanFull(),
                TextInput::make('proposed_agency_cut')
                    ->numeric()
                    ->default(0),
                Select::make('campaign_id')
                    ->relationship('campaign', 'name')
                    ->required(),
                Select::make('agency_id')
                    ->relationship('agency', 'name')
                    ->required(),
                TextInput::make('agency_approval')
                    ->required()
                    ->default('pending'),
                TextInput::make('company_approval')
                    ->required()
                    ->default('pending'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('n_reels')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('n_stories')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('n_carrousels')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
