<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return
            $table->modifyQueryUsing(function (Builder $query) {
                $query->where('company_id', Auth::user()->id);
            })
            ->columns([
                TextColumn::make('name')->label('Nome')
                    ->searchable(),
                TextColumn::make('price')->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('description')->label('Descrição')
                    ->limit(30),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
