<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;
use App\UserRoles;
use Illuminate\Support\Facades\Auth;

//      Criei essa classe como forma de organizar a 
//      lógica dos chats. Caso necessário adições ao 
//      sistema do chat, o ideal é que seja organizado aqui.

class ChatService
{
    public static function createChat(array $selectedUserIds): Chat|array
    {

        $finalUserIds = self::processChatParticipants(Auth::user(), $selectedUserIds);

        if (isset($finalUserIds['error'])) {
            return $finalUserIds;
        }

        $chat = Chat::create([]);
        $chat->users()->attach($finalUserIds);
        $chat->load('users');

        return $chat;
    }

    public static function processChatParticipants(User $currentUser, array $selectedUserIds): array
    {
        $finalUserIds = [$currentUser->id];
        $selectedUsers = User::whereIn('id', $selectedUserIds)->get();

        foreach ($selectedUsers as $user) {
            $result = match ($currentUser->role) {
                UserRoles::Company =>  self::handleCompanyChat($currentUser, $user, $finalUserIds),
                UserRoles::Agency =>  self::handleAgencyChat($currentUser, $user, $finalUserIds),
                UserRoles::Influencer =>  self::handleInfluencerChat($currentUser, $user, $finalUserIds),
                default => $finalUserIds,
            };

            if (isset($result['error'])) {
                return $result;
            }

            $finalUserIds = $result;
        }

        return array_unique($finalUserIds);
    }

    protected static function handleCompanyChat(User $company, User $targetUser, array $currentParticipants): array
    {
        if ($targetUser->role === UserRoles::Agency) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        if ($targetUser->role === UserRoles::Influencer) {
            $currentParticipants[] = $targetUser->id;

            $agencyId = $targetUser->influencer_info['agency_id'] ?? null;

            if ($agencyId) {
                $currentParticipants[] = $agencyId;
            }

            return $currentParticipants;
        }

        if ($targetUser->role === UserRoles::Company) {
            return ['error' => 'Companies cannot start chats with other companies.'];
        }

        return $currentParticipants;
    }

    protected static function handleAgencyChat(User $agency, User $targetUser, array $currentParticipants): array
    {
        if ($targetUser->role === UserRoles::Company) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        if ($targetUser->role === UserRoles::Influencer) {
            $influencerAgencyId = $targetUser->influencer_info['agency_id'] ?? null;

            if ($influencerAgencyId !== $agency->id) {
                return ['error' => 'You can only add influencers that belong to your agency.'];
            }

            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        if ($targetUser->role === UserRoles::Agency) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        return $currentParticipants;
    }

    protected static function  handleInfluencerChat(User $influencer, User $targetUser, array $currentParticipants): array
    {
        if (in_array($targetUser->role, [UserRoles::Company, UserRoles::Agency, UserRoles::Influencer])) {
            $currentParticipants[] = $targetUser->id;
        }

        return $currentParticipants;
    }

    public  static function validateChatPermission(User $currentUser, User $targetUser): array
    {
        if ($currentUser->id === $targetUser->id) {
            return ['allowed' => false, 'message' => 'You cannot chat with yourself.'];
        }

        if ($targetUser->role === UserRoles::Admin) {
            return ['allowed' => false, 'message' => 'Cannot chat with administrators.'];
        }

        return match ($currentUser->role) {
            UserRoles::Company =>  self::validateCompanyPermission($targetUser),
            UserRoles::Agency =>  self::validateAgencyPermission($currentUser, $targetUser),
            UserRoles::Influencer => self::validateInfluencerPermission($targetUser),
            default => ['allowed' => false],
        };
    }

    protected static  function validateCompanyPermission(User $targetUser): array
    {
        if (in_array($targetUser->role, [UserRoles::Agency, UserRoles::Influencer])) {
            return ['allowed' => true];
        }
        return ['allowed' => false, 'message' => 'Companies can only chat with agencies and influencers.'];
    }

    protected static function validateAgencyPermission(User $agency, User $targetUser): array
    {
        if ($targetUser->role === UserRoles::Influencer) {
            $influencerAgencyId = $targetUser->influencer_info['agency_id'] ?? null;
            if ($influencerAgencyId !== $agency->id) {
                return ['allowed' => false, 'message' => 'You can only chat with your own influencers.'];
            }
        }

        if (in_array($targetUser->role, [UserRoles::Company, UserRoles::Agency, UserRoles::Influencer])) {
            return ['allowed' => true];
        }

        return ['allowed' => false];
    }

    protected static function validateInfluencerPermission(User $targetUser): array
    {
        if (in_array($targetUser->role, [UserRoles::Company, UserRoles::Agency])) {
            return ['allowed' => true];
        }
        return ['allowed' => false, 'message' => 'Influencers can only chat with companies and agencies.'];
    }
}
