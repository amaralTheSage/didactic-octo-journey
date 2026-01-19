<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->hiddenLabel()
                    ->disk('public')
                    ->directory('avatars')
                    ->alignCenter()
                    ->image()
                    ->avatar()
                    ->circleCropper(),

                TextInput::make('name')
                    ->label(__('filament-panels::auth/pages/register.form.name.label'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),

                TextInput::make('email')
                    ->label(__('filament-panels::auth/pages/register.form.email.label'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users'),

                Textarea::make('bio')
                    ->rows(5)
                    ->placeholder('Sou criador de conteúdo...')
                    ->required(),

                // TextInput::make('pix_address')->label('Chave Pix'),

                Hidden::make('role')
                    ->default(UserRole::COMPANY->value),

                Hidden::make('email_verified_at')
                    ->default(now()),

                Hidden::make('company_info.curator_id')->default(Auth::id()),

                Hidden::make('password')->default(Hash::make(Str::random(32))),

                Text::make("Caso a Empresa deseje fazer Log In com esta conta, ela deverá inserir uma nova senha clicando em 'Esqueci minha senha'")

            ])->columns(1);
    }
}
