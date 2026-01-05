<?php

namespace App\Actions\Filament;

use App\Helpers\ProposedBudgetCalculator;
use App\Models\User;
use App\UserRoles;
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

        $this->label(fn() =>  'Editar Proposta');

        $this->tableIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION) ?? Heroicon::PencilSquare);
        $this->groupedIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION_GROUPED) ?? Heroicon::PencilSquare);

        $this->modalHeading(fn() => Auth::user()->role === UserRoles::Agency ? 'Editar Proposta' : 'Editar Aprovação');

        $this->modalWidth('2xl');

        $this->visible(fn($livewire) => $livewire->activeTab === 'proposals' && Gate::denies('is_influencer'));

        $this->schema([
            Textarea::make('message')
                ->label('Mensagem')
                ->rows(4)
                ->maxLength(1000)
                ->visible(fn() => Gate::allows('is_agency')),

            Select::make('influencer_ids')
                ->label('Selecionar Influenciadores')
                ->multiple()
                ->getOptionLabelUsing(fn($value) => User::find($value)?->name)
                ->options(
                    function ($record) {
                        return $record->agency
                            ->influencers()
                            ->where('association_status', 'approved')
                            ->pluck('name', 'users.id');
                    }
                )->afterStateUpdated(function ($state, callable $set) {
                    if (empty($state)) {
                        $set('selected_influencers', []);
                        return;
                    }

                    $influencers = Auth::user()
                        ->influencers()
                        ->with('influencer_info')
                        ->select('users.id', 'users.name')
                        ->whereIn('users.id', $state)
                        ->get()
                        ->map(fn($influencer) => [
                            'user_id' => $influencer->id,
                            'name' => $influencer->name,
                            'stories_price' => $influencer->influencer_info->stories_price,
                            'reels_price' => $influencer->influencer_info->reels_price,
                            'carrousel_price' => $influencer->influencer_info->carrousel_price,
                        ])
                        ->toArray();

                    $set('selected_influencers', $influencers);
                })
                ->searchable()
                ->reactive()
                ->visible(fn() => Gate::denies('is_influencer')),

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
                ])

                ->schema([
                    Hidden::make('user_id'),
                    TextEntry::make('name')->label('Nome'),
                    TextInput::make('reels_price')->label('Reels')->numeric()->prefix('R$')->required(),
                    TextInput::make('stories_price')->label('Stories')->numeric()->prefix('R$')->required(),
                    TextInput::make('carrousel_price')->label('Carrossel')->numeric()->prefix('R$')->required(),
                ])
                ->reactive()
                ->visible(fn() => Gate::allows('is_agency') || Gate::allows('is_company')),

            TextEntry::make('summary')
                ->hiddenLabel()
                ->state(fn($record) => new HtmlString("
                    <div style='text-align: right; display: flex; justify-content: flex-end; gap: 0.5rem;'>
                        <span><strong>Reels:</strong> {$record->announcement->n_reels}</span>
                        <span><strong>Stories:</strong> {$record->announcement->n_stories}</span>
                        <span><strong>Carrosséis:</strong> {$record->announcement->n_carrousels}</span>
                    </div>"))
                ->visible(function (Get $get) {
                    $filterIds = $get('influencer_ids') ?? [];

                    return ! empty($filterIds);
                }),

            Group::make([
                TextInput::make('proposed_agency_cut')
                    ->label('Parcela da Agência (%)')
                    ->numeric()
                    ->minValue(0)->placeholder(fn($record) => "{$record->announcement->agency_cut}")
                    ->maxValue(100)
                    ->visible(fn() => Gate::denies('is_influencer')),

                TextInput::make('proposed_budget')
                    ->label('Orçamento Proposto')
                    ->disabled()->reactive()
                    ->placeholder(function (Get $get, $record) {
                        $influencers = $get('selected_influencers') ?? [];

                        if (empty($influencers)) {
                            return 'Selecione influenciadores';
                        }

                        $range = ProposedBudgetCalculator::calculateInfluencerBudgetRange(
                            $record->announcement->n_reels,
                            $record->announcement->n_stories,
                            $record->announcement->n_carrousels,
                            $influencers
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

            $data['influencer_ids'] = $record->influencers()->pluck('users.id')->toArray();

            $data['selected_influencers'] = $record->influencers()
                ->get()
                ->map(fn($influencer) => [
                    'user_id' => $influencer->id,
                    'name' => $influencer->name,
                    'reels_price' => $influencer->pivot->reels_price ?? $influencer->influencer_info->reels_price ?? 0,
                    'stories_price' => $influencer->pivot->stories_price ?? $influencer->influencer_info->stories_price ?? 0,
                    'carrousel_price' => $influencer->pivot->carrousel_price ?? $influencer->influencer_info->carrousel_price ?? 0,
                ])
                ->toArray();

            return $data;
        });

        $this->action(function ($record, array $data) {
            try {
                $record->update([
                    'message' => $data['message'] ?? null,
                    'proposed_agency_cut' => $data['proposed_agency_cut'] ?? null,
                ]);

                $pivotData = [];
                foreach ($data['selected_influencers'] ?? [] as $influencer) {
                    $pivotData[$influencer['user_id']] = [
                        'reels_price' => (float)$influencer['reels_price'],
                        'stories_price' => (float)$influencer['stories_price'],
                        'carrousel_price' => (float)$influencer['carrousel_price'],
                    ];
                }

                $record->influencers()->sync($pivotData);

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
