<?php

namespace App\Filament\Resources\Agencies;

use App\Filament\Resources\Agencies\Pages\CreateAgency;
use App\Filament\Resources\Agencies\Pages\EditAgency;
use App\Filament\Resources\Agencies\Pages\ListAgencies;
use App\Filament\Resources\Agencies\Schemas\AgencyForm;
use App\Filament\Resources\Agencies\Tables\AgenciesTable;
use App\Models\User;
use App\UserRoles;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AgencyResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Agências';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Mídia';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', UserRoles::Agency);
    }

    public static function form(Schema $schema): Schema
    {
        return AgencyForm::configure($schema);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->role === UserRoles::Company ?? false;
    }

    public static function table(Table $table): Table
    {
        return AgenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgencies::route('/'),
            // 'create' => CreateAgency::route('/create'),
            // 'edit' => EditAgency::route('/{record}/edit'),
        ];
    }
}
