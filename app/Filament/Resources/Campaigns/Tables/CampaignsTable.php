<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\ApprovalStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Campanha')
                    ->searchable(),
                TextColumn::make('product.name')->label('Produto')
                    ->searchable(),
                TextColumn::make('influencer.name')->label('Influenciador')
                    ->searchable(),
                TextColumn::make('agency.name')->label('Agência')
                    ->searchable(),

                TextColumn::make('status_agency')->label('Aprovação da Agência')
                    ->formatStateUsing(fn(ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'Aprovação Pendente',
                        ApprovalStatus::APPROVED => 'Aprovada',

                        ApprovalStatus::REJECTED => 'Cancelada',
                        default => $state->value,
                    }),
                TextColumn::make('status_influencer')->label('Aprovação do Influenciador')
                    ->formatStateUsing(fn(ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'Aprovação Pendente',
                        ApprovalStatus::APPROVED => 'Aprovada',

                        ApprovalStatus::REJECTED => 'Cancelada',
                        default => $state->value,
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
                EditAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
