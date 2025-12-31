<?php

namespace App\Actions\Filament;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class ViewCompanyDetails
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewCompanyDetails')
            ->label('Detalhes')
            ->slideOver()
            ->modalWidth('xl')
            ->schema([
                Section::make('Detalhes da Agência')
                    ->schema([
                        Group::make()->columns(2)->schema([
                            ImageEntry::make('avatar_url')
                                ->hiddenLabel()
                                ->circular()
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name))
                                ->columnSpanFull(),
                            TextEntry::make('name')
                                ->label('Nome'),
                        ]),
                        TextEntry::make('role')->hiddenLabel()
                            ->badge()->alignRight()
                            ->color('success'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('bio')
                            ->label('Bio')
                            ->columnSpanFull()
                            ->markdown(),

                        Actions::make([
                            Action::make('viewCampaigns')
                                ->label('Campanhas')
                                ->icon('heroicon-o-presentation-chart-line')
                                ->url(fn ($record) => route('filament.admin.resources.campaign-announcements.index', [
                                    'search' => $record->name,
                                ])),
                            // ChatAction::make() BUG-> não funcionando dentro das ViewDetails

                        ])->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
