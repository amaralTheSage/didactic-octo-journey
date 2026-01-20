<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return
            $table->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                if (Gate::allows('is_company')) {
                    $query->where('company_id', $user->id);
                } elseif (Gate::allows('is_curator')) {
                    $query->whereIn('company_id', $user->curator_companies()->pluck('users.id'));
                }
            })->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('company.avatar_url')
                    ->label('Empresa')->width('4%')->circular()->hidden(Gate::denies('is_curator')),

                TextColumn::make('company.name')->label('')
                    ->searchable()->hidden(Gate::denies('is_curator')),


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
