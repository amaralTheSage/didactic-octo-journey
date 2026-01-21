<?php

namespace App\Filament\Widgets;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Campaigns\CampaignResource;
use App\Filament\Resources\Proposals\ProposalResource;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CompanyWidgets extends StatsOverviewWidget
{
    // protected int | string | array $columnSpan = 'full';

    // protected function getColumns(): int
    // {
    //     return 2;
    // }

    protected function getMatchCounts(float $minPercentage): int
    {
        $user = Auth::user();

        $query = DB::table('campaigns as c');

        // Filtro de Segurança: Empresa vê as dela, Curador vê as das empresas dele
        if ($user->role === UserRole::COMPANY) {
            $query->where('c.company_id', $user->id);
        } elseif ($user->role === UserRole::CURATOR) {
            $query->whereIn('c.company_id', function ($sub) use ($user) {
                $sub->select('company_id')
                    ->from('company_info')
                    ->where('curator_id', $user->id);
            });
        }

        // Conta campanhas que possuem PELO MENOS UM influenciador com o match desejado
        return $query->whereExists(function ($query) use ($minPercentage) {
            $query->select(DB::raw(1))
                ->from('users as u')
                ->join('attribute_value_user as avu', 'u.id', '=', 'avu.user_id')
                ->join('attribute_value_campaign as avc', function ($join) {
                    $join->on('avu.attribute_value_id', '=', 'avc.attribute_value_id')
                        ->whereRaw('COALESCE(avu.title, \'\') = COALESCE(avc.title, \'\')');
                })
                ->whereColumn('avc.campaign_id', 'c.id')
                ->where('u.role', UserRole::INFLUENCER->value)
                ->groupBy('u.id')
                ->havingRaw('CAST(COUNT(avu.id) AS FLOAT) / (SELECT count(*) FROM attribute_value_campaign WHERE campaign_id = c.id) >= ?', [$minPercentage]);
        })->count();
    }

    protected function getStats(): array
    {
        $match50 = $this->getMatchCounts(0.5);
        $match90 = $this->getMatchCounts(0.9);

        $user = auth()->user();

        // Definimos a lógica de filtro de dono/curador em uma closure para não repetir código
        $applyOwnershipFilter = function ($query) use ($user) {
            if ($user->role === \App\Enums\UserRole::COMPANY) {
                return $query->where('company_id', $user->id);
            }

            if ($user->role === \App\Enums\UserRole::CURATOR) {
                // Busca os IDs de todas as empresas vinculadas a este curador
                return $query->whereIn('company_id', function ($sub) use ($user) {
                    $sub->select('company_id')
                        ->from('company_info')
                        ->where('curator_id', $user->id);
                });
            }
        };

        return [

            Stat::make('Campanhas com Candidatos', $match50)
                ->description('Campanhas com influenciadores +50% compatíveis')
                ->chart([2, 4, 6, 8, 10, 12, 14])
                ->color('info')
                ->url(CampaignResource::getUrl('index')),

            Stat::make('Campanhas Ideais', $match90)
                ->description('Campanhas com influenciadores +90% compatíveis')
                ->chart([1, 3, 5, 2, 8, 15, 20])
                ->color('success')
                ->url(CampaignResource::getUrl('index')),

            Stat::make('Propostas Recebidas', Proposal::query()
                ->whereHas('campaign', $applyOwnershipFilter)
                ->count())
                ->description('Totais de propostas recebidas')
                ->descriptionIcon(\Filament\Support\Facades\FilamentIcon::resolve('lucide-handshake'), \Filament\Support\Enums\IconPosition::Before)
                ->chart([1, 5, 10, 5, 15, 25, 20])
                ->color('info')
                // Visível para Empresa ou Curador
                ->visible(fn () => $user->role === \App\Enums\UserRole::COMPANY || $user->role === \App\Enums\UserRole::CURATOR)
                ->url(ProposalResource::getUrl('index')),

            Stat::make('Propostas Pendentes', Proposal::query()
                ->whereHas('campaign', $applyOwnershipFilter)
                ->where('company_approval', ApprovalStatus::PENDING)
                ->count())
                ->description('Propostas com aprovação pendente')
                ->descriptionIcon(\Filament\Support\Facades\FilamentIcon::resolve('lucide-loader'), \Filament\Support\Enums\IconPosition::Before)
                ->chart([1, 3, 5, 10, 20, 40])
                ->color('success')
                ->visible(fn () => $user->role === \App\Enums\UserRole::COMPANY || $user->role === \App\Enums\UserRole::CURATOR)
                ->url(ProposalResource::getUrl('index', [
                    'filters' => [
                        'company_approval' => [
                            'value' => 'pending',
                        ],
                    ],
                ])),

        ];
    }

    public static function canView(): bool
    {
        return Gate::allows('is_company_or_curator');
    }
}
