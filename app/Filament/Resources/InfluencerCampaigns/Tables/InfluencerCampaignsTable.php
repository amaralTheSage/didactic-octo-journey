<?php

namespace App\Filament\Resources\InfluencerCampaigns\Tables;

use App\CampaignStatus;
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
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Console\Color;

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
                    ->formatStateUsing(fn(CampaignStatus $state): string => match ($state) {
                        CampaignStatus::PENDING_APPROVAL => 'Aprovação Pendente',
                        CampaignStatus::APPROVED => 'Aprovada',
                        CampaignStatus::FINISHED => 'Concluída',
                        CampaignStatus::REJECTED => 'Rejeitada',
                        default => $state->value,
                    }),


                TextColumn::make('status_influencer')->label('Aprovação do Influenciador')
                    ->searchable()
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
                    Action::make('influencer_approve')
                        ->label('Aprovar Campanha')
                        ->color(ColorsColor::Green)
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {
                            $record->status_influencer = CampaignStatus::APPROVED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status do influenciador foi definido como aprovado.')
                                ->send();
                        }),

                    Action::make('influencer_reject')
                        ->label('Rejeitar Campanha')
                        ->color('danger')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->action(function (OngoingCampaign $record) {
                            $record->status_influencer = CampaignStatus::REJECTED;
                            $record->save();

                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('O status do influenciador foi definido como rejeitado.')
                                ->send();
                        }),
                ])->visible(
                    fn(Model $record): bool =>
                    Gate::allows('is_influencer') && $record->status_influencer === CampaignStatus::PENDING_APPROVAL
                )
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),

            ]);
    }
}
