<?php

namespace App\Actions\Filament;

use App\Helpers\ProposedBudgetCalculator;
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
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class ViewProposal
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewProposal')
            ->label('Ver Proposta')
            ->slideOver()

            ->modalWidth('xl')
            ->schema(fn($record) => [
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
                            TextEntry::make('agency.role')->formatStateUsing(fn(UserRoles $state): string => __("roles.$state->value"))
                                ->hiddenLabel()->badge()->alignStart(),

                        ])->columns(5)->columnSpan(2),

                        TextEntry::make('message')
                            ->label('Mensagem')->visible(fn($record) => isset($record->message))
                            ->columnSpanFull(),

                        TextEntry::make('proposed_agency_cut')
                            ->label('Porcentagem Proposta')
                            ->placeholder('-')
                            ->weight(FontWeight::Bold)
                            ->formatStateUsing(function ($state, $record) {
                                $announcementCut = $record->announcement?->agency_cut;

                                if (! $state || ! $announcementCut) {
                                    return $state;
                                }

                                $difference = $state - $announcementCut;

                                if ($difference === 0) {
                                    return "{$state}% <span class='text-xs text-gray-500'>(sem variação)</span>";
                                }

                                $arrow = $difference > 0
                                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>'
                                    : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>';

                                $color = $difference > 0 ? 'text-danger-600' : 'text-success-600';

                                return new HtmlString(
                                    " <span class='mr-2' >
                                    {$state}%
                                    </span>
                                     <span class=\"{$color} text-xs inline-flex items-center  pl-4\">
                                     {$difference}% {$arrow}
                                    </span>"
                                );
                            }),

                        TextEntry::make('proposed_budget')
                            ->label('Orçamento Proposto')
                            ->placeholder('-')
                            ->state(function ($record) {
                                $influencers = $record->influencers()
                                    ->get()
                                    ->map(fn($inf) => [
                                        'reels_price' => $inf->pivot->reels_price ?? 0,
                                        'stories_price' => $inf->pivot->stories_price ?? 0,
                                        'carrousel_price' => $inf->pivot->carrousel_price ?? 0,
                                    ])
                                    ->toArray();

                                if (empty($influencers)) {
                                    return '-';
                                }

                                $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                                    $record->announcement->n_reels,
                                    $record->announcement->n_stories,
                                    $record->announcement->n_carrousels,
                                    $influencers
                                );

                                return new HtmlString('
                                    <div class="flex flex-col gap-0.5 text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">De R$ ' . number_format($range['min'], 2, ',', '.') . '</span>
                                        <span class="text-gray-600 dark:text-gray-400">à R$ ' . number_format($range['max'], 2, ',', '.') . '</span>
                                    </div>
                                ');
                            }),

                        TextEntry::make('created_at')
                            ->label('Enviada em')
                            ->dateTime('d/m/Y H:i'),

                        Action::make('newChat')
                            ->label('Conversar')
                            ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                            ->color('secondary')
                            ->visible(fn($record) => $record->agency_id !== Auth::id())
                            ->action(function ($record) {

                                $proposalId = $record->id;

                                $chat = \App\Models\Chat::query()
                                    ->where('proposal_id', $proposalId)
                                    ->whereHas('users', fn($q) => $q->where('users.id', Auth::id()))
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
                    ->schema(function ($record) {
                        $proposalId = $record->id;

                        return [
                            RepeatableEntry::make('influencers')
                                ->hiddenLabel()
                                ->schema([



                                    ImageEntry::make('avatar_url')
                                        ->hiddenLabel()
                                        ->circular()
                                        ->imageSize(76),

                                    Group::make()
                                        ->schema([
                                            TextEntry::make('name')
                                                ->weight(FontWeight::Bold)
                                                ->hiddenLabel(),

                                            TextEntry::make('role')
                                                ->formatStateUsing(
                                                    fn(UserRoles $state): string => __("roles.$state->value")
                                                )
                                                ->hiddenLabel()
                                                ->badge(),
                                        ])
                                        ->columnSpan(2),

                                    TextEntry::make('influencer_approval')->hiddenLabel()
                                        ->badge()->alignment(Alignment::End)
                                        ->state(function ($record) use ($proposalId) {
                                            $approval = DB::table('proposal_user')
                                                ->where('proposal_id', $proposalId)
                                                ->where('user_id', $record->id)
                                                ->value('influencer_approval');

                                            return $approval ?? 'pending';
                                        })
                                        ->formatStateUsing(fn($state) => __("approval_status.{$state}"))
                                        ->color(fn($state) => match ($state) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'pending' => 'warning',
                                        }),

                                    TextEntry::make('bio')
                                        ->weight(FontWeight::SemiBold)
                                        ->hiddenLabel()->columnSpan(5),

                                    // ── Prices
                                    Group::make()->visible(Gate::denies('is_influencer'))
                                        ->schema([
                                            TextEntry::make('pivot_reels_price')
                                                ->label('Reels')
                                                ->money('BRL')
                                                ->weight(FontWeight::SemiBold)
                                                ->placeholder('-')
                                                ->state(function ($record) use ($proposalId) {
                                                    return DB::table('proposal_user')
                                                        ->where('proposal_id', $proposalId)
                                                        ->where('user_id', $record->id)
                                                        ->value('reels_price');
                                                }),

                                            TextEntry::make('pivot_stories_price')
                                                ->label('Stories')
                                                ->money('BRL')
                                                ->weight(FontWeight::SemiBold)
                                                ->placeholder('-')
                                                ->state(function ($record) use ($proposalId) {
                                                    return DB::table('proposal_user')
                                                        ->where('proposal_id', $proposalId)
                                                        ->where('user_id', $record->id)
                                                        ->value('stories_price');
                                                }),

                                            TextEntry::make('pivot_carrousel_price')
                                                ->label('Carrossel')
                                                ->money('BRL')
                                                ->weight(FontWeight::SemiBold)
                                                ->placeholder('-')
                                                ->state(function ($record) use ($proposalId) {
                                                    return DB::table('proposal_user')
                                                        ->where('proposal_id', $proposalId)
                                                        ->where('user_id', $record->id)
                                                        ->value('carrousel_price');
                                                }),
                                        ])
                                        ->columns(3)
                                        ->columnSpanFull(),

                                    Section::make('Redes Sociais')
                                        ->collapsible()->visible(Gate::denies('is_influencer'))
                                        ->collapsed()
                                        ->schema([
                                            Group::make()
                                                ->columns(3)
                                                ->schema([
                                                    TextEntry::make('social_label')->hiddenLabel()
                                                        ->state('Rede')
                                                        ->weight(FontWeight::SemiBold),

                                                    TextEntry::make('social_user')->hiddenLabel()
                                                        ->state('Usuário')
                                                        ->weight(FontWeight::SemiBold),

                                                    TextEntry::make('social_followers')->hiddenLabel()
                                                        ->state('Seguidores')
                                                        ->weight(FontWeight::SemiBold),
                                                ]),
                                            Group::make()->columns(3)->schema([
                                                TextEntry::make('instagram_label')->hiddenLabel()->state('Instagram'),
                                                TextEntry::make('influencer_info.instagram')->hiddenLabel()
                                                    ->prefix('@')->badge()->copyable()
                                                    ->placeholder('-'),
                                                TextEntry::make('influencer_info.instagram_followers')->hiddenLabel()
                                                    ->numeric()
                                                    ->icon('heroicon-o-users')
                                                    ->placeholder('-'),
                                            ]),

                                            Group::make()->columns(3)->schema([
                                                TextEntry::make('youtube_label')->hiddenLabel()->state('YouTube'),
                                                TextEntry::make('influencer_info.youtube')->hiddenLabel()
                                                    ->prefix('@')->badge()->copyable()
                                                    ->placeholder('-'),
                                                TextEntry::make('influencer_info.youtube_followers')->hiddenLabel()
                                                    ->numeric()
                                                    ->icon('heroicon-o-users')
                                                    ->placeholder('-'),
                                            ]),

                                            Group::make()->columns(3)->schema([
                                                TextEntry::make('tiktok_label')->hiddenLabel()->state('TikTok'),
                                                TextEntry::make('influencer_info.tiktok')->hiddenLabel()
                                                    ->prefix('@')->badge()->copyable()
                                                    ->placeholder('-'),
                                                TextEntry::make('influencer_info.tiktok_followers')->hiddenLabel()
                                                    ->numeric()
                                                    ->icon('heroicon-o-users')
                                                    ->placeholder('-'),
                                            ]),

                                            Group::make()->columns(3)->schema([
                                                TextEntry::make('twitter_label')->hiddenLabel()->state('Twitter'),
                                                TextEntry::make('influencer_info.twitter')->hiddenLabel()
                                                    ->prefix('@')->badge()->copyable()
                                                    ->placeholder('-'),
                                                TextEntry::make('influencer_info.twitter_followers')->hiddenLabel()
                                                    ->numeric()
                                                    ->icon('heroicon-o-users')
                                                    ->placeholder('-'),
                                            ]),

                                            Group::make()->columns(3)->schema([
                                                TextEntry::make('facebook_label')->hiddenLabel()->state('Facebook'),
                                                TextEntry::make('influencer_info.facebook')->hiddenLabel()
                                                    ->prefix('@')->badge()->copyable()
                                                    ->placeholder('-'),
                                                TextEntry::make('influencer_info.facebook_followers')->hiddenLabel()
                                                    ->numeric()
                                                    ->icon('heroicon-o-users')
                                                    ->placeholder('-'),
                                            ]),
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->columns(5)
                        ];
                    }),

                Actions::make([

                    EditProposalAction::make(),

                    AcceptProposal::make(),

                    RejectProposal::make(),

                    Action::make('remove_proposal')->icon(Heroicon::Trash)->hiddenLabel()->requiresConfirmation()->modalHeading('Remover Proposta')
                        ->color('gray')->visible(
                            fn($record, $livewire) => Gate::allows('is_agency')
                                && $record
                                ->exists()
                        )
                        ->action(
                            fn($record) => $record->delete()
                        ),
                ]),

            ]);
    }
}
