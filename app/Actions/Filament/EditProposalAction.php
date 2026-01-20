<?php

namespace App\Actions\Filament;

use App\Enums\UserRole;
use App\Helpers\ProposalChangeDiffFinder;
use App\Helpers\ProposedBudgetCalculator;
use App\Models\Proposal;
use App\Models\ProposalChangeLog;
use App\Models\User;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\View\ActionsIconAlias;
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
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\HtmlString;

class EditProposalAction extends Action
{
    protected ?Closure $mutateRecordDataUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'editProposal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn() => 'Editar Proposta');

        $this->extraModalFooterActions([
            ViewChangeLogs::make()->action(fn($record) => dd($record)), // dxa aq
        ]);

        $this->tableIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION) ?? Heroicon::PencilSquare);
        $this->groupedIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION_GROUPED) ?? Heroicon::PencilSquare);

        $this->modalHeading(fn() => Auth::user()->role === UserRole::AGENCY ? 'Editar Proposta' : 'Editar Aprovação');

        $this->modalWidth('3xl');

        $this->visible(Gate::denies('is_influencer'));

        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->rows(4)
                ->maxLength(1000)
                ->visible(fn() => Gate::allows('is_agency')),

            Group::make()->schema([

                Select::make('influencer_ids')
                    ->label('Selecionar Influenciadores')
                    ->multiple()
                    ->options(
                        function (Proposal $record) {
                            $user = Gate::allows('is_agency') ? Auth::user() : $record->agency;

                            return $user->influencers()
                                ->where('association_status', 'approved')
                                ->pluck('name', 'users.id');
                        }

                    )->afterStateUpdated(function ($state, callable $set, Get $get, $record) {
                        $borrowedIds = $get('borrowed_influencer_ids') ?? [];

                        $formatPrice = fn($value) => number_format((float) ($value ?? 0), 2, ',', '.');

                        $borrowedInfluencers = $record->agency
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

                        $ownInfluencers = $record->agency
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
                    ->reactive()->columnSpan(3),

                Select::make('borrowed_influencer_ids')
                    ->label('Influenciadores Emprestados')
                    ->multiple()
                    ->options(
                        function ($record) {
                            $user = Gate::allows('is_agency') ? Auth::user() : $record->agency;

                            return $user->borrowed_influencers()
                                ->pluck('name', 'users.id');
                        }
                    )->afterStateUpdated(function ($state, callable $set, Get $get, $record) {
                        $ownIds = $get('influencer_ids') ?? [];

                        $formatPrice = fn($value) => number_format((float) ($value ?? 0), 2, ',', '.');

                        $borrowedInfluencers = $record->agency
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

                        $ownInfluencers = $record->agency
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
                    ->columnSpan(2),
            ])->columns(5),

            Repeater::make('selected_influencers')
                ->hiddenLabel()
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->table(array_filter([
                    TableColumn::make('Nome'),
                    TableColumn::make('Reels'),
                    TableColumn::make('Stories'),
                    TableColumn::make('Carrossel'),
                    Gate::allows('is_agency') ? TableColumn::make('Comissão') : null,
                ]))
                ->schema([
                    Hidden::make('user_id'),

                    TextEntry::make('name')
                        ->label('Nome'),

                    TextInput::make('reels_price')
                        ->label('Reels')
                        ->required()
                        ->moneyBRL(),

                    TextInput::make('stories_price')
                        ->label('Stories')
                        ->required()
                        ->moneyBRL(),

                    TextInput::make('carrousel_price')
                        ->label('Carrossel')
                        ->required()
                        ->moneyBRL(),

                    TextInput::make('commission_cut')
                        ->label('Comissão')
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
                ->dehydrated()
                ->state(fn($record) => new HtmlString("
                    <div style='text-align: right; display: flex; justify-content: flex-end; gap: 0.5rem;'>
                        <span><strong>Reels:</strong> {$record->n_reels}</span>
                        <span><strong>Stories:</strong> {$record->n_stories}</span>
                        <span><strong>Carrosséis:</strong> {$record->n_carrousels}</span>
                    </div>"))
                ->visible(function (Get $get) {
                    $filterIds = $get('influencer_ids') ?? [];

                    return ! empty($filterIds);
                }),

            Group::make([
                TextInput::make('n_reels')->label('Reels')->numeric()->required()->live(),
                TextInput::make('n_stories')->label('Stories')->numeric()->required()->live(),
                TextInput::make('n_carrousels')->label('Carrosséis')->numeric()->required()->live(),
            ])->columns(3)
                ->visible(fn() => Gate::allows('is_company')),

            Group::make([
                TextInput::make('proposed_agency_cut')
                    ->label('Comissão da Campanha (%)')
                    ->helperText('Percentual do lucro da campanha destinado à agência e aos influenciadores')
                    ->numeric()
                    ->minValue(0)
                    ->placeholder(fn($record) => "{$record->campaign->agency_cut}")
                    ->maxValue(100)
                    ->visible(fn() => Gate::denies('is_influencer')),

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

                        // Pega os valores dos inputs ou usa os valores da campaign como fallback
                        $nReels = $get('n_reels') ?? $record->campaign->n_reels ?? 0;
                        $nStories = $get('n_stories') ?? $record->campaign->n_stories ?? 0;
                        $nCarrousels = $get('n_carrousels') ?? $record->campaign->n_carrousels ?? 0;

                        $parseMoney = fn($value) => (float) str_replace(['.', ','], ['', '.'], (string) $value);

                        $sanitizedInfluencers = collect($influencers)->map(fn($inf) => [
                            'reels_price' => $parseMoney($inf['reels_price'] ?? 0),
                            'stories_price' => $parseMoney($inf['stories_price'] ?? 0),
                            'carrousel_price' => $parseMoney($inf['carrousel_price'] ?? 0),
                        ])->toArray();

                        $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                            $nReels,
                            $nStories,
                            $nCarrousels,
                            $sanitizedInfluencers
                        );


                        return 'R$ ' . number_format($range['min'], 2, ',', '.') . ' - R$ ' . number_format($range['max'], 2, ',', '.');
                    })
                    ->helperText('Faixa baseada nos preços dos influenciadores selecionados'),
            ])->columns(2),

        ]);

        $this->fillForm(function (HasActions&HasSchemas $livewire, Model $record, ?Table $table): array {
            $translatableContentDriver = $livewire->makeFilamentTranslatableContentDriver();

            if ($translatableContentDriver) {
                $data = $translatableContentDriver->getRecordAttributesToArray($record);
            } else {
                $data = $record->attributesToArray();
            }

            $relationship = $table?->getRelationship();

            if ($relationship instanceof BelongsToMany) {
                $pivot = $record->getRelationValue($relationship->getPivotAccessor());

                $pivotColumns = $relationship->getPivotColumns();

                if ($translatableContentDriver) {
                    $data = [
                        ...$data,
                        ...Arr::only($translatableContentDriver->getRecordAttributesToArray($pivot), $pivotColumns),
                    ];
                } else {
                    $data = [
                        ...$data,
                        ...Arr::only($pivot->attributesToArray(), $pivotColumns),
                    ];
                }
            }

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data]);
            }

            $data = $record->attributesToArray();

            $allInfluencerIds = $record->influencers()->pluck('users.id')->toArray();

            $agencyInfluencerIds = $record->agency->influencers()->pluck('users.id')->toArray();

            $data['influencer_ids'] = array_values(array_intersect($allInfluencerIds, $agencyInfluencerIds));
            $data['borrowed_influencer_ids'] = array_values(array_diff($allInfluencerIds, $agencyInfluencerIds));

            $formatPrice = fn($value) => number_format((float) ($value ?? 0), 2, ',', '.');

            $data['selected_influencers'] = $record->influencers()
                ->with('influencer_info')
                ->get()
                ->map(fn($influencer) => [
                    'user_id' => $influencer->id,
                    'name' => $influencer->name,
                    'reels_price' => $formatPrice($influencer->pivot->reels_price ?? $influencer->influencer_info?->reels_price),
                    'stories_price' => $formatPrice($influencer->pivot->stories_price ?? $influencer->influencer_info?->stories_price),
                    'carrousel_price' => $formatPrice($influencer->pivot->carrousel_price ?? $influencer->influencer_info?->carrousel_price),
                    'commission_cut' => $influencer->pivot->commission_cut ?? $influencer->influencer_info?->commission_cut,
                ])
                ->toArray();

            return $data;
        });

        $this->modalSubmitActionLabel('Editar Proposta');

        $this->action(function ($record, array $data) {
            try {
                // --- capture "before" ---
                $beforeProposal = Arr::only($record->attributesToArray(), [
                    'message',
                    'proposed_agency_cut',
                    'n_reels',
                    'n_stories',
                    'n_carrousels',
                ]);

                $beforeInfluencers = $record->influencers()
                    ->get()
                    ->mapWithKeys(fn($inf) => [
                        $inf->id => [
                            'name' => $inf->name,
                            'reels_price' => (float) $inf->pivot->reels_price,
                            'stories_price' => (float) $inf->pivot->stories_price,
                            'carrousel_price' => (float) $inf->pivot->carrousel_price,
                            'commission_cut' => (int) $inf->pivot->commission_cut,
                        ],
                    ])->toArray();

                // --- update proposal fields (defensive) ---
                $record->update(array_filter([
                    'message' => $data['message'] ?? null,
                    'proposed_agency_cut' => $data['proposed_agency_cut'] ?? null,
                    'n_reels' => $data['n_reels'] ?? null,
                    'n_stories' => $data['n_stories'] ?? null,
                    'n_carrousels' => $data['n_carrousels'] ?? null,
                    'commission_cut' => $data['commission_cut'] ?? null,
                ], fn($v) => ! is_null($v)));

                // --- build pivot data and detect price changes for notifications ---
                $oldPrices = $beforeInfluencers;
                $pivotData = [];
                $priceChanges = [];

                foreach ($data['selected_influencers'] ?? [] as $influencer) {
                    $userId = $influencer['user_id'];
                    $newPrices = [
                        'reels_price' => (float) $influencer['reels_price'],
                        'stories_price' => (float) $influencer['stories_price'],
                        'carrousel_price' => (float) $influencer['carrousel_price'],
                        'commission_cut' => (int) ($influencer['commission_cut'] ?? $beforeInfluencers[$userId]['commission_cut'] ?? 0),
                    ];

                    $pivotData[$userId] = $newPrices;

                    if (isset($oldPrices[$userId])) {
                        $old = $oldPrices[$userId];
                        if (
                            ($old['reels_price'] ?? $old['reels'] ?? null) != $newPrices['reels_price'] ||
                            ($old['stories_price'] ?? $old['stories'] ?? null) != $newPrices['stories_price'] ||
                            ($old['carrousel_price'] ?? $old['carrousel'] ?? null) != $newPrices['carrousel_price'] ||
                            ($old['commission_cut'] ?? $old['commission_cut'] ?? null) != $newPrices['commission_cut']
                        ) {
                            $priceChanges[$userId] = [
                                'from' => $old,
                                'to' => $newPrices,
                            ];
                        }
                    } else {
                        // new influencer added — keep for notifications if desired
                        $priceChanges[$userId] = [
                            'from' => null,
                            'to' => $newPrices,
                        ];
                    }
                }

                // --- sync pivot (this actually adds/removes) ---
                $record->influencers()->sync($pivotData);

                // --- capture "after" ---
                $afterProposal = Arr::only($record->fresh()->attributesToArray(), [
                    'message',
                    'proposed_agency_cut',
                    'n_reels',
                    'n_stories',
                    'n_carrousels',
                    'commission_cut',
                ]);

                $afterInfluencers = $record->influencers()
                    ->get()
                    ->mapWithKeys(fn($inf) => [
                        $inf->id => [
                            'name' => $inf->name,
                            'reels_price' => (float) $inf->pivot->reels_price,
                            'stories_price' => (float) $inf->pivot->stories_price,
                            'carrousel_price' => (float) $inf->pivot->carrousel_price,
                            'commission_cut' => (int) $inf->pivot->commission_cut,
                        ],
                    ])->toArray();

                // --- proposal diff ---
                $proposalDiff = ProposalChangeDiffFinder::findDiff($beforeProposal, $afterProposal);

                // --- influencer diffs: added, removed, modified ---
                $beforeIds = array_keys($beforeInfluencers);
                $afterIds = array_keys($afterInfluencers);

                $added = array_diff($afterIds, $beforeIds);
                $removed = array_diff($beforeIds, $afterIds);
                $kept = array_intersect($beforeIds, $afterIds);

                $influencerDiff = [];

                // Added influencers
                foreach ($added as $id) {
                    $influencerDiff[$id] = [
                        'name' => $afterInfluencers[$id]['name'] ?? null,
                        'added' => true,
                        'from' => null,
                        'to' => Arr::except($afterInfluencers[$id], ['name']),
                    ];
                }

                // Removed influencers
                foreach ($removed as $id) {
                    $influencerDiff[$id] = [
                        'name' => $beforeInfluencers[$id]['name'] ?? null,
                        'removed' => true,
                        'from' => Arr::except($beforeInfluencers[$id] ?? [], ['name']),
                        'to' => null,
                    ];
                }

                // Modified (prices changed)
                foreach ($kept as $id) {
                    $from = Arr::except($beforeInfluencers[$id] ?? [], ['name']);
                    $to = Arr::except($afterInfluencers[$id] ?? [], ['name']);

                    $diff = ProposalChangeDiffFinder::findDiff($from, $to);

                    if (! empty($diff)) {
                        $influencerDiff[$id] = [
                            'name' => $afterInfluencers[$id]['name'] ?? $beforeInfluencers[$id]['name'] ?? null,
                            'changes' => $diff,
                            'from' => $from,
                            'to' => $to,
                        ];
                    }
                }

                // --- persist change log only if something changed ---
                if (! empty($proposalDiff) || ! empty($influencerDiff)) {
                    ProposalChangeLog::create([
                        'proposal_id' => $record->id,
                        'user_id' => Auth::id(),
                        'changes' => [
                            'proposal' => $proposalDiff,
                            'influencers' => $influencerDiff,
                        ],
                    ]);
                }

                // --- notifications (unchanged behaviour) ---
                foreach ($priceChanges as $userId => $change) {
                    User::find($userId)?->notify(
                        Notification::make()
                            ->title('Preços atualizados na proposta')
                            ->body('Os valores da sua participação na campanha ' . $record->campaign->name . ' foram atualizados por ' . Auth::user()->name)
                            ->info()
                            ->toDatabase()
                    );
                }

                if (Auth::user()->role === UserRole::COMPANY && ! empty($priceChanges)) {
                    $record->agency->notify(
                        Notification::make()
                            ->title('Proposta atualizada pela empresa')
                            ->body(Auth::user()->name . ' atualizou os valores dos influenciadores na proposta para ' . $record->campaign->name)
                            ->info()
                            ->toDatabase()
                    );
                }

                Notification::make()
                    ->title('Proposta atualizada')
                    ->success()
                    ->send();
            } catch (Exception $e) {
                FacadesLog::error($e);

                Notification::make()
                    ->title('Erro ao atualizar proposta')
                    ->danger()
                    ->send();
            }
        });
    }

    public function mutateRecordDataUsing(?Closure $callback): static
    {
        $this->mutateRecordDataUsing = $callback;

        return $this;
    }
}
