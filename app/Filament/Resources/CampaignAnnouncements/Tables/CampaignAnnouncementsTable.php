<?php

namespace App\Filament\Resources\CampaignAnnouncements\Tables;

use App\Actions\Filament\ProposeAction;
use App\Actions\Filament\ViewProposal;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
        return $table
            ->columns([
                // ANNOUNCEMENTS TAB
                ImageColumn::make('company.avatar_url')->circular()->label(' ')
                    ->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('company.name')->label('Empresa')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('product.name')->label('Produto')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('description')->label("Descrição")->limit(40)
                    ->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('budget')->label('Orçamento')->money('BRL')
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('agency_cut')->label('Porcentagem da Agência')
                    ->numeric()->suffix('%')
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('category.title')->label('Categoria')->badge()
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

                ColumnGroup::make('Agência', [
                    ImageColumn::make('agency.avatar_url')
                        ->circular()
                        ->label(' ')
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                    TextColumn::make('agency.name')
                        ->label('Nome')
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                ]),

                ColumnGroup::make('Influenciador', [
                    ImageColumn::make('influencer.avatar_url')
                        ->circular()
                        ->label(' ')
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                    TextColumn::make('influencer.name')
                        ->label('Nome')
                        ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                ]),

                TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->message)
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals')
                    ->wrap(),

                TextColumn::make('proposed_agency_cut')
                    ->label('Parcela Proposta')
                    ->numeric()
                    ->suffix('%')
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                TextColumn::make('created_at')
                    ->label('Enviada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
            ])
            ->filters([
                //
            ])
            ->recordAction(ViewAction::class)
            ->recordActions([
                ViewAction::make()
                    ->visible(fn($livewire) => $livewire->activeTab === 'announcements'),

                EditAction::make()
                    ->visible(fn($record) => Auth::id() === $record->company_id),

                ViewProposal::make()
                    ->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                ProposeAction::make(),

                Action::make('remove_proposal')
                    ->label('Remover Interesse')
                    ->color('danger')
                    ->visible(
                        fn($record) =>
                        Gate::allows('is_agency')
                            && $record->proposals()
                            ->where('agency_id', Auth::id())
                            ->exists()
                    )
                    ->action(
                        fn($record) => $record->proposals()->where('agency_id', Auth::id())->delete()
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
