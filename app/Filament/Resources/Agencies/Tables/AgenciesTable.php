<?php

namespace App\Filament\Resources\Agencies\Tables;

use App\Actions\Filament\ChatAction;
use App\Actions\Filament\ViewAgencyDetails;
use App\Models\CampaignAnnouncement;
use App\Models\Proposal;
use App\Models\User;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
        $table->recordAction('viewAgencyDetails');

        $table->recordActions([ViewAgencyDetails::make()]);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Nome')
                    ->circular(),

                TextColumn::make('name')->label(' ')
                    ->description(fn ($record) => $record->influencers()->count().' Influenciadores')
                    ->searchable(),

                TextColumn::make('influencer_categories')
                    ->label('Categorias dos Influenciadores')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->influencers
                            ->flatMap(fn ($inf) => $inf->subcategories)
                            ->pluck('title')
                            ->unique()
                            ->values()
                            ->toArray();
                    })->limitList(2)->listWithLineBreaks()->expandableLimitedList(),

                ColumnGroup::make('Seguidores')->columns([
                    TextColumn::make('igfollowers')
                        ->label('Instagram')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.instagram_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('twfollowers')
                        ->label('twitter')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.twitter_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('ytfollowers')
                        ->label('Youtube')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.youtube_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),

                    TextColumn::make('fbfollowers')
                        ->label('Facebook')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.facebook_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('ttfollowers')
                        ->label('TikTok')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.tiktok_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                ]),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('viewCampaigns')->hiddenLabel()
                    ->tooltip('Visualizar campanhas em comum')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->url(fn ($record) => route('filament.admin.resources.campaigns.index', [
                        'search' => $record->name,
                    ]))->visible(
                        fn (Model $record) => $record->campaigns()
                            ->where('company_id', Auth::id())
                            ->exists()
                    ),
                ViewAgencyDetails::make()->hiddenLabel(),
                ChatAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignAgenciesToCampaign')
                        ->label('Atribuir Agências à Campanha')
                        ->icon('heroicon-o-plus-circle')
                        ->fillForm(function (Collection $records) {
                            return [
                                'agency_id' => $records->pluck('id')->toArray(),
                            ];
                        })
                        ->schema([
                            Select::make('agency_id')
                                ->label('Agências')
                                ->multiple()
                                ->options(
                                    User::where('role', UserRoles::Agency)->pluck('name', 'id')
                                )
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('influencer_ids', [])),

                            Select::make('influencer_ids')
                                ->label('Influenciadores')
                                ->multiple()
                                ->options(function (callable $get) {
                                    $agencyId = $get('agency_id');
                                    if (! $agencyId) {
                                        return [];
                                    }

                                    return User::where('role', \App\UserRoles::Influencer)
                                        ->whereHas('influencer_info', function ($query) use ($agencyId) {
                                            $query->whereIn('agency_id', $agencyId)
                                                ->where('association_status', 'approved');
                                        })
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->searchable()
                                ->disabled(fn (callable $get) => ! $get('agency_id'))
                                ->helperText('Selecione uma agência primeiro'),

                            Select::make('campaign_announcement_id')
                                ->label('Campanha')
                                ->options(
                                    CampaignAnnouncement::query()
                                        ->where('company_id', Auth::id())
                                        ->pluck('name', 'id')
                                )
                                ->required()
                                ->searchable(),

                            // Textarea::make('message')
                            //     ->label('Mensagem (opcional)')
                            //     ->placeholder('Mensagem para a agência...')
                            //     ->rows(3),
                        ])
                        ->action(function (array $data) {
                            $campaign = CampaignAnnouncement::find($data['campaign_announcement_id']);
                            $agencyIds = (array) $data['agency_id']; // Ensure it is an array
                            $selectedInfluencerIds = collect($data['influencer_ids']);

                            foreach ($agencyIds as $agencyId) {
                                // Filter influencers belonging specifically to this agency
                                $agencyInfluencerIds = User::whereIn('id', $selectedInfluencerIds)
                                    ->whereHas('influencer_info', fn ($q) => $q->where('agency_id', $agencyId))
                                    ->pluck('id')
                                    ->toArray();

                                if (empty($agencyInfluencerIds)) {
                                    continue;
                                }

                                // Check for existing proposal for THIS agency
                                $proposal = Proposal::where('campaign_announcement_id', $campaign->id)
                                    ->where('agency_id', $agencyId)
                                    ->first();

                                if ($proposal) {
                                    $proposal->influencers()->syncWithoutDetaching($agencyInfluencerIds);
                                } else {
                                    $proposal = Proposal::create([
                                        'campaign_announcement_id' => $campaign->id,
                                        'agency_id' => $agencyId,
                                        'message' => $data['message'] ?? null,
                                        'proposed_agency_cut' => $campaign->agency_cut,
                                        'company_approval' => 'approved',
                                        'agency_approval' => 'pending',
                                    ]);
                                    $proposal->influencers()->attach($agencyInfluencerIds);
                                }

                                // Notify this specific agency
                                User::find($agencyId)?->notify(
                                    Notification::make()
                                        ->title('Nova proposta de campanha')
                                        ->body(Auth::user()->name.' enviou uma proposta para '.$campaign->name)
                                        ->toDatabase()
                                );
                            }

                            // Notify all selected influencers
                            User::whereIn('id', $selectedInfluencerIds)->get()->each(function ($influencer) use ($campaign) {
                                $influencer->notify(
                                    Notification::make()
                                        ->title('Você foi selecionado para uma campanha')
                                        ->body('Campanha: '.$campaign->name)
                                        ->toDatabase()
                                );
                            });

                            Notification::make()->title('Propostas processadas com sucesso')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
