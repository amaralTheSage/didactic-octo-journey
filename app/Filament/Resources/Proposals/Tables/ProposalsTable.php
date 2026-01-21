<?php

namespace App\Filament\Resources\Proposals\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ViewProposal;
use App\Enums\UserRole;
use App\Helpers\ProposedBudgetCalculator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        $colorByStatus = fn (string $state) => match ($state) {
            'approved' => 'success',
            'finished' => 'success',
            'pending' => 'gray',
            'draft' => 'gray',
            'rejected' => 'danger',
            'cancelled' => 'danger',
        };

        return $table
            ->modifyQueryUsing(
                function (Builder $query) {
                    $user = Auth::user();
                    $role = $user->role;

                    if ($role === UserRole::ADMIN) {
                        return $query;
                    }

                    return $query->where(function (Builder $subQuery) use ($user, $role) {
                        switch ($role) {
                            case UserRole::COMPANY:
                                $subQuery->whereHas('campaign', fn ($q) => $q->where('company_id', $user->id));
                                break;

                            case UserRole::CURATOR:
                                $subQuery->whereHas(
                                    'campaign.company.company_info',
                                    fn ($q) => $q->where('curator_id', $user->id)
                                );
                                break;

                            case UserRole::AGENCY:
                                $subQuery->where('agency_id', $user->id);
                                break;
                            case UserRole::INFLUENCER:
                                $subQuery->whereHas('influencers', fn ($q) => $q->where('users.id', $user->id));
                                break;
                        }
                    });
                }
            )
            ->columns([
                TextColumn::make('campaign.name')->label('Campanha')
                    ->searchable()
                    ->icon(fn ($record) => $record->campaign->validated_at
                        ? 'heroicon-o-check-badge'
                        : null)
                    ->iconPosition('after')
                    ->tooltip(fn ($record) => $record->campaign->validated_at
                        ? 'Campanha Verificada'
                        : null)
                    ->iconColor('success')
                    ->description(fn ($record) => 'Produto: '.$record->campaign->product->name),

                // TextColumn::make('campaign.product.name')->label('Produto')
                //     ->searchable()
                //     ,

                TextColumn::make('campaign.subcategories.title')->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->visible(Gate::denies('is_company')),

                // ColumnGroup::make('Agência', [
                ImageColumn::make('agency.avatar_url')
                    ->circular()
                    ->label('Agência'),

                TextColumn::make('agency.name')->searchable()
                    ->label(' '),
                // ]),

                ImageColumn::make('influencers.avatar_url')
                    ->label('Influenciadores')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->tooltip('influencers.name')->tooltip(
                        fn ($record) => $record->influencers
                            ->pluck('name')
                            ->join(', ')

                    ),

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
                    }),

                TextColumn::make('proposed_budget')
                    ->label('Orçamento Proposto')
                    ->placeholder('-')
                    ->state(function ($record) {
                        $influencers = $record->influencers()
                            ->get()
                            ->map(fn ($inf) => [
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
                                    <span class="text-gray-600 dark:text-gray-400">de R$ '.number_format($range['min'], 2, ',', '.').'</span>
                                    <span class="text-gray-600 dark:text-gray-400">à R$ '.number_format($range['max'], 2, ',', '.').'</span>
                                </div>
                            ');
                    })
                    ->visible(Gate::denies('is_influencer')),

                ColumnGroup::make('Status')->columns([

                    // APROVAÇÃO DA EMPRESA

                    TextColumn::make('company_approval')
                        ->label('Empresa')
                        ->badge()
                        ->color($colorByStatus)

                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_company')))
                        ->formatStateUsing(fn ($state): string => __("approval_status.$state")),

                    // APROVAÇÃO DA AGÊNCIA

                    TextColumn::make('agency_approval')
                        ->label('Agência')
                        ->badge()
                        ->color($colorByStatus)

                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_agency')))
                        ->formatStateUsing(fn ($state): string => __("approval_status.$state")),

                    // Status
                    TextColumn::make('status')->label('Status Geral')
                        ->badge()
                        ->color($colorByStatus)
                        ->formatStateUsing(fn ($state): string => __("campaign_status.$state.label")),

                ]),

            ])
            ->filters([
                SelectFilter::make('company_approval')
                    ->label('Empresa')
                    ->options([
                        'pending' => __('approval_status.pending'),
                        'approved' => __('approval_status.approved'),
                        'rejected' => __('approval_status.rejected'),
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('agency_approval')
                    ->label('Agência')
                    ->options([
                        'pending' => __('approval_status.pending'),
                        'approved' => __('approval_status.approved'),
                        'rejected' => __('approval_status.rejected'),
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('influencer_approval')
                    ->label('Influenciador')
                    ->options([
                        'pending' => __('approval_status.pending'),
                        'approved' => __('approval_status.approved'),
                        'rejected' => __('approval_status.rejected'),
                    ])
                    ->placeholder('Todos'),
            ])
            ->recordAction('viewProposal')
            ->recordActions([
                ViewProposal::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
