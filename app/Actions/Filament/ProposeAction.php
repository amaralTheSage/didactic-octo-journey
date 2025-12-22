<?php

namespace App\Actions\Filament;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ProposeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'propose';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Me Interesso');
        $this->color('success');
        $this->icon('heroicon-o-hand-raised');

        $this->visible(
            fn ($record, $livewire) => $livewire->activeTab === 'announcements' &&
                Gate::allows('is_agency')
                && ! $record->proposals()
                    ->where('agency_id', Auth::id())
                    ->exists()
        );

        $this->modalHeading('Enviar Proposta');
        $this->modalDescription(fn ($record) => "Envie sua proposta para a campanha: {$record->name}");
        $this->modalSubmitActionLabel('Enviar Proposta');
        $this->modalWidth('lg');

        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->placeholder('Descreva por que sua agência é ideal para esta campanha...')
                ->rows(4)
                ->maxLength(1000),

            Select::make('influencer_id')
                ->label('Influenciador')
                ->placeholder('Selecione um influenciador')
                ->options(function () {
                    return Auth::user()
                        ->influencers()
                        ->pluck('name', 'user_id');
                })
                ->searchable(),

            TextInput::make('proposed_agency_cut')
                ->label('Proposta de Parcela da Agência')
                ->suffix('%')
                ->numeric()
                ->inputMode('decimal')
                ->minValue(0)
                ->maxValue(100)
                ->default(fn ($record) => $record->agency_cut)
                ->helperText(fn ($record) => "Parcela original: {$record->agency_cut}%"),
        ]);

        $this->action(function ($record, array $data) {
            try {
                $proposal = $record->proposals()->create([
                    'campaign_announcement_id' => $record->id,
                    'agency_id' => Auth::id(),
                    'influencer_id' => $data['influencer_id'],
                    'message' => $data['message'],
                    'proposed_agency_cut' => $data['proposed_agency_cut'],
                ]);

                $record->company->notify(
                    Notification::make()
                        ->title('Proposta recebida para a campanha '.$record->name)
                        ->body('A agência '.Auth::user()->name.' demonstrou interesse em sua campanha')
                        ->toDatabase()
                );

                Notification::make()
                    ->title('Proposta Enviada')
                    ->body('Sua proposta foi enviada com sucesso!')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                Log::error('Erro ao enviar proposta: '.$e->getMessage());

                Notification::make()
                    ->title('Erro ao enviar Proposta')
                    ->body('Ocorreu um erro ao enviar sua proposta. Tente novamente.')
                    ->danger()
                    ->send();
            }
        });
    }
}
