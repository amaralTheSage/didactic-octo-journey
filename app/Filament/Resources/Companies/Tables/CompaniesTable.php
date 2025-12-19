<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Actions\Filament\ChatAction;
use App\Actions\Filament\ViewCompanyDetails;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
                //   ChatAction::make(),
                // Action::make('viewCampaigns')
                //     ->label('Campanhas')
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->url(fn($record) => route('filament.admin.resources.agency-campaigns.index', [
                //         'search' => $record->name,
                //     ]))->visible(
                //         fn(Model $record) => $record->campaigns()
                //             ->where('agency_id', Auth::id())
                //             ->exists()
                //     ),
                ViewCompanyDetails::make(),
                ChatAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
