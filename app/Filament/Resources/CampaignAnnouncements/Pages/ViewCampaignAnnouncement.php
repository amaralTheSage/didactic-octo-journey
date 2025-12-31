<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCampaignAnnouncement extends ViewRecord
{
    protected static string $resource = CampaignAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
