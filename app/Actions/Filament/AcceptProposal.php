<?php

namespace App\Actions\Filament;

use App\Models\Chat;
use App\Models\Proposal;
use App\Services\ChatService;
use App\Enums\UserRoles;
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

        $this->label('Aprovar Proposta');
        $this->color(Color::Green);
        $this->icon('heroicon-o-check-circle');
        $this->button();

        $this->visible(function ($record) {
            $user = Auth::user();

            if ($user->role === UserRoles::Agency) {
                return $record->agency_approval !== 'approved';
            } elseif ($user->role === UserRoles::Company) {
                return $record->company_approval !== 'approved';
            } elseif ($user->role === UserRoles::Influencer) {
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
                if (Auth::user()->role === UserRoles::Company) {
                    $record->update(['company_approval' => 'approved']);
                } elseif (Auth::user()->role === UserRoles::Agency) {
                    $record->update(['agency_approval' => 'approved']);
                } elseif (Auth::user()->role === UserRoles::Influencer) {
                    $record->influencers()->updateExistingPivot(Auth::id(), ['influencer_approval' => 'approved']);
                }

                Notification::make()
                    ->title('Proposta Aprovada')
                    ->body('A proposta foi aprovada. ')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao aprovar proposta: ' . $e->getMessage());
                Notification::make()
                    ->title('Erro ao Aprovar Proposta')
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
                    ->title("{$roleLabel} aprovou proposta")
                    ->body("{$userName} aprovou a proposta para {$campaignName}")
                    ->success()
                    ->toDatabase();

                $notifyRecipients = function ($recipients) use ($notification) {
                    collect($recipients)->each(fn($recipient) => $recipient->notify($notification));
                };

                match ($role) {
                    UserRoles::Company => $notifyRecipients([$record->agency, ...$record->influencers]),
                    UserRoles::Agency => $notifyRecipients([$record->announcement->company, ...$record->influencers]),
                    UserRoles::Influencer => $notifyRecipients([$record->announcement->company, $record->agency]),
                };

                if ($role === UserRoles::Company) {
                    $chat = Chat::query()
                        ->where('proposal_id', $record->id)
                        ->whereHas('users', fn($q) => $q->where('users.id', Auth::user()->id))
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
