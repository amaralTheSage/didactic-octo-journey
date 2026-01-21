<?php

namespace App\Filament\Resources\Influencers;

use App\Enums\UserRole;
use App\Filament\Resources\Influencers\Pages\CreateInfluencer;
use App\Filament\Resources\Influencers\Pages\EditInfluencer;
use App\Filament\Resources\Influencers\Pages\ListInfluencers;
use App\Filament\Resources\Influencers\Schemas\InfluencerForm;
use App\Filament\Resources\Influencers\Tables\InfluencersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class InfluencerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Influenciadores';

    protected static string|UnitEnum|null $navigationGroup = 'Mídia';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()->role === UserRole::AGENCY) {
            return parent::getEloquentQuery()->where('role', UserRole::INFLUENCER)->whereHas('influencer_info', function (Builder $query) {
                $query->where('agency_id', Auth::id());
            });
        } else {
            return parent::getEloquentQuery()->where('role', UserRole::INFLUENCER);
        }
    }

    public static function canViewAny(): bool
    {

        return Gate::allows('is_company_or_curator') || Gate::allows('is_agency');
    }

    public static function form(Schema $schema): Schema
    {
        return InfluencerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfluencersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role === UserRole::AGENCY;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->role === UserRole::AGENCY && $record->agency_id === Auth::user()->id;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfluencers::route('/'),
            //  'create' => CreateInfluencer::route('/create'),
            'edit' => EditInfluencer::route('/{record}/edit'),
        ];
    }
}
