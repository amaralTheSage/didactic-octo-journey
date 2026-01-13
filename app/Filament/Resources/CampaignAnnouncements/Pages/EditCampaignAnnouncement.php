<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use App\Models\Attribute;
use App\Models\CampaignAnnouncement;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EditCampaignAnnouncement extends EditRecord
{
    protected static string $resource = CampaignAnnouncementResource::class;

    protected function afterSave(): void
    {
        $rows = $this->form->getState()['attribute_values'] ?? [];

        $pivotData = [];
        foreach ($rows as $row) {
            $valueIds = $row['attribute_value_id'] ?? [];

            // Ensure we handle both single values or arrays
            $valueIds = is_array($valueIds) ? $valueIds : [$valueIds];

            foreach ($valueIds as $id) {
                if ($id) {
                    $pivotData[$id] = [
                        'title' => $row['title'] ?? null,
                    ];
                }
            }
        }

        $this->record->attribute_values()->sync($pivotData);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\CampaignAnnouncement $record */
        $record = $this->record;

        // Group selected values by attribute_id
        $selectedValues = $record->attribute_values->groupBy('attribute_id');

        $data['attribute_values'] = Attribute::with('values')->get()
            ->map(function ($attribute) use ($selectedValues) {
                $selected = $selectedValues->get($attribute->id);

                return [
                    'attribute_id' => $attribute->id,
                    'attribute' => $attribute,
                    // Map all related IDs into an array for the multiple select
                    'attribute_value_id' => $selected ? $selected->pluck('id')->toArray() : [],
                    // We take the title from the first occurrence if multiple exist
                    'title' => $selected ? $selected->first()->pivot->title : null,
                ];
            })
            ->toArray();

        if (! empty($data['location'])) {
            [$country, $state, $city] = array_pad(explode('|', $data['location']), 3, null);
            $data['location_data'] = [['country' => $country, 'state' => $state, 'city' => $city]];
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
            Action::make('validateNow')
                ->label(fn(CampaignAnnouncement $record) => $record->validated_at ? 'Campanha Validada' : 'Validar Campanha')
                ->color(fn(CampaignAnnouncement $record) => $record->validated_at ? 'secondary' : 'success')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->disabled(fn(CampaignAnnouncement $record) => $record->validated_at)
                ->action(function ($record) {
                    return redirect(route('payments.qrcode') . '?campaign_id=' . $record->id);
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
