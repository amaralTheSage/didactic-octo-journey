<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Chat $chat)
    {
        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $request->validate([
            'content' => 'required|string',
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return back();
    }
}
