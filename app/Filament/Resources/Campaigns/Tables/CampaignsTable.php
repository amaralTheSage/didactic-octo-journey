<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ViewProposal;
use App\Filament\Tables\Columns\ExpandableBadges;
use App\Helpers\ProposedBudgetCalculator;
use App\Models\Subcategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class CampaignsTable
{
    public static function anTab($livewire): bool
    {
        return $livewire->activeTab === 'campaigns';
    }

    public static function prTab($livewire): bool
    {
        return $livewire->activeTab === 'proposals';
    }

    public static function configure(Table $table): Table
    {
        $colorByStatus = fn(string $state) => match ($state) {
            'approved' => 'success',
            'finished' => 'success',
            'pending' => 'gray',
            'draft' => 'gray',
            'rejected' => 'danger',
            'cancelled' => 'danger',
        };

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                if (self::anTab($livewire)) {
                    $query->orderByRaw(
                        'validated_at IS NULL, validated_at DESC, created_at DESC'
                    );
                }
            })
            ->filters([
                SelectFilter::make('match_level')
                    ->label('Compatibilidade')
                    ->options([
                        '100' => 'Match Perfeito (100%)',
                        '50' => 'Alta Compatibilidade (50%+)',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === '100') {
                            return $query->whereMatchesUser(Auth::user(), 100);
                        }
                        if ($data['value'] === '50') {
                            return $query->whereMatchesUser(Auth::user(), 50, 99);
                        }
                    })
                    ->visible(fn($livewire) => Gate::allows('is_influencer') && self::anTab($livewire)),
            ], layout: FiltersLayout::AboveContent)

            ->columns([

                // CAMPAIGNS TAB
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->visible(fn($livewire) => self::anTab($livewire))->description(function ($record) {
                        $count = $record->proposals->count();

                        return $count === 1 ? $count . ' Proposta' : $count . ' Propostas';
                    })
                    ->icon(fn($record) => $record->validated_at
                        ? 'heroicon-o-check-badge'
                        : null)
                    ->iconPosition('after')
                    ->tooltip(fn($record) => $record->validated_at
                        ? 'Campanha Verificada'
                        : null)
                    ->iconColor('success'),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')->toggleable()
                    ->visible(fn($livewire) => Gate::denies('is_company') && self::anTab($livewire)),

                ExpandableBadges::make('subcategories')
                    ->label('Categorias')
                    ->limit(5)
                    ->grow(true)
                    ->width('120%')
                    ->extraAttributes([
                        'style' => 'min-width: 400px !important; display: block;',
                    ])
                    ->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('company.name')->label('Empresa')->toggleable()
                    ->searchable()->visible(fn($livewire) => Gate::denies('is_company') && self::anTab($livewire)),
                TextColumn::make('product.name')->label('Produto')->toggleable()
                    ->searchable()->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('description')->label('Descrição')->limit(40)->tooltip(fn($record) => $record->description)->toggleable()->toggledHiddenByDefault()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('budget')->label('Orçamento')->money('BRL')->toggleable()
                    ->sortable()
                    ->visible(fn($livewire) => self::anTab($livewire) && Gate::denies('is_influencer'))
                    ->description(fn($record) => '+' . rtrim(rtrim(number_format($record->agency_cut, 2, '.', ''), '0'), '.') . '% de Comissão'),

                ColumnGroup::make('Mídias', [
                    TextColumn::make('n_reels')->label('Reels')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                    TextColumn::make('n_stories')->label('Stories')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                    TextColumn::make('n_carrousels')->label('Carrosseis')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                ]),

                TextColumn::make('campaign_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'open' => 'success',
                        'paused' => 'gray',
                        'finished' => 'info',
                    })
                    ->formatStateUsing(fn(string $state) => __("campaign_status.$state.label"))
                    ->toggleable()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('created_at')->label('Anunciada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => self::anTab($livewire))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')->label('Atualizada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => self::anTab($livewire))
                    ->toggleable(isToggledHiddenByDefault: true),

                // --------------------------
                // PROPOSALS TAB
                TextColumn::make('campaign.name')->label('Campanha')
                    ->searchable()
                    ->icon(fn($record) => $record->campaign->validated_at
                        ? 'heroicon-o-check-badge'
                        : null)
                    ->iconPosition('after')
                    ->tooltip(fn($record) => $record->campaign->validated_at
                        ? 'Campanha Verificada'
                        : null)
                    ->iconColor('success')
                    ->visible(fn($livewire) => self::prTab($livewire))->description(fn($record) => 'Produto: ' . $record->campaign->product->name),

                // TextColumn::make('campaign.product.name')->label('Produto')
                //     ->searchable()
                //     ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('campaign.subcategories.title')->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->visible(fn($livewire) => self::prTab($livewire) && Gate::denies('is_company')),

                // ColumnGroup::make('Agência', [
                ImageColumn::make('agency.avatar_url')
                    ->circular()
                    ->label('Agência')
                    ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('agency.name')->searchable()
                    ->label(' ')
                    ->visible(fn($livewire) => self::prTab($livewire)),
                // ]),

                ImageColumn::make('influencers.avatar_url')
                    ->label('Influenciadores')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->tooltip('influencers.name')->tooltip(
                        fn($record) => $record->influencers
                            ->pluck('name')
                            ->join(', ')

                    )->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('proposed_agency_cut')
                    ->label('% da Agência Proposta')
                    ->numeric()->placeholder('-')->suffix('%')
                    ->description(function ($record) {
                        $campaignCut = $record->campaign?->agency_cut;
                        $proposedCut = $record->proposed_agency_cut;

                        if (! $campaignCut || ! $proposedCut) {
                            return null;
                        }

                        $difference = $proposedCut - $campaignCut;

                        if ($difference === 0) {
                            return 'Sem variação';
                        }

                        $arrow = $difference > 0 ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-icon lucide-arrow-up mb-0.5"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-icon lucide-arrow-down mb-0.5"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>';
                        $color = $difference > 0 ? 'text-danger-600' : 'text-success-600';

                        return new HtmlString(
                            "<span class=\"{$color} text-xs flex items-end gap-1\">{$difference}% {$arrow}</span>"
                        );
                    })
                    ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('proposed_budget')
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
                            $record->n_reels,
                            $record->n_stories,
                            $record->n_carrousels,
                            $influencers
                        );

                        return new HtmlString('
                                <div class="flex flex-col gap-0.5 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">de R$ ' . number_format($range['min'], 2, ',', '.') . '</span>
                                    <span class="text-gray-600 dark:text-gray-400">à R$ ' . number_format($range['max'], 2, ',', '.') . '</span>
                                </div>
                            ');
                    })
                    ->visible(fn($livewire) => self::prTab($livewire) && Gate::denies('is_influencer')),

                ColumnGroup::make('Status')->columns([

                    // APROVAÇÃO DA EMPRESA

                    TextColumn::make('company_approval')
                        ->label('Empresa')
                        ->badge()
                        ->color($colorByStatus)

                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_company')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                    // APROVAÇÃO DA AGÊNCIA

                    TextColumn::make('agency_approval')
                        ->label('Agência')
                        ->badge()
                        ->color($colorByStatus)

                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_agency')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                    // Status
                    TextColumn::make('status')->label('Status Geral')
                        ->badge()
                        ->color($colorByStatus)
                        ->formatStateUsing(fn($state): string => __("campaign_status.$state.label"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                ]),

            ])
            ->filters(
                [
                    // SelectFilter::make('status')
                    //     ->label('Status')
                    //     ->options([
                    //         'draft' => __('campaign_status.draft.label'),
                    //         'approved' => __('campaign_status.approved.label'),
                    //         'ongoing' => __('campaign_status.ongoing.label'),
                    //         'finished' => __('campaign_status.finished.label'),
                    //         'cancelled' => __('campaign_status.cancelled.label'),
                    //         'rejected' => __('campaign_status.rejected.label'),
                    //     ])->visible(fn($livewire) => self::prTab($livewire)),

                    // SelectFilter::make('subcategories.[0]')->label('Categoria')
                    //     ->options(
                    //         Subcategory::query()->pluck('title', 'id')->toArray(),
                    //     )->visible(fn($livewire) => self::anTab($livewire)),
                ]
            )
            ->recordAction(fn($livewire) => self::anTab($livewire) ? 'view' : 'viewProposal')
            ->recordActions([
                ViewAction::make()->hiddenLabel()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                EditAction::make()->hiddenLabel()->defaultColor('gray')
                    ->visible(fn($record, $livewire) => Gate::allows('is_company') && self::anTab($livewire)),

                ViewProposal::make()->hiddenLabel()
                    ->visible(fn($livewire) => self::prTab($livewire)),

                // Action::make('influencerApprove')->icon(Heroicon::Check)->hiddenLabel()->tooltip('Aprovar')
                //     ->action(function ($record) {
                //         $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'approved']);

                //         $record->campaign->company->notify(
                //             Notification::make()
                //                 ->title('Influenciador aprovou proposta')
                //                 ->body(Auth::user()->name . ' aprovou a proposta para ' . $record->campaign->name)
                //                 ->success()
                //                 ->toDatabase()
                //         );

                //         $record->agency->notify(
                //             Notification::make()
                //                 ->title('Influenciador aprovou proposta')
                //                 ->body(Auth::user()->name . ' aprovou sua proposta para ' . $record->campaign->name)
                //                 ->success()
                //                 ->toDatabase()
                //         );

                //         Notification::make()
                //             ->title('Proposta aprovada')
                //             ->success()
                //             ->send();
                //     })->visible(
                //         fn($livewire, $record) => self::prTab($livewire)
                //             && Gate::allows('is_influencer')
                //             && DB::table('proposal_user')->where(['proposal_id' => $record->id, 'user_id' => Auth::id()])->value('influencer_approval') !== 'approved'
                //     ),

                // Action::make('influencerReject')->icon(Heroicon::XMark)->hiddenLabel()->tooltip('Rejeitar')
                //     ->color('danger')
                //     ->action(function ($record) {
                //         $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'rejected']);

                //         $record->campaign->company->notify(
                //             Notification::make()
                //                 ->title('Influenciador rejeitou proposta')
                //                 ->body(Auth::user()->name . ' rejeitou a proposta para ' . $record->campaign->name)
                //                 ->danger()
                //                 ->toDatabase()
                //         );

                //         $record->agency->notify(
                //             Notification::make()
                //                 ->title('Influenciador rejeitou proposta')
                //                 ->body(Auth::user()->name . ' rejeitou sua proposta para ' . $record->campaign->name)
                //                 ->danger()
                //                 ->toDatabase()
                //         );

                //         Notification::make()
                //             ->title('Proposta rejeitada')
                //             ->danger()
                //             ->send();
                //     })->visible(
                //         fn($livewire, $record) => self::prTab($livewire)
                //             && Gate::allows('is_influencer')
                //             && DB::table('proposal_user')->where(['proposal_id' => $record->id, 'user_id' => Auth::id()])->value('influencer_approval') !== 'rejected'
                //     ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
