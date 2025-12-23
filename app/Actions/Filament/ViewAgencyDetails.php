<?php

namespace App\Actions\Filament;

use App\Services\ChatService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

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

                        Actions::make([
                            Action::make('newChat')
                                ->label('Conversar')
                                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                                ->color('secondary')
                                ->action(function ($record) {

                                    $chat = ChatService::createChat(
                                        [
                                            $record->id,
                                        ]

                                    );

                                    if (is_array($chat) && isset($chat['error'])) {
                                        Notification::make()
                                            ->title('Erro')
                                            ->body($chat['error'])
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    return redirect()->route('chats.show', ['chat' => $chat]);
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
