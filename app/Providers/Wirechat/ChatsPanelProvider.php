<?php

namespace App\Providers\Wirechat;

use App\Models\User;
use Wirechat\Wirechat\Http\Resources\WirechatUserResource;
use Wirechat\Wirechat\Panel;
use Wirechat\Wirechat\PanelProvider;

class ChatsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('chats')
            ->path('chats')
            ->middleware(['web', 'auth'])
            ->createGroupAction()
            ->createChatAction()->createGroupAction()
            ->clearChatAction(false)->maxGroupMembers(10)
            ->webPushNotifications()
            ->deleteChatAction(false)
            ->searchableAttributes(['name', 'email'])
            ->searchUsersUsing(function (string $needle) {
                $needle = trim($needle);

                return WirechatUserResource::collection(
                    User::query()
                        ->where('name', 'ILIKE', "%{$needle}%")
                        ->orWhere('email', 'ILIKE', "%{$needle}%")
                        ->limit(20)
                        ->get()
                );
            })
            ->attachments()
            ->default();
    }
}
