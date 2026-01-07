<?php

namespace App\Actions\Filament;

use App\Models\User;
use App\Services\ChatService;
use Closure;
use Filament\Actions\Action;
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

        $this->url(fn (User $record) => route('chats.create', ['users' => [$record->id]]));
        $this->openUrlInNewTab();

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
