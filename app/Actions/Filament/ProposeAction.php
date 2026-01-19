<?php

namespace App\Actions\Filament;

use App\Helpers\ProposedBudgetCalculator;
use App\Models\Campaign;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
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
        $this->color('success');

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
        $this->modalWidth('4xl');

        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->placeholder('Descreva por que sua agência é ideal para esta campanha...')
                ->rows(4)
                ->maxLength(1000),

            Hidden::make('n_reels')->default(fn($record) => $record->n_reels),
            Hidden::make('n_stories')->default(fn($record) => $record->n_stories),
            Hidden::make('n_carrousels')->default(fn($record) => $record->n_carrousels),

            Group::make()->schema([

                Select::make('influencer_ids')
                    ->label('Selecionar Influenciadores')
                    ->multiple()
                    ->options(
                        function () {
                            return Auth::user()
                                ->influencers()
                                ->where('association_status', 'approved')
                                ->pluck('name', 'users.id');
                        }
                    )->afterStateUpdated(function ($state, callable $set, Get $get) {
                        $borrowedIds = $get('borrowed_influencer_ids') ?? [];

                        $formatPrice = fn($value) => number_format((float) ($value ?? 0), 2, ',', '.');

                        $borrowedInfluencers = Auth::user()
                            ->agency_loans()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', $borrowedIds)
                            ->get()
                            ->map(fn($influencer) => [
                                'user_id' => $influencer->id,
                                'name' => $influencer->name,
                                'stories_price' => $formatPrice($influencer->influencer_info?->stories_price),
                                'reels_price' => $formatPrice($influencer->influencer_info?->reels_price),
                                'carrousel_price' => $formatPrice($influencer->influencer_info?->carrousel_price),
                                'commission_cut' => $influencer->influencer_info->commission_cut,
                            ])
                            ->toArray();

                        $ownInfluencers = Auth::user()
                            ->influencers()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', (array) $state)
                            ->get()
                            ->map(fn($influencer) => [
                                'user_id' => $influencer->id,
                                'name' => $influencer->name,
                                'stories_price' => $formatPrice($influencer->influencer_info?->stories_price),
                                'reels_price' => $formatPrice($influencer->influencer_info?->reels_price),
                                'carrousel_price' => $formatPrice($influencer->influencer_info?->carrousel_price),
                                'commission_cut' => $influencer->influencer_info->commission_cut ?? 0,
                            ])
                            ->toArray();

                        $influencers = array_merge($ownInfluencers, $borrowedInfluencers);

                        $set('selected_influencers', $influencers);
                    })
                    ->searchable()
                    ->live()
                    ->columnSpan(3)
                    ->visible(fn() => Gate::allows('is_agency')),

                Select::make('borrowed_influencer_ids')
                    ->label('Influenciadores Emprestados')
                    ->multiple()
                    ->options(
                        function () {
                            return Auth::user()
                                ->borrowed_influencers()
                                ->pluck('name', 'users.id');
                        }
                    )->afterStateUpdated(function ($state, callable $set, Get $get) {
                        $ownIds = $get('influencer_ids') ?? [];

                        $formatPrice = fn($value) => number_format((float) ($value ?? 0), 2, ',', '.');

                        $borrowedInfluencers = Auth::user()
                            ->agency_loans()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', (array) $state)
                            ->get()
                            ->map(fn($influencer) => [
                                'user_id' => $influencer->id,
                                'name' => $influencer->name,
                                'stories_price' => $formatPrice($influencer->influencer_info?->stories_price),
                                'reels_price' => $formatPrice($influencer->influencer_info?->reels_price),
                                'carrousel_price' => $formatPrice($influencer->influencer_info?->carrousel_price),
                                'commission_cut' => $influencer->influencer_info->commission_cut,
                            ])
                            ->toArray();

                        $ownInfluencers = Auth::user()
                            ->influencers()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', $ownIds)
                            ->get()
                            ->map(fn($influencer) => [
                                'user_id' => $influencer->id,
                                'name' => $influencer->name,
                                'stories_price' => $formatPrice($influencer->influencer_info?->stories_price),
                                'reels_price' => $formatPrice($influencer->influencer_info?->reels_price),
                                'carrousel_price' => $formatPrice($influencer->influencer_info?->carrousel_price),
                                'commission_cut' => $influencer->influencer_info->commission_cut ?? 0,
                            ])
                            ->toArray();

                        $influencers = array_merge($ownInfluencers, $borrowedInfluencers);

                        $set('selected_influencers', $influencers);
                    })
                    ->searchable()
                    ->reactive()
                    ->live()
                    ->columnSpan(2)
                    ->visible(fn() => Gate::allows('is_agency')),

            ])->columns(5),

            Repeater::make('selected_influencers')
                ->hiddenLabel()
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->table([
                    TableColumn::make('Nome'),
                    TableColumn::make('Reels'),
                    TableColumn::make('Stories'),
                    TableColumn::make('Carrossel'),
                    TableColumn::make('Comissão para a Agência')->hiddenHeaderLabel(Gate::denies('is_agency')),
                ])
                ->schema([
                    Hidden::make('user_id'),

                    TextEntry::make('name')
                        ->label('Nome'),

                    TextInput::make('reels_price')
                        ->label('Reels')
                        ->required()
                        ->live(onBlur: true)
                        ->moneyBRL(),

                    TextInput::make('stories_price')
                        ->label('Stories')
                        ->required()
                        ->live(onBlur: true)
                        ->moneyBRL(),

                    TextInput::make('carrousel_price')
                        ->label('Carrossel')
                        ->required()
                        ->live(onBlur: true)
                        ->moneyBRL(),

                    TextInput::make('commission_cut')
                        ->label('Comissão para a Agência')
                        ->mask('99')
                        ->suffix('%')
                        ->required()
                        ->hidden(Gate::denies('is_agency'))
                        ->extraInputAttributes([
                            'style' => 'font-weight: bold; color: #07c9de; text-align: right; border: none; border-radius: 0;',
                        ])
                        ->extraAttributes(
                            [
                                'class' => 'bg-gray-50 dark:bg-white/5 rounded-lg',
                                'style' => 'font-weight: bold; color: #07c9de; text-align: right; border: none; border-radius: 0.5rem; margin: 0px;',
                            ]
                        ),
                ])
                ->default(function (Get $get) {
                    $ownIds = $get('influencer_ids') ?? [];
                    $borrowedIds = $get('borrowed_influencer_ids') ?? [];

                    if (empty($ownIds) && empty($borrowedIds)) {
                        return [];
                    }

                    $influencers = collect();

                    // Get own influencers
                    if (! empty($ownIds)) {
                        $ownInfluencers = Auth::user()
                            ->influencers()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', $ownIds)
                            ->get();

                        $influencers = $influencers->merge($ownInfluencers);
                    }

                    // Get borrowed influencers
                    if (! empty($borrowedIds)) {
                        $borrowedInfluencers = Auth::user()
                            ->agency_loans()
                            ->with('influencer_info')
                            ->select('users.id', 'users.name')
                            ->whereIn('users.id', $borrowedIds)
                            ->get()->toArray();

                        $influencers = $influencers->merge($borrowedInfluencers);
                    }

                    return $influencers
                        ->map(fn($influencer) => [
                            'user_id' => $influencer->id,
                            'name' => $influencer->name,
                            'stories_price' => $influencer->influencer_info->stories_price,
                            'reels_price' => $influencer->influencer_info->reels_price,
                            'carrousel_price' => $influencer->influencer_info->carrousel_price,
                            'commission_cut' => $influencer->influencer_info->commission_cut,
                        ])
                        ->toArray();
                })
                ->live(),

            TextEntry::make('summary')
                ->hiddenLabel()
                ->state(fn($record) => new HtmlString("
                <div  style=' display: flex; justify-content: space-between; flex-wrap:wrap; gap: 0.5rem;'>
                    <div style=' display: flex; gap: 0.5rem;'>
                    <span><strong>Reels:</strong> {$record->n_reels}</span>
                    <span><strong>Stories:</strong> {$record->n_stories}</span>
                    <span><strong>Carrosséis:</strong> {$record->n_carrousels}</span>
                    </div>

                    <span><strong>Comissão:</strong> Porcentagem que o influenciador deixa para a agência</span>
                </div>"))
                ->visible(function (Get $get) {
                    $filterIds = $get('influencer_ids') ?? [];

                    return ! empty($filterIds);
                }),

            Group::make([
                TextInput::make('proposed_agency_cut')
                    ->label('Proposta de Comissão da Campanha')
                    ->prefix('%')
                    ->numeric()
                    ->minValue(0)->placeholder(fn($record) => "{$record->agency_cut}")
                    ->maxValue(100)
                    ->default(fn($record) => $record->agency_cut)
                    ->helperText(fn($record) => "Comissão original: {$record->agency_cut}%"),

                TextInput::make('proposed_budget')
                    ->label('Orçamento Proposto')
                    ->disabled()->reactive()
                    ->dehydrated(false)
                    ->placeholder(function (Get $get, $record) {
                        $influencers = $get('selected_influencers') ?? [];

                        if (empty($influencers)) {
                            return 'Selecione influenciadores';
                        }

                        $parseMoney = fn($value) => (float) str_replace(['.', ','], ['', '.'], (string) $value);

                        // Limpamos os dados antes de enviar para o Calculator
                        $sanitizedInfluencers = collect($influencers)->map(fn($inf) => [
                            'reels_price' => $parseMoney($inf['reels_price'] ?? 0),
                            'stories_price' => $parseMoney($inf['stories_price'] ?? 0),
                            'carrousel_price' => $parseMoney($inf['carrousel_price'] ?? 0),
                        ])->toArray();

                        $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                            $record->n_reels,
                            $record->n_stories,
                            $record->n_carrousels,
                            $sanitizedInfluencers
                        );

                        return 'R$ ' . number_format($range['min'], 2, ',', '.') . ' - R$ ' . number_format($range['max'], 2, ',', '.');
                    })
                    ->helperText('Faixa baseada nos preços dos influenciadores selecionados'),
            ])->columns(2),
        ]);

        $this->action(function (Campaign $record, array $data) {
            try {
                $proposal = $record->proposals()->create([
                    'campaign_id' => $record->id,
                    'agency_id' => Auth::id(),
                    'message' => $data['message'],
                    'proposed_agency_cut' => $data['proposed_agency_cut'],
                    'n_reels' => $data['n_reels'],
                    'n_stories' => $data['n_stories'],
                    'n_carrousels' => $data['n_carrousels'],
                ]);

                $pivotData = [];
                $influencerIds = [];

                foreach ($data['selected_influencers'] ?? [] as $influencer) {
                    $userId = $influencer['user_id'];
                    $influencerIds[] = $userId;

                    $pivotData[$userId] = [
                        'reels_price' => (float) $influencer['reels_price'],
                        'stories_price' => (float) $influencer['stories_price'],
                        'carrousel_price' => (float) $influencer['carrousel_price'],
                        'commission_cut' => (float) $influencer['commission_cut'],
                    ];
                }

                $proposal->influencers()->sync($pivotData);

                $record->company->notify(
                    Notification::make()
                        ->title('Proposta recebida para a campanha ' . $record->name)
                        ->body('A agência ' . Auth::user()->name . ' demonstrou interesse em sua campanha')
                        ->actions([
                            Action::make('view')
                                ->label('Ver proposta')
                                ->url(route('filament.admin.resources.proposals.index', [
                                    'tableAction' => 'viewProposal',
                                    'tableActionRecord' => $proposal->getKey(),
                                ])),
                        ])
                        ->toDatabase()
                );

                foreach ($influencerIds as $influencerId) {
                    User::find($influencerId)?->notify(
                        Notification::make()
                            ->title('Você foi incluído em uma proposta')
                            ->body($proposal->agency->name . ' incluiu você na proposta para a campanha: ' . $record->name)
                            ->info()
                            ->toDatabase()
                    );
                }

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
