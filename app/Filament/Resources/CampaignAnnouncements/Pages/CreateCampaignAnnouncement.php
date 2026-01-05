<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaignAnnouncement extends CreateRecord
{
    protected function afterCreate(): void
    {
        $rows = $this->form->getState()['attribute_values'] ?? [];

        $valueIds = collect($rows)
            ->map(fn($r) => $r['attribute_value_id'] ?? null)
            ->filter()
            ->values()
            ->all();

        $this->record->attribute_values()->sync($valueIds);
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['location_data'])) {
            $loc = $data['location_data'][0] ?? [];

            $data['location'] = implode('|', [
                $loc['country'] ?? '',
                $loc['state'] ?? '',
                $loc['city'] ?? '',
            ]);

            unset($data['location_data']); // IMPORTANT
        }

        return $data;
    }

    protected static string $resource = CampaignAnnouncementResource::class;
}
