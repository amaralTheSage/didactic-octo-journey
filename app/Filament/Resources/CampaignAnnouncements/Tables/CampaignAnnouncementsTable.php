<?php

namespace App\Filament\Resources\CampaignAnnouncements\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ViewProposal;
use App\Helpers\ProposedBudgetCalculator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\HtmlString;
use Laravel\Reverb\Loggers\Log;

class CampaignAnnouncementsTable
{
    public static function anTab($livewire): bool
    {
        return $livewire->activeTab === 'announcements';
    }
    public static function prTab($livewire): bool
    {
        return  $livewire->activeTab === 'proposals';
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

        return $table->defaultSort('created_at', 'desc')
            ->groups(groups: fn($livewire) => self::prTab($livewire) ? [
                // Group::make('company_approval')->label('Aprovação da Empresa')->collapsible(),
                Group::make('status')->collapsible()->orderQueryUsing(function (Builder $query, string $direction) {
                    return $query->orderByRaw("
                CASE 
                    WHEN status = 'draft' THEN 1
                    WHEN status = 'approved' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    WHEN status = 'finished' THEN 4
                    ELSE 5
                END {$direction}
            ");
                })
                    ->getTitleFromRecordUsing(
                        fn($record) => __("campaign_status.{$record->status}.label")
                    )
                    ->getDescriptionFromRecordUsing(
                        fn($record) => __("campaign_status.{$record->status}.description")
                    ),
            ] : [])
            ->defaultGroup(fn($livewire) => self::prTab($livewire) ?
                'status'
                : null)->groupingDirectionSettingHidden()
            ->columns([

                // ANNOUNCEMENTS TAB
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->visible(fn($livewire) => self::anTab($livewire))->description(function ($record) {
                        $count = $record->proposals->count();

                        return $count === 1 ? $count . ' Proposta' : $count . ' Propostas';
                    }),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')->toggleable()
                    ->visible(fn($livewire) => Gate::denies('is_company') && self::anTab($livewire)),

                TextColumn::make('company.name')->label('Empresa')->toggleable()
                    ->searchable()->visible(fn($livewire) => Gate::denies('is_company') && self::anTab($livewire)),
                TextColumn::make('product.name')->label('Produto')->toggleable()
                    ->searchable()->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('description')->label('Descrição')->limit(40)->tooltip(fn($record) => $record->description)->toggleable()->toggledHiddenByDefault()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('budget')->label('Orçamento')->money('BRL')->toggleable()
                    ->sortable()->visible(fn($livewire) => self::anTab($livewire) && Gate::denies('is_influencer'))->description(fn($record) => '+' . rtrim(rtrim(number_format($record->agency_cut, 2, '.', ''), '0'), '.') . '% de Comissão'),

                ColumnGroup::make('Mídias', [
                    TextColumn::make('n_reels')->label('Reels')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                    TextColumn::make('n_stories')->label('Stories')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                    TextColumn::make('n_carrousels')->label('Carrosseis')->alignCenter()->visible(fn($livewire) => self::anTab($livewire)),
                ]),

                TextColumn::make('announcement_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'open' => 'success',
                        'paused' => 'gray',
                        'finished' => 'info',
                    })
                    ->formatStateUsing(fn(string $state) => __("campaign_announcement_status.$state.label"))
                    ->toggleable()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                TextColumn::make('category.title')->label('Categoria')->badge()->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()->visible(fn($livewire) => self::anTab($livewire)),

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
                TextColumn::make('announcement.name')->label('Campanha')
                    ->searchable()
                    ->visible(fn($livewire) => self::prTab($livewire))->description(fn($record) => 'Produto: ' . $record->announcement->product->name),

                // TextColumn::make('announcement.product.name')->label('Produto')
                //     ->searchable()
                //     ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('announcement.category.title')->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->visible(fn($livewire) => self::prTab($livewire) && Gate::denies('is_company')),

                // ColumnGroup::make('Agência', [
                ImageColumn::make('agency.avatar_url')
                    ->circular()
                    ->label('Agência')
                    ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('agency.name')
                    ->label(' ')
                    ->visible(fn($livewire) => self::prTab($livewire)),
                // ]),

                // ColumnGroup::make('Influenciador', [
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
                // ]),

                TextColumn::make('proposed_agency_cut')
                    ->label('% da Agência Proposta')
                    ->numeric()->placeholder('-')->suffix('%')
                    ->description(function ($record) {
                        $announcementCut = $record->announcement?->agency_cut;
                        $proposedCut = $record->proposed_agency_cut;

                        if (! $announcementCut || ! $proposedCut) {
                            return null;
                        }

                        $difference = $proposedCut - $announcementCut;

                        if ($difference === 0) {
                            return 'Sem variação';
                        }

                        $arrow = $difference > 0 ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-icon lucide-arrow-up"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-icon lucide-arrow-down"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>';
                        $color = $difference > 0 ? 'text-danger-600' : 'text-success-600';

                        return new HtmlString(
                            "<span class=\"{$color} text-xs flex items-end gap-1\">{$difference}% {$arrow}</span>"
                        );
                    })
                    ->visible(fn($livewire) => self::prTab($livewire)),

                TextColumn::make('proposed_budget_1')
                    ->label('Orçamento Proposto')
                    ->placeholder('-')
                    ->state(function ($record) {
                        $influencers = $record->influencers()
                            ->with('influencer_info')
                            ->get()
                            ->map(fn($inf) => [
                                'reels_price' => $inf->influencer_info->reels_price ?? 0,
                                'stories_price' => $inf->influencer_info->stories_price ?? 0,
                                'carrousel_price' => $inf->influencer_info->carrousel_price ?? 0,
                            ])
                            ->toArray();

                        FacadesLog::debug('Influencers: ', $influencers);

                        dump($influencers);

                        if (empty($influencers)) {
                            return '-';
                        }

                        $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                            $record->announcement->n_reels,
                            $record->announcement->n_stories,
                            $record->announcement->n_carrousels,
                            $influencers
                        );

                        return 'R$ ' . number_format($range['min'], 2, ',', '.') . ' - R$ ' . number_format($range['max'], 2, ',', '.');
                    })
                    ->visible(fn($livewire) => self::prTab($livewire)),


                ColumnGroup::make('Status')->columns([

                    // APROVAÇÃO DA EMPRESA

                    TextColumn::make('company_approval')
                        ->label('Empresa')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn() => Gate::allows('is_company') ? Heroicon::OutlinedCursorArrowRays : null)->iconPosition(IconPosition::After)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_company')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                    // APROVAÇÃO DA AGÊNCIA

                    TextColumn::make('agency_approval')
                        ->label('Agência')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn() => Gate::allows('is_agency') ? Heroicon::OutlinedCursorArrowRays : null)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_agency')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                    // Status
                    TextColumn::make('status')->label('Status Geral')
                        ->badge()
                        ->color($colorByStatus)
                        ->formatStateUsing(fn($state): string => __("campaign_status.$state.label"))
                        ->visible(fn($livewire) => self::prTab($livewire)),

                    // APROVAÇÃO DO INFLUENCIADOR

                    // TextColumn::make('influencer_approval')
                    //     ->label('Influenciador')->badge()
                    //     ->color($colorByStatus)
                    //     ->icon(fn() => Gate::allows('is_influencer') ? Heroicon::OutlinedCursorArrowRays : null)
                    //     ->action(EditProposalAction::make()->disabled(Gate::denies('is_influencer')))
                    //     ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                    //     ->visible(fn($livewire) => self::prTab($livewire)),
                ]),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => __('campaign_status.draft.label'),
                        'approved' => __('campaign_status.approved.label'),
                        'ongoing' => __('campaign_status.ongoing.label'),
                        'finished' => __('campaign_status.finished.label'),
                        'cancelled' => __('campaign_status.cancelled.label'),
                        'rejected' => __('campaign_status.rejected.label'),
                    ])->visible(fn($livewire) => self::prTab($livewire)),
            ])
            ->recordAction(fn($livewire) => self::anTab($livewire) ? 'view' : 'viewProposal')
            ->recordActions([
                ViewAction::make()->hiddenLabel()
                    ->visible(fn($livewire) => self::anTab($livewire)),

                EditAction::make()->hiddenLabel()
                    ->visible(fn($record, $livewire) => Gate::allows('is_company') && self::anTab($livewire)),



                ViewProposal::make()
                    ->visible(fn($livewire) => self::prTab($livewire)),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
