<?php

namespace App\Actions\Filament;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ViewProposal
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewProposal')
            ->label('Ver Proposta')
            ->slideOver()
            ->modalWidth('xl')
            ->schema(fn($record) => [
                Section::make('Informações da Agência')->schema([
                    ImageEntry::make('agency.avatar_url')->circular(),
                    TextEntry::make('agency.name')->label('Agência'),
                    TextEntry::make('announcement.name')->label('Campanha'),

                    Actions::make([
                        Action::make('viewInfluencers')
                            ->label('Ver Influenciadores')
                            ->button()
                            ->icon('heroicon-o-user-group')
                            ->color('primary')
                            ->url(function ($record) {
                                return route('filament.admin.resources.influencers.index', [
                                    'search' => $record->name,
                                ]);
                            }),
                        ChatAction::make(),
                        AcceptProposal::make()

                    ])->columnSpanFull(),
                ])
            ]);
    }
}
