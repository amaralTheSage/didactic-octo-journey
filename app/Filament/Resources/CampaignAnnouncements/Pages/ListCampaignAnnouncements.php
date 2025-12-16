<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use App\Models\Proposal;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListCampaignAnnouncements extends ListRecords
{
    protected static string $resource = CampaignAnnouncementResource::class;

    public ?string $activeTab = 'announcements';



    public function getTabs(): array
    {
        if (Gate::denies('is_company')) {
            return [];
        }

        return [
            'announcements' => Tab::make('Anúncios')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('company_id', Auth::id())
                ),

            'proposals' => Tab::make('Propostas')
                ->modifyQueryUsing(
                    fn() =>
                    Proposal::query()
                        ->with(['agency', 'announcement'])
                        ->whereHas(
                            'announcement',
                            fn(Builder $q) =>
                            $q->where('company_id', Auth::id())
                        )
                ),
        ];
    }



    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Anunciar Campanha'),
        ];
    }
}
