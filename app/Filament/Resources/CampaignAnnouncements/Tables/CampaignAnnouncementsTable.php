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
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignAnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        $colorByStatus = fn (string $state) => match ($state) {
            'approved' => 'success',
            'pending' => 'gray',    // neutral
            'rejected' => 'danger',
        };

        return $table
            ->columns([
                // ANNOUNCEMENTS TAB
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')
                    ->visible(fn ($livewire) => Gate::denies('is_company') && $livewire->activeTab === 'announcements'),

                TextColumn::make('company.name')->label('Empresa')
                    ->searchable()->visible(fn ($livewire) => Gate::denies('is_company') && $livewire->activeTab === 'announcements'),
                TextColumn::make('product.name')->label('Produto')
                    ->searchable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),

                TextColumn::make('description')->label('Descrição')->limit(40)->tooltip(fn ($record) => $record->description)
                    ->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('budget')->label('Orçamento')->money('BRL')
                    ->sortable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('agency_cut')->label('Porcentagem da Agência')
                    ->numeric()->suffix('%')
                    ->sortable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('category.title')->label('Categoria')->badge()
                    ->searchable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('created_at')->label('Anunciada em')
                    ->dateTime()
                    ->sortable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Atualizada em')
                    ->dateTime()
                    ->sortable()->visible(fn ($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),

                // PROPOSALS TAB
                TextColumn::make('announcement.name')->label('Campanha')
                    ->searchable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('announcement.product.name')->label('Produto')
                    ->searchable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('announcement.category.title')->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                // ColumnGroup::make('Agência', [
                ImageColumn::make('agency.avatar_url')
                    ->circular()
                    ->label('Agência')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('agency.name')
                    ->label(' ')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),
                // ]),

                // ColumnGroup::make('Influenciador', [
                ImageColumn::make('influencers.avatar_url')
                    ->label('Influenciadores')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->tooltip('influencers.name')->tooltip(
                        fn ($record) => $record->influencers
                            ->pluck('name')
                            ->join(', ')

                    )->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),
                // ]),

                TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->message)
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('proposed_agency_cut')
                    ->label('Parcela Proposta')
                    ->numeric()
                    ->suffix('%')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                ColumnGroup::make('Aprovação')->columns([

                    // APROVAÇÃO DA EMPRESA

                    TextColumn::make('company_approval')
                        ->label('Empresa')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn () => Gate::allows('is_company') ? Heroicon::OutlinedCursorArrowRays : null)->iconPosition(IconPosition::After)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_company')))
                        ->formatStateUsing(fn ($state): string => __("approval_status.$state"))
                        ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                    // APROVAÇÃO DA AGÊNCIA

                    TextColumn::make('agency_approval')
                        ->label('Agência')
                        ->badge()
                        ->color($colorByStatus)
                        ->icon(fn () => Gate::allows('is_agency') ? Heroicon::OutlinedCursorArrowRays : null)
                        ->action(EditProposalAction::make()->disabled(Gate::denies('is_agency')))
                        ->formatStateUsing(fn ($state): string => __("approval_status.$state"))
                        ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

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
                //
            ])
            ->recordAction(fn ($livewire) => $livewire->activeTab === 'announcements' ? 'view' : 'viewProposal')
            ->recordActions([
                ViewAction::make()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'announcements'),

                EditAction::make()
                    ->visible(fn ($record, $livewire) => Auth::id() === $record->company_id && $livewire->activeTab === 'announcements'),

                // EditAction::make()->label('Editar Proposta')
                //     ->visible(fn($record, $livewire) => Auth::id() === $record->agency_id &&  $livewire->activeTab === 'proposals'),

                ViewProposal::make()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'proposals'),

                ProposeAction::make(),

                Action::make('remove_proposal')
                    ->label('Remover Interesse')
                    ->color('danger')->visible(
                        fn ($record, $livewire) => $livewire->activeTab === 'announcements' &&
                            Gate::allows('is_agency')
                            && $record->proposals()
                                ->where('agency_id', Auth::id())
                                ->exists()
                    )
                    ->action(
                        fn ($record) => $record->proposals()->where('agency_id', Auth::id())->delete()
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
