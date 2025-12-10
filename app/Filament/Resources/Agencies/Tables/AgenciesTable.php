<?php

namespace App\Filament\Resources\Agencies\Tables;

use App\Actions\Filament\ChatAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Image;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
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
                        fn(Model $record) =>
                        $record->campaigns()
                            ->where('company_id', Auth::id())
                            ->exists()
                    ),
                ChatAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
