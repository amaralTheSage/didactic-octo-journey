<?php

namespace App\Filament\Resources\AgencyCampaigns\Tables;

use App\ApprovalStatus;
use App\Models\OngoingCampaign;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AgencyCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->label('Campanha')
                    ->searchable(),

                TextColumn::make('product.name')->label('Produto')
                    ->searchable(),

                ColumnGroup::make('Empresa', [

                    ImageColumn::make('company.avatar_url')->circular()->label('')
                        ->searchable(),

                    TextColumn::make('company.name')->label('')
                        ->searchable(),
                ]),

                ColumnGroup::make('Influencer', [

                    ImageColumn::make('influencer.avatar_url')->circular()->label('')
                        ->searchable(),

                    TextColumn::make('influencer.name')->label('')
                        ->searchable(),

                ]),

                ColumnGroup::make('Aprovação', [
                    TextColumn::make('status_agency')->label('Agência')
                        ->searchable()
                        ->formatStateUsing(fn (ApprovalStatus $state): string => match ($state) {
                            ApprovalStatus::PENDING => 'Pendente',
                            ApprovalStatus::APPROVED => 'Aprovada',

                            ApprovalStatus::REJECTED => 'Rejeitada',
                            default => $state->value,
                        }),

                    TextColumn::make('status_influencer')->label('')
                        ->searchable()->label('Influenciador')
                        ->formatStateUsing(fn (ApprovalStatus $state): string => match ($state) {
                            ApprovalStatus::PENDING => 'Pendente',
                            ApprovalStatus::APPROVED => 'Aprovada',

                            ApprovalStatus::REJECTED => 'Rejeitada',
                        }),
                ]),

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
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('agency_approve')
                        ->label('Aprovar Campanha')
                        ->color(Color::Green)
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {
                            $record->status_agency = ApprovalStatus::APPROVED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status da agência foi definido como aprovado')
                                ->success();

                            $record->company->notify(
                                Notification::make()
                                    ->title('Campanha '.$record->name.' aprovada pela agência')
                                    ->body('A agência '.Auth::user()->name.' aprovou sua campanha')
                                    ->success()->toDatabase()
                            );
                        }),

                    Action::make('agency_reject')
                        ->label('Rejeitar Campanha')
                        ->color('danger')
                        ->icon(Heroicon::OutlinedCheckCircle)

                        ->action(function (OngoingCampaign $record) {
                            $record->status_agency = ApprovalStatus::REJECTED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status da agência foi definido como rejeitado.')
                                ->send();

                            $record->company->notify(
                                Notification::make()
                                    ->title('Campanha '.$record->name.' rejeitada pela agência')
                                    ->body('A agência '.Auth::user()->name.' rejeitou sua campanha')->danger()->toDatabase()
                            );
                        }),
                ])->visible(
                    fn (Model $record): bool => Gate::allows('is_agency') && $record->status_agency === ApprovalStatus::PENDING
                ),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),

            ]);
    }
}
