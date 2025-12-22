<?php

namespace App\Filament\Resources\InfluencerCampaigns;

use App\Filament\Resources\InfluencerCampaigns\Pages\CreateInfluencerCampaign;
use App\Filament\Resources\InfluencerCampaigns\Pages\EditInfluencerCampaign;
use App\Filament\Resources\InfluencerCampaigns\Pages\ListInfluencerCampaigns;
use App\Filament\Resources\InfluencerCampaigns\Schemas\InfluencerCampaignForm;
use App\Filament\Resources\InfluencerCampaigns\Tables\InfluencerCampaignsTable;
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

class InfluencerCampaignResource extends Resource
{
    protected static ?string $model = OngoingCampaign::class;

    protected static ?string $modelLabel = 'Minhas Campanhas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    public static function form(Schema $schema): Schema
    {
        return InfluencerCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfluencerCampaignsTable::configure($table);
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

        if (Auth::user()->role === UserRoles::Influencer) {
            return $query->where('influencer_id', Auth::id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfluencerCampaigns::route('/'),
            //  'create' => CreateInfluencerCampaign::route('/create'),
            //  'edit' => EditInfluencerCampaign::route('/{record}/edit'),
        ];
    }
}
