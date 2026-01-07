<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Actions\Filament\ViewCompanyDetails;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        $table->recordAction('viewCompanyDetails');

        $table->recordActions([ViewCompanyDetails::make()]);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')->circular(),

                TextColumn::make('name')->label('Nome')
                    ->searchable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewCompanyDetails::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
