<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use App\Models\CampaignAnnouncement;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateCampaignAnnouncement extends CreateRecord
{
    protected function afterCreate(): void
    {
        $rows = $this->form->getState()['attribute_values'] ?? [];

        $pivotData = [];
        foreach ($rows as $row) {
            $valueIds = $row['attribute_value_id'] ?? [];

            // Convert to array if it's a single value (fallback)
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

        Notification::make()
            ->title('Campanha criada com sucesso')
            ->body('Gostaria de validar esta campanha agora?')
            ->actions([
                Action::make('validateNow')
                    ->label(fn() => $this->record->validated_at ? 'Campanha Validada' : 'Validar')
                    ->color(fn() => $this->record->validated_at ? 'secondary' : 'success')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->disabled(fn() => $this->record->validated_at)
                    ->url(
                        route('payments.qrcode') . '?campaign_id=' . $this->record->id
                    ),
            ])
            ->send()
            ->toDatabase();
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
