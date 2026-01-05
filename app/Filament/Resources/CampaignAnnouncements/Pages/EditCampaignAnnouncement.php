<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use App\Models\Attribute;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCampaignAnnouncement extends EditRecord
{
    protected static string $resource = CampaignAnnouncementResource::class;

    protected function afterSave(): void
    {
        $rows = $this->form->getState()['attribute_values'] ?? [];

        $valueIds = collect($rows)
            ->map(fn($r) => $r['attribute_value_id'] ?? null)
            ->filter()
            ->values()
            ->all();

        $this->record->attribute_values()->sync($valueIds);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\CampaignAnnouncement $record */
        $record = $this->record;

        $selectedValues = $record->attribute_values
            ->mapWithKeys(fn($value) => [
                $value->attribute_id => $value->id,
            ]);

        $data['attribute_values'] = Attribute::with('values')->get()
            ->map(function ($attribute) use ($selectedValues) {
                return [
                    'attribute_id' => $attribute->id,
                    'attribute' => $attribute,
                    'attribute_value_id' => $selectedValues[$attribute->id] ?? null,
                ];
            })
            ->toArray();

        if (! empty($data['location'])) {
            [$country, $state, $city] = array_pad(
                explode('|', $data['location']),
                3,
                null
            );

            $data['location_data'] = [[
                'country' => $country,
                'state' => $state,
                'city' => $city,
            ]];
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
