<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCampaignAnnouncement extends EditRecord
{
    protected static string $resource = CampaignAnnouncementResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['location'])) {
            [$country, $state, $city] = array_pad(
                explode('|', $data['location']),
                3,
                null
            );

            $data['location_data'] = [
                [
                    'country' => $country,
                    'state'   => $state,
                    'city'    => $city,
                ],
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
