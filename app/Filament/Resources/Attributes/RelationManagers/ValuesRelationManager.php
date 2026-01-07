<?php

namespace App\Filament\Resources\Attributes\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $label = 'Valor';

    protected static ?string $title = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Valor')
                    ->required(),
                Toggle::make('editable')->label('Editável')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->heading('')
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Título')
                    ->searchable(),
                IconColumn::make('editable')->label('Editável')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel()->tooltip('Editar'),
                //   DissociateAction::make()->hiddenLabel()->tooltip('Desassociar'),
                DeleteAction::make()->hiddenLabel()->tooltip('Excluir'),
            ])
            ->toolbarActions([
                CreateAction::make(),
                //         AssociateAction::make(),
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
