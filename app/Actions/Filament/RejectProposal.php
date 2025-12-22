<?php

namespace App\Actions\Filament;

use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RejectProposal extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'rejectProposal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Rejeitar Proposta');
        $this->color('danger');
        $this->icon('heroicon-o-x-circle');

        $this->button();
        $this->visible(
            fn ($record, $livewire) => Gate::allows('is_company')
                && $record
                    ->exists()
        );

        $this->action(function (Proposal $record) {
            try {
                $record->update(['company_approval' => 'rejected']);

                Notification::make()
                    ->title('Proposta rejeitar')
                    ->body('A proposta foi rejeitada. ')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao rejeitar proposta: '.$e->getMessage());
                Notification::make()
                    ->title('Erro ao rejeitarr Proposta')
                    ->body('Ocorreu um erro ao iniciar a campanha. Tente novamente.')
                    ->danger()
                    ->send();
            } finally {
                $record->agency->notify(
                    Notification::make()
                        ->title('Proposta de Campanha rejeitada por '.Auth::user()->name)
                        ->body('A sua proposta de campanha foi rejeitada.')
                        ->toDatabase()
                );
            }
        });
    }
}
