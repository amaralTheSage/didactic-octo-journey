<?php

namespace App\Filament\Resources\CampaignAnnouncements\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ProposeAction;
use App\Actions\Filament\ViewProposal;
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

class CampaignAnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        $colorByStatus = fn(string $state) => match ($state) {
            'approved' => 'success',
            'finished' => 'success',
            'pending'  => 'gray',
            'draft' => 'gray',
            'rejected' => 'danger',
            'cancelled' => 'danger',
        };

        return $table
            ->groups(groups: fn($livewire) => $livewire->activeTab === 'proposals' ? [
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
            ->defaultGroup(fn($livewire) => $livewire->activeTab === 'proposals' ?
                'status'
                : null)->groupingDirectionSettingHidden()
            ->columns([

                // ANNOUNCEMENTS TAB
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')->toggleable()
                    ->visible(fn($livewire) => Gate::denies('is_company') && $livewire->activeTab === 'announcements'),

                TextColumn::make('company.name')->label('Empresa')->toggleable()
                    ->searchable()->visible(fn($livewire) => Gate::denies('is_company') && $livewire->activeTab === 'announcements'),
                TextColumn::make('product.name')->label('Produto')->toggleable()
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),

                TextColumn::make('description')->label('Descrição')->limit(40)->tooltip(fn($record) => $record->description)->toggleable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('budget')->label('Orçamento')->money('BRL')->toggleable()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('agency_cut')->label('Porcentagem da Agência')->toggleable()
                    ->numeric()->suffix('%')
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('category.title')->label('Categoria')->badge()->toggleable()
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('created_at')->label('Anunciada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Atualizada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),

                // PROPOSALS TAB
                TextColumn::make('announcement.name')->label('Campanha')
                    ->searchable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('announcement.product.name')->label('Produto')
                    ->searchable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('announcement.category.title')->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                // ColumnGroup::make('Agência', [
                ImageColumn::make('agency.avatar_url')
                    ->circular()
                    ->label('Agência')
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('agency.name')
                    ->label(' ')
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
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

                    )->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                // ]),

                TextColumn::make('message')
                    ->label('Mensagem')
                    ->tooltip(fn($record) => $record->message)
                    ->limit(40)->toggledHiddenByDefault(true)
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('proposed_agency_cut')
                    ->label('Porcentagem Proposta')
                    ->numeric()->placeholder('-')
                    ->suffix('%')
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('proposed_budget')
                    ->label('Orçamento Proposto')
                    ->numeric()->placeholder('-')
                    ->money('BRL')
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                ColumnGroup::make('Status')->columns([

                    // APROVAÇÃO DA EMPRESA

                    TextColumn::make('company_approval')
                        ->label('Empresa')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn() => Gate::allows('is_company') ? Heroicon::OutlinedCursorArrowRays : null)->iconPosition(IconPosition::After)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_company')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                    // APROVAÇÃO DA AGÊNCIA

                    TextColumn::make('agency_approval')
                        ->label('Agência')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn() => Gate::allows('is_agency') ? Heroicon::OutlinedCursorArrowRays : null)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_agency')))
                        ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                    // Status
                    TextColumn::make('status')->label('Status Geral')
                        ->badge()
                        ->color($colorByStatus)
                        ->formatStateUsing(fn($state): string => __("campaign_status.$state.label"))
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),


                    // APROVAÇÃO DO INFLUENCIADOR

                    // TextColumn::make('influencer_approval')
                    //     ->label('Influenciador')->badge()
                    //     ->color($colorByStatus)
                    //     ->icon(fn() => Gate::allows('is_influencer') ? Heroicon::OutlinedCursorArrowRays : null)
                    //     ->action(EditProposalAction::make()->disabled(Gate::denies('is_influencer')))
                    //     ->formatStateUsing(fn($state): string => __("approval_status.$state"))
                    //     ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                ]),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => __('campaign_status.draft.label'),
                        'approved'  => __('campaign_status.approved.label'),
                        'ongoing'   => __('campaign_status.ongoing.label'),
                        'finished'  => __('campaign_status.finished.label'),
                        'cancelled' => __('campaign_status.cancelled.label'),
                        'rejected'  => __('campaign_status.rejected.label'),
                    ])->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
            ])
            ->recordAction(fn($livewire) => $livewire->activeTab === 'announcements' ? 'view' : 'viewProposal')
            ->recordActions([
                ViewAction::make()
                    ->visible(fn($livewire) => $livewire->activeTab === 'announcements'),

                EditAction::make()
                    ->visible(fn($record, $livewire) => Auth::id() === $record->company_id && $livewire->activeTab === 'announcements'),

                // EditAction::make()->label('Editar Proposta')
                //     ->visible(fn($record, $livewire) => Auth::id() === $record->agency_id &&  $livewire->activeTab === 'proposals'),

                ViewProposal::make()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
