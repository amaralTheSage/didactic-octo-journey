<?php

namespace App\Filament\Resources\Agencies\Tables;

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

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->default('https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fd1a3f4spazzrp4.cloudfront.net%2Fuber-com%2F1.1.8%2Fd1a3f4spazzrp4.cloudfront.net%2Fimages%2Ffacebook-shareimage-1-c3462391c9.jpg&f=1&nofb=1&ipt=2d5599fe5390a3a3ae3cf9936a3ef2fd2a1babb90ef970f18fb474e888b2fb90'),

                TextColumn::make('name')->label('Nome')
                    ->searchable(),

                TextColumn::make('Chat')->label('')
                    ->url(fn(Model $record): string => route('home', ['user' => $record]))->default('Chat'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(), // try and change into a chat action
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
