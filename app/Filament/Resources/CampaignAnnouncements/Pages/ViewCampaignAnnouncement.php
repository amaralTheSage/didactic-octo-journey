<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Actions\Filament\ProposeAction;
use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
