<?php

namespace App\Actions\Filament;

use App\Models\User;
use App\Services\ChatService;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ChatAction extends Action
{
    protected ?Closure $redirectUrlUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'chat';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedChatBubbleLeftEllipsis);
        $this->label(__('Chat'));
        $this->defaultColor('secondary');
        $this->tableIcon(icon: Heroicon::OutlinedChatBubbleLeftEllipsis);

        $this->modalHeading(fn (User $record) => "Chat com {$record->name}");
        $this->modalDescription('Deseja iniciar uma nova conversa ou continuar uma conversa existente?');
        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);
        $this->modalWidth(Width::Large);

        $this->modalFooterActions(function (User $record) {

            return [
                Action::make('newChat')
                    ->label('Iniciar Nova Conversa')
                    ->icon('heroicon-o-plus-circle')

                    ->action(function ($record) {
                        $chat = ChatService::createChat([$record->id]);

                        if (is_array($chat) && isset($chat['error'])) {
                            Notification::make()
                                ->title('Erro')
                                ->body($chat['error'])
                                ->danger()
                                ->send();

                            return;
                        }

                        return redirect()->route('chats.show', ['chat' => $chat]);
                    }),

                Action::make('viewChats')
                    ->label('Ver todas conversas')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->action(fn () => redirect()->route('chats.index')),
            ];
        });

        $this->visible(function (User $record) {
            return ChatService::validateChatPermission(Auth::user(), $record)['allowed'];
        });

        $this->before(function (User $record) {
            $validation = ChatService::validateChatPermission(Auth::user(), $record);

            if (! $validation['allowed']) {
                $this->halt();
                $this->sendNotification(
                    title: 'Cannot Start Chat',
                    body: $validation['message'],
                    type: 'warning'
                );
            }
        });
    }

    public function redirectUrlUsing(?Closure $callback): static
    {
        $this->redirectUrlUsing = $callback;

        return $this;
    }
}
