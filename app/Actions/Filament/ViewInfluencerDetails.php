<?php

namespace App\Actions\Filament;

use App\UserRoles;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ViewInfluencerDetails
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewInfluencerDetails')
            ->label('Detalhes')
            ->slideOver()
            ->modalWidth('2xl')
            ->schema([
                Section::make('Informações do Influencer')
                    ->schema([
                        Group::make()->columns(2)->schema([
                            ImageEntry::make('avatar')
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

                        TextEntry::make('subcategories.title')
                            ->label('Categorias')
                            ->badge()
                            ->separator(',')
                            ->columnSpanFull()->visible(function ($record) {
                                return sizeof($record->subcategories) > 0;
                            }),
                    ])
                    ->columns(2),


                Section::make('Estatísticas Gerais')
                    ->schema([
                        TextEntry::make('total_followers')
                            ->label('Total de Seguidores')
                            ->state(function ($record) {
                                $info = $record->influencer_info;
                                if (!$info) return 0;

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
                            ->icon(Heroicon::OutlinedBuildingStorefront),

                        // TextEntry::make('created_at')
                        //     ->label('Membro desde')
                        //     ->date('d/m/Y')
                        //     ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(3),


                Section::make('Redes Sociais')
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
                    ])
                    ->columns(2),


            ]);
    }
}
