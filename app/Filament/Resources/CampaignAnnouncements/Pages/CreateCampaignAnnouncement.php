<?php

namespace App\Filament\Resources\CampaignAnnouncements\Pages;

use App\Filament\Resources\CampaignAnnouncements\CampaignAnnouncementResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaignAnnouncement extends CreateRecord
{
    protected function afterCreate(): void
    {
        $rows = $this->form->getState()['attribute_values'] ?? [];

        $pivotData = [];
        foreach ($rows as $row) {
            $valueId = $row['attribute_value_id'] ?? null;
            if ($valueId) {
                $pivotData[$valueId] = [
                    'title' => $row['title'] ?? null,
                ];
            }
        }

        $this->record->attribute_values()->sync($pivotData);

        Notification::make()
            ->title('Campanha criada com sucesso')
            ->body('Gostaria de validar esta campanha agora?')
            ->actions([
                Action::make('validateNow')
                    ->label('Validar')
                    ->color('success')
                    ->action(fn () => dump('foi')),
            ])
            ->send()
            ->ToDatabase();
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
