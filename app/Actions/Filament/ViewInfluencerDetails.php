<?php

namespace App\Actions\Filament;

use App\Services\ChatService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ViewInfluencerDetails
{
    public static function make(): ViewAction
    {

        return ViewAction::make('viewInfluencerDetails')
            ->label('Detalhes')
            ->slideOver()
            ->modalWidth('xl')
            ->schema([
                Section::make('Informações do Influencer')
                    ->schema([
                        Group::make()->columns(2)->schema([
                            ImageEntry::make('avatar_url')
                                ->hiddenLabel()
                                ->circular()
                                ->imageSize(100)
                                ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),

                            TextEntry::make('name')->weight(FontWeight::Bold)
                                ->label('Nome'),

                        ]),

                        TextEntry::make('role')->hiddenLabel()
                            ->badge()->alignRight()
                            ->color('success'),

                        TextEntry::make('bio')
                            ->label('Bio')
                            ->columnSpanFull()
                            ->markdown(),

                        TextEntry::make('subcategories.title')
                            ->label('Categorias')
                            ->badge()
                            ->separator(',')
                            ->columnSpanFull()->visible(function ($record) {
                                return count($record->subcategories) > 0;
                            }),

                        // Group::make([
                        //     Action::make('newChat')
                        //         ->label('Conversar')
                        //         ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                        //         ->color('secondary')
                        //         ->action(function ($record) {

                        //             $chat = ChatService::createChat(
                        //                 [
                        //                     $record->id,
                        //                 ]

                        //             );

                        //             if (is_array($chat) && isset($chat['error'])) {
                        //                 Notification::make()
                        //                     ->title('Erro')
                        //                     ->body($chat['error'])
                        //                     ->danger()
                        //                     ->send();

                        //                 return;
                        //             }

                        //             return redirect()->route('chats.show', ['chat' => $chat]);
                        //         }),

                        // ]),
                    ])
                    ->columns(2),

                Section::make('Estatísticas Gerais')
                    ->schema([
                        TextEntry::make('total_followers')
                            ->label('Total de Seguidores')
                            ->state(function ($record) {
                                $info = $record->influencer_info;
                                if (! $info) {
                                    return 0;
                                }

                                return collect([
                                    $info->instagram_followers,
                                    $info->youtube_followers,
                                    $info->tiktok_followers,
                                    $info->twitter_followers,
                                    $info->facebook_followers,
                                ])->sum();
                            })
                            ->numeric()
                            ->color('success')
                            ->icon('heroicon-o-users'),

                        TextEntry::make('influencer_info.agency.name')
                            ->label('Agência')
                            ->placeholder('Independente')
                            ->icon(Heroicon::OutlinedBuildingStorefront)->columnSpan(2)->url(
                                fn($record) => route('filament.admin.resources.agencies.index', [
                                    'search' => $record->influencer_info->agency->name,
                                    'tableAction' => 'viewAgencyDetails',
                                    'tableActionRecord' => $record->influencer_info->agency->getKey(),
                                ])
                            )->visible(Gate::denies('is_agency')),

                    ])
                    ->columns(3),

                Section::make('Tabela de Preços')
                    ->schema([
                        TextEntry::make('influencer_info.reels_price')
                            ->label('Reels')
                            ->money('BRL')
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.stories_price')
                            ->label('Stories')
                            ->money('BRL')
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.carrousel_price')
                            ->label('Carrossel')
                            ->money('BRL')
                            ->placeholder('Não informado'),
                    ])
                    ->columns([
                        'default' => 2,
                        'sm' => 3,
                        'lg' => 3,
                    ])
                    ->visible(fn($record) => (bool) $record->influencer_info),

                Section::make('Redes Sociais')->columns([
                    'default' => 2,
                    'sm' => 2,
                    'lg' => 2,
                ])
                    ->schema([
                        TextEntry::make('influencer_info.instagram')
                            ->label('Instagram')
                            ->prefix('@')
                            ->url(fn($state) => $state ? "https://instagram.com/{$state}" : null)
                            ->openUrlInNewTab()
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.instagram_followers')
                            ->label('Seguidores')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('influencer_info.youtube')
                            ->label('YouTube')
                            ->prefix('@')
                            ->url(fn($state) => $state ? "https://youtube.com/@{$state}" : null)
                            ->openUrlInNewTab()
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.youtube_followers')
                            ->label('Inscritos')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('influencer_info.tiktok')
                            ->label('TikTok')
                            ->prefix('@')
                            ->url(fn($state) => $state ? "https://tiktok.com/@{$state}" : null)
                            ->openUrlInNewTab()
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.tiktok_followers')
                            ->label('Seguidores')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('influencer_info.twitter')
                            ->label('Twitter')
                            ->prefix('@')
                            ->url(fn($state) => $state ? "https://twitter.com/{$state}" : null)
                            ->openUrlInNewTab()
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.twitter_followers')
                            ->label('Seguidores')
                            ->numeric()
                            ->placeholder('-'),

                        TextEntry::make('influencer_info.facebook')
                            ->label('Facebook')
                            ->prefix('@')
                            ->url(fn($state) => $state ? "https://facebook.com/{$state}" : null)
                            ->openUrlInNewTab()
                            ->placeholder('Não informado'),

                        TextEntry::make('influencer_info.facebook_followers')
                            ->label('Seguidores')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

            ]);
    }
}
