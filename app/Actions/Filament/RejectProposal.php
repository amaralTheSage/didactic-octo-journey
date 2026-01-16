<?php

namespace App\Actions\Filament;

use App\Enums\UserRole;
use App\Helpers\ProposalChangeDiffFinder;
use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $this->visible(function ($record) {
            $user = Auth::user();

            if ($user->role === UserRole::AGENCY) {
                return $record->agency_approval !== 'rejected';
            } elseif ($user->role === UserRole::COMPANY) {
                return $record->company_approval !== 'rejected';
            } elseif ($user->role === UserRole::INFLUENCER) {
                $approved = DB::table('proposal_user')
                    ->where('proposal_id', $record->id)
                    ->where('user_id', $user->id)
                    ->value('influencer_approval');

                return $approved !== 'rejected';
            }

            return false;
        });

        $this->label(function ($record) {
            return Auth::user()->role === UserRole::INFLUENCER ? 'Não Vou Participar' : 'Rejeitar Proposta';
        });
        $this->color('danger');
        $this->icon('heroicon-o-x-circle');

        $this->button();

        $this->action(function (Proposal $record) {
            try {
                if (Auth::user()->role === UserRole::COMPANY) {
                    $from = $record->company_approval;
                    $record->update(['company_approval' => 'rejected']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'company',
                        $from,
                        'rejected'
                    );
                } elseif (Auth::user()->role === UserRole::AGENCY) {
                    $from = $record->agency_approval;
                    $record->update(['agency_approval' => 'rejected']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'agency',
                        $from,
                        'rejected'
                    );
                } elseif (Auth::user()->role === UserRole::INFLUENCER) {
                    $from = DB::table('proposal_user')
                        ->where('proposal_id', $record->id)
                        ->where('user_id', Auth::id())
                        ->value('influencer_approval');

                    $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'rejected']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'influencer',
                        $from,
                        'rejected'
                    );
                }

                Notification::make()
                    ->title('Proposta rejeitada')
                    ->body('A proposta foi rejeitada. ')
                    ->danger()
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
                $role = Auth::user()->role;
                $userName = Auth::user()->name;
                $campaignName = $record->campaign->name;

                $roleLabel = match ($role) {
                    UserRole::COMPANY => 'Empresa',
                    UserRole::AGENCY => 'Agência',
                    UserRole::INFLUENCER => 'Influenciador',
                    UserRole::CURATOR => 'Curadoria',
                };

                $notification = Notification::make()
                    ->title("{$roleLabel} rejeitou proposta")
                    ->body("{$userName} rejeitou a proposta para {$campaignName}")
                    ->danger()
                    ->toDatabase();

                $notifyRecipients = function ($recipients) use ($notification) {
                    collect($recipients)->each(fn ($recipient) => $recipient->notify($notification));
                };

                match ($role) {
                    UserRole::COMPANY => $notifyRecipients([$record->agency, ...$record->influencers]),
                    UserRole::AGENCY => $notifyRecipients([$record->campaign->company, ...$record->influencers]),
                    UserRole::INFLUENCER => $notifyRecipients([$record->campaign->company, $record->agency]),
                };
            }
        });
    }
}
