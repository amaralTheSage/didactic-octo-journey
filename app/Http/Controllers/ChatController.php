<?php

namespace App\Http\Controllers;

use App\Models\Chat;
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

    public function store(Request $request)
    {
        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $chat = $this->chatService->createChat(Auth::user(), $request->users);

        if (is_array($chat) && isset($chat['error'])) {
            return response()->json(['error' => $chat['error']], 422);
        }

        return redirect()->route('chats.show', ['chat' => $chat]);
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

    public function update(Request $request, Chat $chat)
    {
        dd($request);

        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
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