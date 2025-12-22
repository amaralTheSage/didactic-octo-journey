<?php

namespace App\Actions\Filament;

use App\ApprovalStatus;
use App\Models\OngoingCampaign;
use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcceptProposal extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'acceptProposal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Aceitar Proposta');
        $this->color(Color::Green);
        $this->icon('heroicon-o-check-circle');
        $this->button();

        // $this->modalHeading('Confirmar aceitação da Proposta');
        // $this->modalDescription('Tem certeza de que deseja aceitar esta proposta? Isto iniciará a Campanha.');
        // $this->modalSubmitActionLabel('Aceitar')->color('primary');

        $this->action(function (Proposal $record) {

            try {

                // OngoingCampaign::create([
                //     'name' => $announcement->name,
                //     'product_id' => $announcement->product_id,
                //     'company_id' => $announcement->company_id,
                //     'agency_id' => $record->agency_id,
                //     'influencer_id' => $record->influencer_id ?? null,
                //     'category_id' => $announcement->category_id,

                //     'budget' => $announcement->budget,
                //     'agency_cut' => $announcement->agency_cut,

                //     'status_agency' => ApprovalStatus::PENDING,
                //     'status_influencer' => ApprovalStatus::PENDING,
                // ]);

                $record->update(['company_approval' => 'approved']);

                Notification::make()
                    ->title('Proposta Aceita')
                    ->body('A proposta foi aprovada. ')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao aceitar proposta: '.$e->getMessage());
                Notification::make()
                    ->title('Erro ao Aceitar Proposta')
                    ->body('Ocorreu um erro ao iniciar a campanha. Tente novamente.')
                    ->danger()
                    ->send();
            } finally {
                $record->agency->notify(
                    Notification::make()
                        ->title('Proposta de Campanha aceita por '.Auth::user()->name)
                        ->body('A sua proposta de campanha foi aceita.')
                        ->toDatabase()
                );
            }
        });
    }
}
