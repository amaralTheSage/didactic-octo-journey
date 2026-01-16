<?php

namespace App\Actions\Filament;

use App\Enums\UserRole;
use App\Helpers\ProposalChangeDiffFinder;
use App\Models\Chat;
use App\Models\Proposal;
use App\Services\ChatService;
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

        $this->label(function ($record) {
            return Auth::user()->role === UserRole::INFLUENCER ? 'Vou Participar' : 'Aprovar Proposta';
        });
        $this->color(Color::Green);
        $this->icon('heroicon-o-check-circle');
        $this->button();

        $this->visible(function ($record) {
            $user = Auth::user();

            if ($user->role === UserRole::AGENCY) {
                return $record->agency_approval !== 'approved';
            } elseif ($user->role === UserRole::COMPANY) {
                return $record->company_approval !== 'approved';
            } elseif ($user->role === UserRole::INFLUENCER) {
                $approved = DB::table('proposal_user')
                    ->where('proposal_id', $record->id)
                    ->where('user_id', $user->id)
                    ->value('influencer_approval');

                return $approved !== 'approved';
            }

            return false;
        });

        $this->action(function (Proposal $record) {

            try {
                if (Auth::user()->role === UserRole::COMPANY) {
                    $from = $record->company_approval;
                    $record->update(['company_approval' => 'approved']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'company',
                        $from,
                        'approved'
                    );
                } elseif (Auth::user()->role === UserRole::AGENCY) {
                    $from = $record->agency_approval;
                    $record->update(['agency_approval' => 'approved']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'agency',
                        $from,
                        'approved'
                    );
                } elseif (Auth::user()->role === UserRole::INFLUENCER) {
                    $from = DB::table('proposal_user')
                        ->where('proposal_id', $record->id)
                        ->where('user_id', Auth::id())
                        ->value('influencer_approval');

                    $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'approved']);

                    ProposalChangeDiffFinder::logProposalApproval(
                        $record,
                        'influencer',
                        $from,
                        'approved'
                    );
                }

                Notification::make()
                    ->title('Proposta Aprovada')
                    ->body('A proposta foi aprovada. ')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao aprovar proposta: '.$e->getMessage());
                Notification::make()
                    ->title('Erro ao Aprovar Proposta')
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
                    ->title("{$roleLabel} aprovou proposta")
                    ->body("{$userName} aprovou a proposta para {$campaignName}")
                    ->success()
                    ->toDatabase();

                $notifyRecipients = function ($recipients) use ($notification) {
                    collect($recipients)->each(fn ($recipient) => $recipient->notify($notification));
                };

                match ($role) {
                    UserRole::COMPANY => $notifyRecipients([$record->agency, ...$record->influencers]),
                    UserRole::AGENCY => $notifyRecipients([$record->campaign->company, ...$record->influencers]),
                    UserRole::INFLUENCER => $notifyRecipients([$record->campaign->company, $record->agency]),
                };

                if ($role === UserRole::COMPANY) {
                    $chat = Chat::query()
                        ->where('proposal_id', $record->id)
                        ->whereHas('users', fn ($q) => $q->where('users.id', Auth::user()->id))
                        ->first();

                    if (! $chat) {
                        $chat = ChatService::createChat(
                            [$record->agency_id],
                            $record->id
                        );
                    }

                    if ($chat instanceof Chat) {
                        return redirect()->route('chats.show', ['chat' => $chat]);
                    }
                }
            }
        });
    }
}
