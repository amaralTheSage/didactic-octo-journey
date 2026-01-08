<?php

namespace App\Filament\Resources\CampaignAnnouncements\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ViewProposal;
use App\Enums\PaymentStatus;
use App\Helpers\ProposedBudgetCalculator;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class CampaignAnnouncementsTable
{
    public static function anTab($livewire): bool
    {
        return $livewire->activeTab === 'announcements';
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
                    })
                    ->icon(fn($record) => $record->payments()->where('status', PaymentStatus::PAID)->exists()
                        ? 'heroicon-o-check-circle'
                        : null)
                    ->iconPosition('after')
                    ->tooltip(fn($record) => $record->payments()->where('status', PaymentStatus::PAID)->exists()
                        ? 'Campanha Verificada'
                        : null)
                    ->iconColor(Color::hex('#1e3948')),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')->toggleable()
                    ->visible(fn($livewire) => Gate::denies('is_company') && self::anTab($livewire)),

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

                EditAction::make()->hiddenLabel()->defaultColor('gray')
                    ->visible(fn($record, $livewire) => Gate::allows('is_company') && self::anTab($livewire)),

                ViewProposal::make()->hiddenLabel()
                    ->visible(fn($livewire) => self::prTab($livewire)),

                // Action::make('influencerApprove')->icon(Heroicon::Check)->hiddenLabel()->tooltip('Aprovar')
                //     ->action(function ($record) {
                //         $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'approved']);

                //         $record->announcement->company->notify(
                //             Notification::make()
                //                 ->title('Influenciador aprovou proposta')
                //                 ->body(Auth::user()->name . ' aprovou a proposta para ' . $record->announcement->name)
                //                 ->success()
                //                 ->toDatabase()
                //         );

                //         $record->agency->notify(
                //             Notification::make()
                //                 ->title('Influenciador aprovou proposta')
                //                 ->body(Auth::user()->name . ' aprovou sua proposta para ' . $record->announcement->name)
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

                //         $record->announcement->company->notify(
                //             Notification::make()
                //                 ->title('Influenciador rejeitou proposta')
                //                 ->body(Auth::user()->name . ' rejeitou a proposta para ' . $record->announcement->name)
                //                 ->danger()
                //                 ->toDatabase()
                //         );

                //         $record->agency->notify(
                //             Notification::make()
                //                 ->title('Influenciador rejeitou proposta')
                //                 ->body(Auth::user()->name . ' rejeitou sua proposta para ' . $record->announcement->name)
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
