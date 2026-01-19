<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Chat;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function index()
    {
        $allChats = Auth::user()
            ->chats()
            ->with(['users', 'messages'])
            ->latest()
            ->get();

        return Inertia::render('chat/Chats', ['allChats' => $allChats]);
    }

    public function create(Request $request, ChatService $chatService)
    {
        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $userIds = array_merge($request->users, [Auth::id()]);
        sort($userIds);
        $userCount = count($userIds);

        $existingChat = Chat::select('chats.*')
            ->selectRaw('COUNT(chat_user.user_id) as users_count')
            ->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
            ->whereIn('chat_user.user_id', $userIds)
            ->groupBy('chats.id')
            ->havingRaw('COUNT(chat_user.user_id) = ?', [$userCount])
            ->first();

        if ($existingChat) {
            return redirect()->route('chats.show', ['chat' => $existingChat]);
        }

        $chat = $chatService->createChat($request->users);

        if (is_array($chat) && isset($chat['error'])) {
            return redirect()->route('chats.index')
                ->with('error', $chat['error']);
        }

        return redirect()->route('chats.show', ['chat' => $chat]);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'users' => 'required|array|min:1',
    //         'users.*' => 'exists:users,id',
    //     ]);

    //     $chat = $this->chatService->createChat([Auth::user(), $request->users]);

    //     if (is_array($chat) && isset($chat['error'])) {
    //         return response()->json(['error' => $chat['error']], 422);
    //     }

    //     return redirect()->route('chats.show', ['chat' => $chat]);
    // }

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

    public function searchUsersToAdd(Request $request, Chat $chat)
    {
        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $search = $request->input('search', '');
        $currentUser = Auth::user();

        // Get IDs of users already in the chat
        $existingUserIds = $chat->users->pluck('id')->toArray();

        // Query based on role with filtering
        $query = User::query()
            ->whereNotIn('id', $existingUserIds) // Exclude existing members
            ->where('id', '!=', $currentUser->id) // Exclude current user
            ->where('role', '!=', UserRole::ADMIN->value); // Exclude admins

        // Apply role-based filtering
        switch ($currentUser->role) {
            case UserRole::COMPANY:
                $query->whereIn('role', [
                    UserRole::AGENCY->value,
                    UserRole::INFLUENCER->value,
                ]);
                break;

            case UserRole::AGENCY:
                $query->where(function ($q) use ($currentUser) {
                    $q->whereIn('role', [
                        UserRole::COMPANY->value,
                        UserRole::AGENCY->value,
                    ])
                        ->orWhere(function ($subQ) use ($currentUser) {
                            $subQ->where('role', UserRole::INFLUENCER->value)
                                ->whereHas('influencer_info', function ($infoQ) use ($currentUser) {
                                    $infoQ->where('agency_id', $currentUser->id)
                                        ->where('association_status', 'approved');
                                });
                        });
                });
                break;

            case UserRole::INFLUENCER:
                $query->whereIn('role', [
                    UserRole::COMPANY->value,
                    UserRole::AGENCY->value,
                ]);
                break;
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->select('id', 'name', 'email', 'avatar', 'role')
            ->limit(15)
            ->get();

        return response()->json($users);
    }

    public function addUsers(Request $request, Chat $chat)
    {
        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $result = ChatService::addUsersToChat($chat, $request->users);

        if (is_array($result) && isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Users added to chat successfully');
    }

    public function update(Request $request, Chat $chat)
    {

        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:10240',

        ]);

        if ($request->hasFile('image')) {

            if ($chat->image) {
                Storage::disk('public')->delete($chat->image);
            }

            $path = $request->file('image')->store('chat-images', 'public');
            $validated['image'] = $path;
        }

        $chat->update($validated);

        return back()->with('success', 'Chat updated successfully');
    }

    public function deleteImage(Chat $chat)
    {
        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        if ($chat->image) {
            Storage::disk('public')->delete($chat->image);
            $chat->update(['image' => null]);
        }

        return back()->with('success', 'Chat image deleted successfully');
    }
}
