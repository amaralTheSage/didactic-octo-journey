<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CampaignMatchesWidget extends StatsOverviewWidget
{
    protected function getMatchCounts(float $minPercentage, float $maxPercentage = 1.0): int
    {
        $userId = Auth::id();

        // 1. Pegamos todas as campanhas e calculamos o percentual de match
        return DB::table('campaigns as c')
            ->select('c.id')
            // Subquery para contar quantos atributos a campanha tem no total
            ->addSelect([
                'total_reqs' => DB::table('attribute_value_campaign')
                    ->whereColumn('campaign_id', 'c.id')
                    ->selectRaw('count(*)'),
            ])
            // Join para encontrar onde o usuário dá "match" (ID + Title)
            ->join('attribute_value_campaign as avc', 'c.id', '=', 'avc.campaign_id')
            ->join('attribute_value_user as avu', function ($join) use ($userId) {
                $join->on('avc.attribute_value_id', '=', 'avu.attribute_value_id')
                    ->where('avu.user_id', $userId)
                    // Lógica de match do Title (considerando nulos como iguais)
                    ->whereRaw('COALESCE(avc.title, \'\') = COALESCE(avu.title, \'\')');
            })
            ->groupBy('c.id')
            // Filtramos pelo percentual (Matches / Total Requisitos)
            ->havingRaw('CAST(COUNT(avu.id) AS FLOAT) / (SELECT count(*) FROM attribute_value_campaign WHERE campaign_id = c.id) >= ?', [$minPercentage])
            ->when($maxPercentage < 1.0, function ($query) use ($maxPercentage) {
                $query->havingRaw('CAST(COUNT(avu.id) AS FLOAT) / (SELECT count(*) FROM attribute_value_campaign WHERE campaign_id = c.id) < ?', [$maxPercentage]);
            })
            ->count();
    }

    protected function getStats(): array
    {
        // Campanhas que batem 90%
        $match90 = $this->getMatchCounts(1.0);

        // Campanhas que batem entre 50% e 99%
        $match50 = $this->getMatchCounts(0.5, 1.0);

        return [
            Stat::make('Oportunidades', $match50)
                ->description('Campanhas com +50% de match')
                ->chart([6, 2, 8, 2, 12, 18, 16])
                ->color('info')
                ->url(CampaignResource::getUrl('index', [
                    'tableFilters' => [
                        'match_level' => ['value' => '50'],
                    ],
                ]))
                ->visible(Gate::allows('is_influencer')),

            Stat::make('Match Perfeito', $match90)
                ->description('Campanhas 90% compatíveis')
                ->chart([6, 2, 8, 2, 9, 3, 15])
                ->color('success')
                ->url(CampaignResource::getUrl('index', [
                    'tableFilters' => [
                        'match_level' => ['value' => '90'],
                    ],
                ]))
                ->visible(Gate::allows('is_influencer')),
        ];
    }
}
