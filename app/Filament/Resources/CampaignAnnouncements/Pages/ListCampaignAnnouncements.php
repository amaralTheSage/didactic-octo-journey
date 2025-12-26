<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use App\Models\Proposal;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ListCampaignAnnouncements extends ListRecords
{

    protected static string $resource = CampaignAnnouncementResource::class;

    public ?string $activeTab = 'announcements';

    public function getTabs(): array
    {
        return [
            'announcements' => Tab::make('Anúncios')
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->when(
                        Gate::allows('is_company'),
                        fn($q) => $q->where('company_id', Auth::id())
                    )
                ),
            'proposals' => Tab::make(fn() => Gate::allows('is_company') ? 'Propostas' : 'Nossas Propostas')
                ->badge(fn() => ($count = Proposal::query()
                    ->whereHas('announcement', fn($q) => $q->where('company_id', Auth::id()))
                    ->where('company_approval', 'pending')
                    ->count()
                ) > 0 ? $count : null)->badgeTooltip('Propostas não avaliadas')

                ->modifyQueryUsing(
                    fn() => Proposal::query()

                        ->with(['agency', 'announcement'])
                        ->where(function ($query) {
                            $query->whereHas('announcement', fn($q) => $q->where('company_id', Auth::id()))
                                ->orWhere('agency_id', Auth::id())
                                ->orWhereHas(
                                    'influencers',
                                    fn($q) => $q->where('users.id', Auth::id())
                                );
                        })
                ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Anunciar Campanha')->visible(fn() => Gate::allows('is_company')),
        ];
    }
}
