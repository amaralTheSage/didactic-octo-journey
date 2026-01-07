<?php

namespace App\Actions\Filament;

use App\Models\User;
use App\Services\ChatService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewAgencyDetails
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewAgencyDetails')
            ->label('Detalhes')
            ->slideOver()
            ->modalWidth('xl')
            ->schema([
                Section::make('Detalhes da Agência')
                    ->schema([
                        Group::make()->columns(2)->schema([
                            ImageEntry::make('avatar_url')->hiddenLabel()
                                ->circular()
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name))
                                ->columnSpanFull(),
                            TextEntry::make('name')
                                ->label('Nome')->columnSpan(2),
                        ]),
                        TextEntry::make('role')->hiddenLabel()
                            ->badge()->alignRight()
                            ->color('success'),

                        TextEntry::make('bio')
                            ->label('Bio')
                            ->columnSpanFull()
                            ->markdown(),

                        Actions::make([
                            Action::make('newChat')
                                ->label('Conversar')
                                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                                ->color('secondary')
                                ->url(fn (User $record) => route('chats.create', ['users' => [$record->id]]))
                                ->openUrlInNewTab()
                                ->visible(function (User $record) {
                                    return ChatService::validateChatPermission(Auth::user(), $record)['allowed'];
                                }),

                            Action::make('viewInfluencers')
                                ->label('Ver Influenciadores desta Agência')
                                ->button()
                                ->icon('heroicon-o-user-group')
                                ->color('primary')
                                ->url(function ($record) {
                                    return route('filament.admin.resources.influencers.index', [
                                        'search' => $record->name,
                                    ]);
                                }),

                            // ChatAction::make() BUG-> não funcionando dentro das ViewDetails
                        ])->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
