<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        $companyField = match ($user->role) {
            UserRole::COMPANY => Hidden::make('company_id')
                ->default($user->id),

            UserRole::CURATOR => Select::make('company_id')
                ->label('Empresa')
                ->options(fn() => $user->curator_companies()->pluck('name', 'users.id'))
                ->searchable()
                ->required(),

            default => Hidden::make('company_id'),
        };

        return $schema
            ->components([

                Group::make([
                    Section::make([
                        $companyField,

                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),

                        TextInput::make('price')
                            ->label('Preço')
                            ->moneyBRL()
                            ->required(),

                        MarkdownEditor::make('description')
                            ->label('Descrição')
                            ->nullable(),
                    ])->columnSpan(6)->columns(1)->columnStart(2),
                ])->columns(8)->columnSpanFull()
            ]);
    }
}
