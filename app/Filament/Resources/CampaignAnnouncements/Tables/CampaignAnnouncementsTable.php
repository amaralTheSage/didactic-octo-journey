<?php

namespace App\Filament\Resources\CampaignAnnouncements\Tables;

use App\Actions\Filament\ViewProposal;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
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
                ImageColumn::make('company.avatar_url')->circular()->label(' ')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('company.name')->label('Empresa')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('agency_cut')->label('Parcela da Agência')
                    ->numeric()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('budget')->label('Orçamento')
                    ->numeric()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('product.name')->label('Produto')
                    ->searchable()->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('category.title')->label('Categoria')->badge()
                    ->searchable()->searchable()->visible(fn($livewire) => $livewire->activeTab === 'announcements'),
                TextColumn::make('created_at')->label('Anunciada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Atualizada em')
                    ->dateTime()
                    ->sortable()->visible(fn($livewire) => $livewire->activeTab === 'announcements')
                    ->toggleable(isToggledHiddenByDefault: true),

                ///////
                ///////
                ///////
                TextColumn::make('announcement.name')->label('Campanha')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                TextColumn::make('announcement.product.name')->label('Produto')
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                TextColumn::make('announcement.category.title')->label('Categoria')->badge()
                    ->searchable()->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                ColumnGroup::make('Agência', [
                    ImageColumn::make('agency.avatar_url')
                        ->circular()
                        ->label(' ')->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                    TextColumn::make('agency.name')
                        ->label('Agência')->visible(fn($livewire) => $livewire->activeTab === 'proposals'),
                ])

            ])
            ->filters([
                //
            ])
            ->recordActions([
                // EditAction::make()->visible(fn($record) => Auth::id() === $record->agency_id),
                ViewProposal::make()->visible(fn($livewire) => $livewire->activeTab === 'proposals'),

                Action::make('propose')
                    ->label('Me Interesso')
                    ->visible(
                        fn($record) =>
                        Gate::allows('is_agency')
                            && !$record->proposals()
                                ->where('agency_id', Auth::id())
                                ->exists()
                    )
                    ->action(
                        fn($record) =>
                        $record->proposals()->create([
                            'campaign_announcement_id' => $record->id,
                            'agency_id' => Auth::id(),
                        ])
                    ),

                Action::make('remove_proposal')
                    ->label('Remover Interesse')->color('danger')
                    ->visible(
                        fn($record) =>
                        Gate::allows('is_agency')
                            && $record->proposals()
                            ->where('agency_id', Auth::id())
                            ->exists()
                    )
                    ->action(
                        fn($record) =>
                        $record->proposals()->where('agency_id', Auth::id())->delete()
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
