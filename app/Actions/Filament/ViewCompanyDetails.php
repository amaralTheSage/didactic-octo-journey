<?php

namespace App\Actions\Filament;

use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
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
            ->modalWidth('2xl')
            ->schema([
                Section::make('Detalhes da Agência')
                    ->schema([
                        Group::make()->columns(2)->schema([
                            ImageEntry::make('avatar_url')
                                ->label('Avatar')
                                ->circular()
                                ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name))
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

                        // Actions::make([
                        //     Action::make('viewInfluencers')
                        //         ->label('Ver Influenciadores desta Agência')
                        //         ->button()
                        //         ->icon('heroicon-o-user-group')
                        //         ->color('primary')
                        //         ->url(function ($record) {
                        //             return route('filament.admin.resources.influencers.index', [
                        //                 'search' => $record->name
                        //             ]);
                        //         })
                        // ])->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
