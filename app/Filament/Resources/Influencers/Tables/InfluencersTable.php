<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Actions\Filament\ViewInfluencerDetails;
use App\Enums\UserRole;
use App\Filament\Tables\Columns\ExpandableBadges;
use App\Models\Campaign;
use App\Models\Proposal;
use App\Models\Subcategory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InfluencersTable
{
    public static function getEloquentQuery(): Builder
    {
        $query = User::query()
            ->where('role', UserRole::INFLUENCER)
            ->whereHas('influencer_info', function (Builder $query) {
                $query->where('agency_id', Auth::id());
            });

        return $query->with('subcategories');
    }

    public static function configure(Table $table): Table
    {
        $table->recordAction(null);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Nome')
                    ->circular(),

                TextColumn::make('name')->label(' ')
                    ->searchable(),

                TextColumn::make('influencer_info.agency.name')->label('Agência')->default('___')
                    ->searchable(),

                ExpandableBadges::make('subcategories')->label('Categorias')->limit(6)->width('40%'),

                TextColumn::make('total_followers')
                    ->label('Seguidores')
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

            ])
            ->filters([
                SelectFilter::make('subcategories.[0]')->label('Categoria')
                    ->options(
                        Subcategory::query()->pluck('title', 'id')->toArray(),
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
                    BulkAction::make('lendInfluencersBulk')
                        ->label('Emprestar Influenciadores')
                        ->icon('heroicon-o-user-plus')
                        ->color('secondary')
                        ->visible(fn () => Gate::allows('is_agency'))
                        ->schema([
                            Select::make('agency_id')
                                ->label('Agência Destino')
                                ->placeholder('Selecione a agência que receberá o acesso')
                                ->required()
                                ->searchable()
                                ->preload()
                                // Filtra para mostrar apenas outras agências
                                ->options(fn () => User::where('role', UserRole::AGENCY)
                                    ->whereNot('id', auth()->id())
                                    ->pluck('name', 'id')),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $targetAgencyId = $data['agency_id'];
                            $lender = auth()->user();
                            $count = $records->count();

                            // 1. Inserção em massa no banco
                            foreach ($records as $record) {
                                // Usamos updateOrInsert para evitar duplicidade se já foi emprestado antes
                                DB::table('borrowed_influencer_agency')->updateOrInsert(
                                    [
                                        'influencer_id' => $record->id,
                                        'agency_id' => $targetAgencyId,
                                    ],
                                    [
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]
                                );
                            }

                            // 2. Notificação consolidada para a agência destino
                            $targetAgency = User::find($targetAgencyId);
                            if ($targetAgency) {
                                $targetAgency->notify(
                                    Notification::make()
                                        ->title('Influenciadores Emprestados')
                                        ->body("A agência {$lender->name} lhe emprestou {$count} influenciadores.")
                                        ->success()
                                        ->toDatabase()
                                );
                            }

                            // 3. Feedback para quem executou a ação
                            Notification::make()
                                ->title('Sucesso!')
                                ->body("{$count} influenciadores foram emprestados para {$targetAgency->name}.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('assignToExistingCampaign')
                        ->label('Atribuir à Campanha Existente')
                        ->icon('heroicon-o-plus-circle')
                        ->visible(Gate::allows('is_company_or_curator'))
                        ->schema([
                            Select::make('campaign_id')
                                ->label('Campanha')
                                ->options(function () {
                                    $user = Auth::user();
                                    $query = Campaign::query();

                                    if ($user->role === UserRole::COMPANY) {
                                        $query->where('company_id', $user->id);
                                    } elseif (Gate::allows('is_agency')) {
                                        Campaign::all();
                                    } elseif ($user->role === UserRole::CURATOR) {
                                        $query->whereIn('company_id', function ($sub) use ($user) {
                                            $sub->select('company_id')
                                                ->from('company_info')
                                                ->where('curator_id', $user->id);
                                        });
                                    }

                                    return $query->pluck('name', 'id');
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (EloquentCollection $records, array $data) {
                            $user = Auth::user();
                            $campaign = Campaign::find($data['campaign_id']);

                            // Agrupar influenciadores por agência
                            $influencersByAgency = $records->groupBy(
                                fn ($influencer) => $influencer->influencer_info->agency_id
                            );

                            foreach ($influencersByAgency as $agencyId => $influencers) {
                                // Lógica de aprovação:
                                // Se Empresa/Curador envia -> Já sai aprovado pela empresa
                                // Se Agência envia -> Já sai aprovado pela agência
                                $isInitiatedByOwner = in_array($user->role, [UserRole::COMPANY, UserRole::CURATOR]);

                                $proposal = Proposal::create([
                                    'campaign_id' => $campaign->id,
                                    'agency_id' => $agencyId,
                                    'message' => $data['message'] ?? null,
                                    'proposed_agency_cut' => $campaign->agency_cut,
                                    'company_approval' => $isInitiatedByOwner ? 'approved' : 'pending',
                                    'agency_approval' => $isInitiatedByOwner ? 'pending' : 'approved',
                                ]);

                                $proposal->influencers()->attach($influencers->pluck('id'));

                                // Notificação Dinâmica
                                if ($isInitiatedByOwner) {
                                    // Notifica a Agência do Influenciador
                                    $recipient = User::find($agencyId);
                                    $body = "{$user->name} enviou uma proposta para {$campaign->name}";
                                } else {
                                    // Notifica o Dono da Campanha (Empresa)
                                    $recipient = User::find($campaign->company_id);
                                    $body = "A agência {$user->name} sugeriu influenciadores para {$campaign->name}";
                                }

                                if ($recipient) {
                                    $recipient->notify(
                                        Notification::make()
                                            ->title('Nova interação em campanha')
                                            ->body($body)
                                            ->toDatabase()
                                    );
                                }
                            }

                            Notification::make()
                                ->title('Ação concluída')
                                ->body('Propostas processadas com sucesso!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('createCampaignWithInfluencers')
                        ->label('Criar Campanha com Selecionados')
                        ->icon('heroicon-o-sparkles')
                        ->visible(Gate::allows('is_company_or_curator'))
                        ->action(function (EloquentCollection $records) {
                            $influencerIds = $records->pluck('id')->toArray();

                            // Store in session to pre-populate form
                            session(['selected_influencers' => $influencerIds]);

                            // Redirect to create page
                            return redirect()->route('filament.admin.resources.campaigns.create');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]), ]);
    }
}
