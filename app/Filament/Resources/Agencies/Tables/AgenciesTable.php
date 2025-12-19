<?php

namespace App\Filament\Resources\Agencies\Tables;

use App\Actions\Filament\ChatAction;
use App\Actions\Filament\ViewAgencyDetails;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
        $table->recordAction('viewAgencyDetails');

        $table->recordActions([ViewAgencyDetails::make()]);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),

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
                Action::make('viewCampaigns')
                    ->label('Campanhas')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->url(fn($record) => route('filament.admin.resources.campaigns.index', [
                        'search' => $record->name,
                    ]))->visible(
                        fn(Model $record) => $record->campaigns()
                            ->where('company_id', Auth::id())
                            ->exists()
                    ),
                ViewAgencyDetails::make(),
                ChatAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
