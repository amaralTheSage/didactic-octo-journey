<?php

namespace App\Filament\Resources\Influencers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InfluencersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->default('https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fogimg.infoglobo.com.br%2Feconomia%2F25035438-701-611%2FFT1086A%2F92729986.jpg&f=1&nofb=1&ipt=251fab699105e037d167e46c1f5264a920953b961a9d7f902f7706593b8f7c79'),
                TextColumn::make('name')->label('Nome')
                    ->searchable(),
                TextColumn::make('agency.name')->label('Agência')->default('___')
                    ->searchable(),
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
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
