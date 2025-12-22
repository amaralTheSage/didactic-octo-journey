<?php

namespace App\Filament\Resources\InfluencerCampaigns\Tables;

use App\ApprovalStatus;
use App\Models\OngoingCampaign;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color as ColorsColor;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InfluencerCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Campanha')
                    ->searchable(),
                TextColumn::make('product.name')->label('Produto')
                    ->searchable(),

                TextColumn::make('company.name')->label('Empresa')
                    ->searchable(),

                TextColumn::make('status_agency')->label('Aprovação da Agência')
                    ->searchable()
                    ->formatStateUsing(fn (ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'Aprovação Pendente',
                        ApprovalStatus::APPROVED => 'Aprovada',
                        ApprovalStatus::REJECTED => 'Rejeitada',
                        default => $state->value,
                    }),

                TextColumn::make('status_influencer')->label('Aprovação do Influenciador')
                    ->searchable()
                    ->formatStateUsing(fn (ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'Pendente',
                        ApprovalStatus::APPROVED => 'Aprovada',
                        ApprovalStatus::REJECTED => 'Rejeitada',
                    }),

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
                    Action::make('influencer_approve')
                        ->label('Aprovar Campanha')
                        ->color(ColorsColor::Green)
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {

                            $record->status_influencer = ApprovalStatus::APPROVED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status do influenciador foi definido como aprovado.')
                                ->send();

                            Notification::make()
                                ->title('Campanha '.$record->name.' aprovada pelo influenciador')
                                ->body('O influenciador '.Auth::user()->name.' aprovou sua campanha')
                                ->success()
                                ->sendToDatabase($record->company);
                        }),

                    Action::make('influencer_reject')
                        ->label('Rejeitar Campanha')
                        ->color('danger')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {
                            $record->status_influencer = ApprovalStatus::REJECTED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status do influenciador foi definido como rejeitado.')
                                ->send();

                            $record->company->notify(
                                Notification::make()
                                    ->title('Campanha '.$record->name.' rejeitada pelo influenciador')
                                    ->body('O influenciador '.Auth::user()->name.' rejeitou sua campanha')->danger()->toDatabase()
                            );
                        }),
                ])->visible(
                    fn (Model $record): bool => Gate::allows('is_influencer') && $record->status_influencer === ApprovalStatus::PENDING
                ),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),

            ]);
    }
}
