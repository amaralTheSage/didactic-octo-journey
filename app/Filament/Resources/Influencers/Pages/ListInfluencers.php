<?php

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\InfluencerResource;
use App\UserRoles;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListInfluencers extends ListRecords
{
    protected static string $resource = InfluencerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        if (Auth::user()->role !== UserRoles::Agency) {
            return [];
        }

        return [
            'Nossos Influenciadores' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->whereHas('influencer_info', function (Builder $query) {
                    $query->where('agency_id', Auth::id())->where('association_status', 'approved');
                });
            }),
            'Pedidos de Vínculo' => Tab::make()->modifyQueryUsing(function ($query) {
                $query->whereHas('influencer_info', function (Builder $query) {
                    $query->where('agency_id', Auth::id())->where('association_status', 'pending');
                });
            }),
        ];
    }
}
