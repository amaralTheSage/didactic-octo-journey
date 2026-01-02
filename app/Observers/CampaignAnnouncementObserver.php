<?php

namespace App\Observers;

use App\Models\CampaignAnnouncement;
use App\Models\Proposal;
use App\Models\User;
use Filament\Notifications\Notification;

class CampaignAnnouncementObserver
{
    public function created(CampaignAnnouncement $campaign): void
    {
        $influencerIds = $campaign->temp_influencer_ids;


        if (empty($influencerIds)) {
            return;
        }

        $influencers = User::whereIn('id', $influencerIds)
            ->with('influencer_info')
            ->get();

        // Groups by agency
        $influencersByAgency = $influencers->groupBy(
            fn($influencer) => $influencer->influencer_info?->agency_id
        );

        foreach ($influencersByAgency as $agencyId => $agencyInfluencers) {
            if (empty($agencyId)) {
                continue;
            }

            $proposal = Proposal::create([
                'campaign_announcement_id' => $campaign->id,
                'agency_id'                => $agencyId,
                'proposed_agency_cut'      => $campaign->agency_cut,
            ]);

            $proposal->influencers()->attach($agencyInfluencers->pluck('id'));

            $agency = User::find($agencyId);
            if ($agency) {
                Notification::make()
                    ->title("Nova proposta de campanha")
                    ->body("{$campaign->company->name} criou uma campanha e enviou uma proposta.")
                    ->sendToDatabase($agency);
            }

            foreach ($agencyInfluencers as $influencer) {
                Notification::make()
                    ->title("Nova proposta de campanha de {$campaign->company->name}")
                    ->body("Você foi selecionado para a campanha: {$campaign->name}")
                    ->sendToDatabase($influencer);
            }
        }
    }
}
