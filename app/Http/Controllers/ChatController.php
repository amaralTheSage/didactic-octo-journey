<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {

        $allChats = Auth::user()
            ->chats()
            ->with(['users', 'messages'])
            ->latest()
            ->get();

        return Inertia::render('chat/Chats', ['allChats' => $allChats]);
    }


    public function store(Request $request)
    {
        dd($request);

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $currentUser = Auth::user();
        $selectedUserIds = $request->users;

        // Validate and process participants based on rules
        $finalUserIds = $this->processChatParticipants($currentUser, $selectedUserIds);

        if (is_array($finalUserIds) && isset($finalUserIds['error'])) {
            return response()->json(['error' => $finalUserIds['error']], 422);
        }

        $chat = Chat::create([
            'name' => $request->name,
        ]);

        $chat->users()->attach($finalUserIds);

        $chat->load('users');

        return route('chats.show', ['chat' => $chat]);
    }

    /**
     * Process chat participants based on business rules
     */
    protected function processChatParticipants(User $currentUser, array $selectedUserIds): array
    {
        $finalUserIds = [$currentUser->id];
        $selectedUsers = User::whereIn('id', $selectedUserIds)->get();

        foreach ($selectedUsers as $user) {
            // Rule validation based on current user's role
            switch ($currentUser->role) {
                case UserRoles::Company->value:
                    $result = $this->handleCompanyChat($currentUser, $user, $finalUserIds);
                    if (isset($result['error'])) return $result;
                    $finalUserIds = $result;
                    break;

                case UserRoles::Agency->value:
                    $result = $this->handleAgencyChat($currentUser, $user, $finalUserIds);
                    if (isset($result['error'])) return $result;
                    $finalUserIds = $result;
                    break;

                case UserRoles::Influencer->value:
                    $result = $this->handleInfluencerChat($currentUser, $user, $finalUserIds);
                    if (isset($result['error'])) return $result;
                    $finalUserIds = $result;
                    break;
            }
        }

        return array_unique($finalUserIds);
    }

    /**
     * Handle chat creation by Company
     */
    protected function handleCompanyChat(User $company, User $targetUser, array $currentParticipants): array
    {
        // Companies can chat with Agencies
        if ($targetUser->role === UserRoles::Agency->value) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        // Companies can chat with Influencers, but must include their agency
        if ($targetUser->role === UserRoles::Influencer->value) {
            $currentParticipants[] = $targetUser->id;

            // Get the influencer's agency
            $agencyId = $targetUser->influencer_info['agency_id'] ?? null;

            if (!$agencyId) {
                return ['error' => 'This influencer does not belong to an agency.'];
            }

            // Add the agency to the chat
            $currentParticipants[] = $agencyId;
            return $currentParticipants;
        }

        // Companies cannot chat with other companies
        if ($targetUser->role === UserRoles::Company->value) {
            return ['error' => 'Companies cannot start chats with other companies.'];
        }

        return $currentParticipants;
    }

    /**
     * Handle chat creation by Agency
     */
    protected function handleAgencyChat(User $agency, User $targetUser, array $currentParticipants): array
    {
        // Agencies can chat with Companies
        if ($targetUser->role === UserRoles::Company->value) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        // Agencies can only add their own influencers
        if ($targetUser->role === UserRoles::Influencer->value) {
            $influencerAgencyId = $targetUser->influencer_info['agency_id'] ?? null;

            if ($influencerAgencyId !== $agency->id) {
                return ['error' => 'You can only add influencers that belong to your agency.'];
            }

            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        // Agencies can chat with other agencies
        if ($targetUser->role === UserRoles::Agency->value) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        return $currentParticipants;
    }

    /**
     * Handle chat creation by Influencer
     */
    protected function handleInfluencerChat(User $influencer, User $targetUser, array $currentParticipants): array
    {
        // Influencers can respond to existing chats but may have restrictions on creating new ones
        // Add your specific rules here based on your business requirements

        // For now, allow influencers to chat with companies and agencies
        if (in_array($targetUser->role, [
            UserRoles::Company->value,
            UserRoles::Agency->value
        ])) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        // Influencers chatting with other influencers
        if ($targetUser->role === UserRoles::Influencer->value) {
            $currentParticipants[] = $targetUser->id;
            return $currentParticipants;
        }

        return $currentParticipants;
    }

    public function show(Chat $chat)
    {
        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $chat = $chat->load(['users', 'messages.sender']);

        $allChats = Auth::user()
            ->chats()
            ->with(['users', 'messages'])
            ->latest()
            ->get();

        return Inertia::render('chat/Chats', ['chat' => $chat, 'allChats' => $allChats]);
    }
}
