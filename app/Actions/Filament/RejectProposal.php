<?php

namespace App\Actions\Filament;

use App\Models\Proposal;
use App\UserRoles;
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

            if ($user->role === UserRoles::Agency) {
                return $record->agency_approval !== 'rejected';
            } elseif ($user->role === UserRoles::Company) {
                return $record->company_approval !== 'rejected';
            } elseif ($user->role === UserRoles::Influencer) {
                $approved = DB::table('proposal_user')
                    ->where('proposal_id', $record->id)
                    ->where('user_id', $user->id)
                    ->value('influencer_approval');

                return $approved !== 'rejected';
            }

            return false;
        });

        $this->label('Rejeitar Proposta');
        $this->color('danger');
        $this->icon('heroicon-o-x-circle');

        $this->button();

        $this->action(function (Proposal $record) {
            try {
                if (Auth::user()->role === UserRoles::Company) {
                    $record->update(['company_approval' => 'rejected']);
                } elseif (Auth::user()->role === UserRoles::Agency) {
                    $record->update(['agency_approval' => 'rejected']);
                } elseif (Auth::user()->role === UserRoles::Influencer) {
                    $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'rejected']);
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
                $campaignName = $record->announcement->name;

                $roleLabel = match ($role) {
                    UserRoles::Company => 'Empresa',
                    UserRoles::Agency => 'Agência',
                    UserRoles::Influencer => 'Influenciador',
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
                    UserRoles::Company => $notifyRecipients([$record->agency, ...$record->influencers]),
                    UserRoles::Agency => $notifyRecipients([$record->announcement->company, ...$record->influencers]),
                    UserRoles::Influencer => $notifyRecipients([$record->announcement->company, $record->agency]),
                };
            }
        });
    }
}
