<?php

namespace App\Filament\Resources\AgencyCampaigns\Tables;

use App\CampaignStatus;
use App\Models\OngoingCampaign;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ApproveCampaignAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RejectCampaignAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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

                TextColumn::make('company.name')->label('Empresa')
                    ->searchable(),

                TextColumn::make('status_agency')->label('Aprovação da Agência')
                    ->searchable()
                    ->formatStateUsing(fn(CampaignStatus $state): string => match ($state) {
                        CampaignStatus::PENDING_APPROVAL => 'Pendente',
                        CampaignStatus::APPROVED => 'Aprovada',
                        CampaignStatus::FINISHED => 'Concluída',
                        CampaignStatus::REJECTED => 'Rejeitada',
                        default => $state->value,
                    }),

                TextColumn::make('status_influencer')->label('')
                    ->searchable()->label('Aprovação do Influenciador')
                    ->formatStateUsing(fn(CampaignStatus $state): string => match ($state) {
                        CampaignStatus::PENDING_APPROVAL => 'Pendente',
                        CampaignStatus::APPROVED => 'Aprovada',
                        CampaignStatus::FINISHED => 'Concluída',
                        CampaignStatus::REJECTED => 'Rejeitada',
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
                    Action::make('agency_approve')
                        ->label('Aprovar Campanha')
                        ->color(Color::Green)
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {
                            $record->status_agency = CampaignStatus::APPROVED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status da agência foi definido como aprovado.')
                                ->success()
                                ->send();
                        }),

                    Action::make('agency_reject')
                        ->label('Rejeitar Campanha')
                        ->color('danger')
                        ->icon(Heroicon::OutlinedCheckCircle)

                        ->action(function (OngoingCampaign $record) {
                            $record->status_agency = CampaignStatus::REJECTED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status da agência foi definido como rejeitado.')
                                ->send();
                        }),
                ])->visible(
                    fn(Model $record): bool =>
                    Gate::allows('is_agency') && $record->status_agency === CampaignStatus::PENDING_APPROVAL
                )
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),

            ]);
    }
}
