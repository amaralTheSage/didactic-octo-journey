<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProposalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('proposed_agency_cut')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('campaign.name')
                    ->label('Campaign'),
                TextEntry::make('agency.name')
                    ->label('Agency'),
                TextEntry::make('agency_approval'),
                TextEntry::make('company_approval'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('n_reels')
                    ->numeric(),
                TextEntry::make('n_stories')
                    ->numeric(),
                TextEntry::make('n_carrousels')
                    ->numeric(),
            ]);
    }
}
