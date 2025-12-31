<?php

namespace App\Actions\Filament;

use App\Helpers\ProposedBudgetCalculator;
use App\Models\User;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

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
        $this->color('secondary');

        $this->button();

        $this->visible(
            fn($record) => Gate::allows('is_agency')
                && ! $record->proposals()
                    ->where('agency_id', Auth::id())
                    ->exists()
        );

        $this->modalHeading('Enviar Proposta');
        $this->modalDescription(fn($record) => "Envie sua proposta para a campanha: {$record->name}");
        $this->modalSubmitActionLabel('Enviar Proposta');
        $this->modalWidth('2xl');



        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->placeholder('Descreva por que sua agência é ideal para esta campanha...')
                ->rows(4)
                ->maxLength(1000),

            Select::make('filter_influencers')
                ->label('Selecionar Influenciadores')
                ->multiple()
                ->options(
                    function () {
                        return Auth::user()
                            ->influencers()
                            ->where('association_status', 'approved')
                            ->pluck('name', 'users.id');
                    }
                )
                ->searchable()
                ->reactive()
                ->visible(fn() => Gate::allows('is_agency')),



            RepeatableEntry::make('influencer_ids')->hiddenLabel()
                ->table([
                    TableColumn::make('Nome'),
                    TableColumn::make('Reels'),
                    TableColumn::make('Stories'),
                    TableColumn::make('Carrossel'),
                ])
                ->schema([

                    TextEntry::make('name')
                        ->label('Nome'),

                    TextEntry::make('reels_price')
                        ->label('Reels')
                        ->money('BRL'),

                    TextEntry::make('stories_price')
                        ->label('Stories')
                        ->money('BRL'),

                    TextEntry::make('carrousel_price')
                        ->label('Carrossel')
                        ->money('BRL'),

                ])->default(function (Get $get) {
                    $filterIds = $get('filter_influencers') ?? [];

                    if (empty($filterIds)) {
                        return [];
                    }

                    return Auth::user()
                        ->influencers()
                        ->with('influencer_info')
                        ->select('users.id', 'users.name')
                        ->whereIn('users.id', $filterIds)
                        ->get()
                        ->map(fn($influencer) => [
                            'user_id'         => $influencer->id,
                            'name'            => $influencer->name,
                            'stories_price'   => $influencer->influencer_info->stories_price,
                            'reels_price'     => $influencer->influencer_info->reels_price,
                            'carrousel_price' => $influencer->influencer_info->carrousel_price,
                        ])
                        ->toArray();
                })
                ->reactive(),


            TextEntry::make('summary')
                ->hiddenLabel()->visible(function (Get $get) {
                    $filterIds = $get('filter_influencers') ?? [];

                    return !empty($filterIds);
                })
                ->state(fn($record) => new HtmlString("
          <div style='text-align: right; display: flex; justify-content: flex-end; gap: 0.5rem;'>
            <span><strong>Reels:</strong> {$record->n_reels}</span>
            <span><strong>Stories:</strong> {$record->n_stories}</span>
            <span><strong>Carrosséis:</strong> {$record->n_carrousels}</span>
        </div>
        ")),


            Group::make([
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
                    ->disabled()->reactive()
                    ->placeholder(function (Get $get, $record) {
                        $influencers = $get('influencer_ids') ?? [];

                        if (empty($influencers)) {
                            return 'Selecione influenciadores';
                        }

                        $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                            $record->n_reels,
                            $record->n_stories,
                            $record->n_carrousels,
                            $influencers
                        );

                        return 'R$ ' . number_format($range['min'], 2, ',', '.') . ' - R$ ' . number_format($range['max'], 2, ',', '.');
                    })
                    ->helperText('Faixa baseada nos preços dos influenciadores selecionados'),
            ])->columns(2)
        ]);

        $this->action(function ($record, array $data) {
            try {


                $proposal = $record->proposals()->create([
                    'campaign_announcement_id' => $record->id,
                    'agency_id' => Auth::id(),

                    'message' => $data['message'],
                    'proposed_agency_cut' => $data['proposed_agency_cut'],

                ]);

                $influencerIds = collect($data['influencer_ids'] ?? [])
                    ->pluck('user_id')
                    ->values()
                    ->toArray();


                unset($data['influencer_ids']);
                unset($data['proposed_budget']);

                $proposal->influencers()->sync($influencerIds);

                $record->company->notify(
                    Notification::make()
                        ->title('Proposta recebida para a campanha ' . $record->name)
                        ->body('A agência ' . Auth::user()->name . ' demonstrou interesse em sua campanha')
                        ->actions([
                            Action::make('view')
                                ->label('Ver proposta')
                                ->url(route('filament.admin.resources.campaign-announcements.index', [
                                    'activeTab' => 'proposals',
                                    'tableAction' => 'viewProposal',
                                    'tableActionRecord' => $proposal->getKey(),
                                ])),
                        ])
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
