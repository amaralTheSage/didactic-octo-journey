<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Actions\Filament\ViewCompanyDetails;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        $table->recordAction('viewCompanyDetails');

        $table->recordActions([ViewCompanyDetails::make()]);

        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->withCount('proposals')->orderBy('proposals_count', 'desc')
            )
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')->circular(),

                TextColumn::make('name')->label('Nome')
                    ->searchable(),

                TextColumn::make('campaigns_count')
                    ->label('Campanhas')
                    ->counts('campaigns'),

                TextColumn::make('proposals_count')
                    ->label('Propostas')
                    ->counts('proposals'),

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
