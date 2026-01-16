<?php

namespace App\Filament\Widgets;

use App\Enums\ApprovalStatus;
use App\Models\Proposal;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Gate;

class ProposalsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Propostas Pendentes', Proposal::query()
                ->whereHas('campaign', function ($query) {
                    $query->where('company_id', auth()->id());
                })
                ->where('company_approval', ApprovalStatus::PENDING)
                ->count())
                ->description('Propostas com aprovação pendente')
                ->descriptionIcon(LucideIcon::Loader, IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('success')
                ->visible(Gate::allows('is_company')),
        ];
    }
}
