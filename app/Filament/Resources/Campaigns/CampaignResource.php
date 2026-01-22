<?php

namespace App\Filament\Resources\Campaigns;

use App\Filament\Resources\Campaigns\Pages\CreateCampaign;
use App\Filament\Resources\Campaigns\Pages\EditCampaign;
use App\Filament\Resources\Campaigns\Pages\ListCampaigns;
use App\Filament\Resources\Campaigns\Pages\ViewCampaign;
use App\Filament\Resources\Campaigns\Schemas\CampaignForm;
use App\Filament\Resources\Campaigns\Schemas\CampaignInfolist;
use App\Filament\Resources\Campaigns\Tables\CampaignsTable;
use App\Models\Campaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $modelLabel = 'Campanhas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Gate::allows('is_company')) {
            return $query->where('company_id', Auth::id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return CampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canAccess(): bool
    {
        return Gate::denies('is_admin');
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('is_company') && $record->company_id === Auth::id();
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return (Gate::allows('is_company') && $record->company_id === $user->id)
            || (Gate::allows('is_curator') && $user->curator_companies()->where('users.id', $record->company_id)->exists());
    }

    public static function canCreate(): bool
    {
        return Gate::allows('is_company_or_curator');
    }
    public static function getPages(): array
    {

        return [
            'index' => ListCampaigns::route('/'),
            'create' => CreateCampaign::route('/create'),
            // 'view' => ViewCampaign::route('/{record}'),
            'edit' => EditCampaign::route('/{record}/edit'),
        ];
    }
}
