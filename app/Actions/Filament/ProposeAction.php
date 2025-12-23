<?php

namespace App\Actions\Filament;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
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

        $this->button();

        $this->visible(
            fn($record) =>
            Gate::allows('is_agency')
                && ! $record->proposals()
                    ->where('agency_id', Auth::id())
                    ->exists()
        );

        $this->modalHeading('Enviar Proposta');
        $this->modalDescription(fn($record) => "Envie sua proposta para a campanha: {$record->name}");
        $this->modalSubmitActionLabel('Enviar Proposta');
        $this->modalWidth('lg');

        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->placeholder('Descreva por que sua agência é ideal para esta campanha...')
                ->rows(4)
                ->maxLength(1000),

            Select::make('influencer_ids')
                ->label('Influenciadores')
                ->multiple()
                ->options(
                    fn() => Auth::user()
                        ->influencers()->where('association_status', 'approved')
                        ->pluck('name', 'users.id')
                )
                ->searchable()->reactive()
                ->visible(fn() => Gate::allows('is_agency')),

            TextEntry::make('influencer_pricing')
                ->hiddenLabel()
                ->state(function ($get) {
                    $influencerIds = $get('influencer_ids');

                    if (empty($influencerIds)) {
                        return null;
                    }

                    $influencers = \App\Models\User::with('influencer_info')
                        ->whereIn('id', $influencerIds)
                        ->get();

                    $content = '';
                    $content .= "<div class='p-3 gap-3 grid grid-cols-2 rounded-lg bg-gray-100 dark:bg-gray-800'>";
                    foreach ($influencers as $influencer) {
                        $info = $influencer->influencer_info;
                        $content .= "<div>";
                        $content .= "<strong>{$influencer->name}</strong><br>";
                        $content .= "Reels: R$ " . number_format($info->reels_price ?? 0, 2, ',', '.') . "<br>";
                        $content .= "Stories: R$ " . number_format($info->stories_price ?? 0, 2, ',', '.') . "<br>";
                        $content .= "Carrousel: R$ " . number_format($info->carrousel_price ?? 0, 2, ',', '.');
                        $content .= "</div>";
                    }
                    $content .= "</div>";

                    return new \Illuminate\Support\HtmlString($content);
                })
                ->visible(fn() => Gate::allows('is_agency')),


            TextInput::make('proposed_agency_cut')
                ->label('Proposta de Parcela da Agência')
                ->suffix('%')
                ->numeric()
                ->inputMode('decimal')
                ->minValue(0)->placeholder(fn($record) => "{$record->agency_cut}")
                ->maxValue(100)
                ->default(fn($record) => $record->agency_cut)
                ->helperText(fn($record) => "Parcela original: {$record->agency_cut}%"),

            TextInput::make('proposed_budget')
                ->label('Orçamento Proposto')
                ->numeric()->placeholder(fn($record) => "{$record->budget}")
                ->inputMode('decimal')
                ->minValue(0)
                ->prefix('R$')
                ->default(fn($record) => $record->budget)
                ->helperText(fn($record) => "Orçamento original: R$ {$record->budget}"),
        ]);

        $this->action(function ($record, array $data) {
            try {
                $influencerIds = $data['influencer_ids'] ?? [];
                unset($data['influencer_ids']);

                $proposal = $record->proposals()->create([
                    'campaign_announcement_id' => $record->id,
                    'agency_id' => Auth::id(),

                    'message' => $data['message'],
                    'proposed_agency_cut' => $data['proposed_agency_cut'],
                    'proposed_budget' => $data['proposed_budget'],

                ]);

                $proposal->influencers()->sync($influencerIds);

                $record->company->notify(
                    Notification::make()
                        ->title('Proposta recebida para a campanha ' . $record->name)
                        ->body('A agência ' . Auth::user()->name . ' demonstrou interesse em sua campanha')
                        ->toDatabase()
                );

                Notification::make()
                    ->title('Proposta Enviada')
                    ->body('Sua proposta foi enviada com sucesso!')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                Log::error('Erro ao enviar proposta: ' . $e->getMessage());

                Notification::make()
                    ->title('Erro ao enviar Proposta')
                    ->body('Ocorreu um erro ao enviar sua proposta. Tente novamente.')
                    ->danger()
                    ->send();
            }
        });
    }
}
