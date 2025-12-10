<?php

namespace App\Filament\Resources\Users\Tables;

use App\AssociationStatus;
use App\UserRoles;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role')
                    ->badge()
                    ->searchable()->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('agency.name')->label('Agência')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('association_status')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record): string {
                        if ($record?->role !== UserRoles::Influencer) {
                            return '-';
                        }

                        return match ($state) {
                            'pending'  => 'Pendente',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                            default    => '-',
                        };
                    })

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
