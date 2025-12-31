<?php

namespace App\Filament\Resources\CampaignAnnouncements;

use App\Filament\Resources\CampaignAnnouncements\Pages\CreateCampaignAnnouncement;
use App\Filament\Resources\CampaignAnnouncements\Pages\EditCampaignAnnouncement;
use App\Filament\Resources\CampaignAnnouncements\Pages\ListCampaignAnnouncements;
use App\Filament\Resources\CampaignAnnouncements\Pages\ViewCampaignAnnouncement;
use App\Filament\Resources\CampaignAnnouncements\Schemas\CampaignAnnouncementForm;
use App\Filament\Resources\CampaignAnnouncements\Schemas\CampaignAnnouncementInfolist;
use App\Filament\Resources\CampaignAnnouncements\Tables\CampaignAnnouncementsTable;
use App\Models\CampaignAnnouncement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampaignAnnouncementResource extends Resource
{
    protected static ?string $model = CampaignAnnouncement::class;

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
        return CampaignAnnouncementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CampaignAnnouncementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampaignAnnouncementsTable::configure($table);
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
        return Gate::allows('is_company') && $record->company_id === Auth::id();
    }

    public static function getPages(): array
    {

        return [
            'index' => ListCampaignAnnouncements::route('/'),
            'create' => CreateCampaignAnnouncement::route('/create'),
            // 'view' => ViewCampaignAnnouncement::route('/{record}'),
            'edit' => EditCampaignAnnouncement::route('/{record}/edit'),
        ];
    }
}
