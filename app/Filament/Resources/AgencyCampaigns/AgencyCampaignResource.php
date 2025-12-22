<?php

namespace App\Filament\Resources\AgencyCampaigns;

use App\Filament\Resources\AgencyCampaigns\Pages\CreateAgencyCampaign;
use App\Filament\Resources\AgencyCampaigns\Pages\EditAgencyCampaign;
use App\Filament\Resources\AgencyCampaigns\Pages\ListAgencyCampaigns;
use App\Filament\Resources\AgencyCampaigns\Schemas\AgencyCampaignForm;
use App\Filament\Resources\AgencyCampaigns\Tables\AgencyCampaignsTable;
use App\Models\OngoingCampaign;
use App\UserRoles;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgencyCampaignResource extends Resource
{
    protected static ?string $model = OngoingCampaign::class;

    protected static ?string $modelLabel = 'Nossas Campanhas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    public static function form(Schema $schema): Schema
    {
        return AgencyCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgencyCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role === UserRoles::Agency) {
            return $query->where('agency_id', Auth::id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgencyCampaigns::route('/'),
            //  'create' => CreateAgencyCampaign::route('/create'),
            //  'edit' => EditAgencyCampaign::route('/{record}/edit'),
        ];
    }
}
