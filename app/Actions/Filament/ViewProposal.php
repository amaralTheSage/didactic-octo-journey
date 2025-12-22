<?php

namespace App\Actions\Filament;

use App\Services\ChatService;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ViewProposal
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewProposal')
            ->label('Ver Proposta')
            ->slideOver()

            ->modalWidth('xl')
            ->schema(fn ($record) => [
                Section::make('Campanha')
                    ->schema([
                        TextEntry::make('announcement.name')
                            ->label('Campanha')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('announcement.product.name')
                            ->label('Produto'),
                        TextEntry::make('announcement.budget')
                            ->label('Orçamento')
                            ->money('BRL'),
                        TextEntry::make('announcement.category.title')
                            ->label('Categoria')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Proposta')
                    ->schema([
                        Group::make()->schema([

                            ImageEntry::make('agency.avatar_url')
                                ->hiddenLabel()
                                ->circular()
                                ->imageSize(60),

                            TextEntry::make('agency.name')->weight(FontWeight::Bold)
                                ->hiddenLabel()->columnSpan(2)->alignStart(),
                            TextEntry::make('agency.role')->formatStateUsing(fn (UserRoles $state): string => __("roles.$state->value"))
                                ->hiddenLabel()->badge()->alignStart(),

                        ])->columns(5)->columnSpan(2),

                        TextEntry::make('message')
                            ->label('Mensagem')
                            ->columnSpanFull(),
                        TextEntry::make('proposed_agency_cut')
                            ->label('Parcela Proposta')
                            ->suffix('%')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('created_at')
                            ->label('Enviada em')
                            ->dateTime('d/m/Y H:i'),

                        Action::make('newChat')
                            ->label('Conversar')
                            ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                            ->color('secondary')
                            ->action(function ($record) {

                                $proposalId = $record->id;

                                $chat = \App\Models\Chat::query()
                                    ->where('proposal_id', $proposalId)
                                    ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
                                    ->first();

                                if (! $chat) {
                                    $chat = ChatService::createChat(
                                        [
                                            $record->agency->id,
                                        ],
                                        $proposalId
                                    );

                                    if (is_array($chat) && isset($chat['error'])) {
                                        Notification::make()
                                            ->title('Erro')
                                            ->body($chat['error'])
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                }

                                return redirect()->route('chats.show', ['chat' => $chat]);
                            }),

                    ])
                    ->columns(2),

                Section::make('Influenciadores')
                    ->schema([
                        RepeatableEntry::make('influencers')
                            ->hiddenLabel()
                            ->schema([

                                // ── Header (avatar + basic info)
                                ImageEntry::make('avatar_url')
                                    ->hiddenLabel()
                                    ->circular()
                                    ->imageSize(56),

                                Group::make()
                                    ->schema([
                                        TextEntry::make('name')
                                            ->weight(FontWeight::Bold)
                                            ->hiddenLabel(),

                                        TextEntry::make('role')
                                            ->formatStateUsing(
                                                fn (UserRoles $state): string => __("roles.$state->value")
                                            )
                                            ->hiddenLabel()
                                            ->badge(),

                                    ])
                                    ->columnSpan(3),

                                // ── Prices
                                Group::make()
                                    ->schema([
                                        TextEntry::make('influencer_info.reels_price')
                                            ->label('Reels')
                                            ->money('BRL')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.stories_price')
                                            ->label('Stories')
                                            ->money('BRL')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.carrousel_price')
                                            ->label('Carrossel')
                                            ->money('BRL')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),

                                Section::make('Redes Sociais')->collapsible()->collapsed()
                                    ->schema([
                                        TextEntry::make('influencer_info.instagram_followers')
                                            ->label('Instagram')
                                            ->numeric()
                                            ->icon('heroicon-o-camera')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.youtube_followers')
                                            ->label('YouTube')
                                            ->numeric()
                                            ->icon('heroicon-o-play')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.tiktok_followers')
                                            ->label('TikTok')
                                            ->numeric()
                                            ->icon('heroicon-o-music-note')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.twitter_followers')
                                            ->label('Twitter')
                                            ->numeric()
                                            ->icon('heroicon-o-chat-bubble-left-right')
                                            ->placeholder('-'),

                                        TextEntry::make('influencer_info.facebook_followers')
                                            ->label('Facebook')
                                            ->numeric()
                                            ->icon('heroicon-o-users')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),

                            ])
                            ->columns(5),
                    ]),

                Actions::make([
                    // Action::make('viewAgency')
                    //     ->label('Ver Agência')
                    //     ->icon('heroicon-o-building-storefront')
                    //     ->url(fn($record) => route('filament.admin.resources.agencies.index', [
                    //         'search' => $record->agency->name,
                    //     ])),

                    // Action::make('viewInfluencer')
                    //     ->label('Ver Influenciador')
                    //     ->icon('heroicon-o-user-circle')
                    //     ->visible(fn($record) => isset($record->influencer))
                    //     ->url(fn($record) => route('filament.admin.resources.influencers.index', [
                    //         'search' => $record->influencer?->name,
                    //     ])),

                    EditProposalAction::make(),

                    AcceptProposal::make(),

                    RejectProposal::make(),

                    Action::make('remove_proposal')
                        ->label('Remover Interesse')
                        ->color('danger')->visible(
                            fn ($record, $livewire) => Gate::allows('is_agency')
                                && $record
                                    ->exists()
                        )->button()
                        ->action(
                            fn ($record) => $record->delete()
                        ),
                ]),

            ]);
    }
}
