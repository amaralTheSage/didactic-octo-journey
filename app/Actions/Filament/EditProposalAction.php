<?php

namespace App\Actions\Filament;

use App\UserRoles;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

        $this->label(fn () => Auth::user()->role === UserRoles::Agency ? 'Editar Proposta' : 'Editar Aprovação');

        $this->tableIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION) ?? Heroicon::PencilSquare);
        $this->groupedIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION_GROUPED) ?? Heroicon::PencilSquare);

        $this->modalHeading(fn () => Auth::user()->role === UserRoles::Agency ? 'Editar Proposta' : 'Editar Aprovação');

        $this->modalWidth('lg');

        $this->visible(fn ($livewire) => $livewire->activeTab === 'proposals' && Gate::allows('is_agency'));

        $this->schema([

            Textarea::make('message')
                ->label('Mensagem')
                ->rows(4)
                ->maxLength(1000)
                ->visible(fn () => Gate::allows('is_agency')),

            Select::make('influencer_ids')
                ->label('Influenciadores')
                ->multiple()
                ->options(
                    fn () => Auth::user()
                        ->influencers()
                        ->pluck('name', 'users.id')
                )
                ->searchable()
                ->visible(fn () => Gate::allows('is_agency')),

            TextInput::make('proposed_agency_cut')
                ->label('Parcela da Agência (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->visible(fn () => Gate::allows('is_agency')),

            // Select::make('company_approval')
            //     ->label('Aprovação da Empresa')
            //     ->options([
            //         'pending'  => 'Pendente',
            //         'approved' => 'Aprovado',
            //         'rejected' => 'Rejeitado',
            //     ])
            //     ->visible(fn() => Gate::allows('is_company')),

            // Select::make('agency_approval')
            //     ->label('Aprovação da Agência')
            //     ->options([
            //         'pending'  => 'Pendente',
            //         'approved' => 'Aprovado',
            //         'rejected' => 'Rejeitado',
            //     ])
            //     ->visible(fn() => Gate::allows('is_agency')),

            // Select::make('influencer_approval')
            //     ->label('Aprovação do Influenciador')
            //     ->options([
            //         'pending'  => 'Pendente',
            //         'approved' => 'Aprovado',
            //         'rejected' => 'Rejeitado',
            //     ])
            //     ->visible(fn() => Gate::allows('is_influencer')),
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

            $data['influencer_ids'] = $record
                ->influencers()
                ->pluck('users.id')
                ->toArray();

            return $data;
        });

        $this->action(function ($record, array $data) {
            try {
                $record->update($data);
            } catch (Exception $e) {
                FacadesLog::error($e);
            } finally {
                $record->influencers()->sync($data['influencer_ids'] ?? []);

                Notification::make()
                    ->title('Proposta atualizada')
                    ->success()
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
