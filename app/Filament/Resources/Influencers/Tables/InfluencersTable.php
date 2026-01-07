<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Actions\Filament\ViewInfluencerDetails;
use App\Models\CampaignAnnouncement;
use App\Models\Category;
use App\Models\Proposal;
use App\Models\User;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InfluencersTable
{
    public static function getEloquentQuery(): Builder
    {
        $query = User::query()
            ->where('role', UserRoles::Influencer)
            ->whereHas('influencer_info', function (Builder $query) {
                $query->where('agency_id', Auth::id());
            });

        return $query->with('subcategories');
    }

    public static function configure(Table $table): Table
    {
        $table->recordAction('viewInfluencerDetails');

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Nome')
                    ->circular(),
                TextColumn::make('name')->label(' ')
                    ->searchable(),
                TextColumn::make('influencer_info.agency.name')->label('Agência')->default('___')
                    ->searchable(),

                TextColumn::make('subcategories')
                    ->label('Subcategorias')
                    ->placeholder('-')
                    ->badge()->listWithLineBreaks()
                    ->limitList(1)
                    ->expandableLimitedList()
                    ->state(function (Model $record) {
                        return $record->subcategories->pluck('title')->toArray();
                    })
                    ->tooltip(
                        fn (Model $record) => $record->subcategories->pluck('title')->join(', ')
                    )
                    ->sortable(false)
                    ->wrap(),

                TextColumn::make('total_followers')
                    ->label('Seguidores (Total)')
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
                    ->numeric(),

                // TextColumn::make('influencer_info.city')
                //     ->label('Cidade / UF')
                //     ->placeholder('-')
                //     ->searchable()->description(fn($record) => $record->influencer_info->state),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subcategory.0.category')->label('Categoria')
                    ->options(
                        Category::query()->pluck('title', 'id')->toArray(),
                    ),
            ])
            ->recordActions([
                Action::make('Aprovar Vínculo')
                    ->label('Aprovar')
                    ->visible(fn ($livewire): bool => $livewire->activeTab === 'Pedidos de Vínculo')
                    ->action(function ($record) {
                        $record->influencer_info->update(['association_status' => 'approved']);
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Influenciador vinculado')
                            ->body('Vínculo com influenciador criado com sucesso.')
                    ),

                ViewInfluencerDetails::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignToExistingCampaign')
                        ->label('Atribuir à Campanha Existente')
                        ->icon('heroicon-o-plus-circle')
                        ->schema([
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
                            //     ->placeholder('Mensagem para as agências...')
                            //     ->rows(3),
                        ])
                        ->action(function (EloquentCollection $records, array $data) {
                            // Group influencers by agency
                            $influencersByAgency = $records->groupBy(
                                fn ($influencer) => $influencer->influencer_info->agency_id
                            );

                            $campaign = CampaignAnnouncement::find($data['campaign_announcement_id']);

                            // Create one proposal per agency
                            foreach ($influencersByAgency as $agencyId => $influencers) {
                                $proposal = Proposal::create([
                                    'campaign_announcement_id' => $campaign->id,
                                    'agency_id' => $agencyId,
                                    'message' => $data['message'] ?? null,
                                    'proposed_agency_cut' => $campaign->agency_cut,
                                    'company_approval' => 'approved',
                                    'agency_approval' => 'pending',
                                ]);

                                $proposal->influencers()->attach($influencers->pluck('id'));

                                // Notify agency
                                User::find($agencyId)->notify(
                                    Notification::make()
                                        ->title('Nova proposta de campanha')
                                        ->body(Auth::user()->name.' enviou uma proposta para '.$campaign->name)
                                        ->toDatabase()
                                );
                            }

                            Notification::make()
                                ->title('Influenciadores atribuídos')
                                ->body('Propostas criadas com sucesso!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('createCampaignWithInfluencers')
                        ->label('Criar Campanha com Selecionados')
                        ->icon('heroicon-o-sparkles')
                        ->action(function (EloquentCollection $records) {
                            $influencerIds = $records->pluck('id')->toArray();

                            // Store in session to pre-populate form
                            session(['selected_influencers' => $influencerIds]);

                            // Redirect to create page
                            return redirect()->route('filament.admin.resources.campaign-announcements.create');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
