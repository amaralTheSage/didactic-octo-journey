<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{

    public function store(Request $request, Chat $chat)
    {

        abort_unless(
            $chat->users->contains(Auth::id()),
            403
        );

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240'], // 10MB per file
        ]);

        // Must have at least text or files
        if (
            blank($validated['content'] ?? null) &&
            !$request->hasFile('files')
        ) {
            abort(422, 'Message must contain text or files.');
        }

        $attachments = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('chat-attachments', 'public');

                $attachments[] = [
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'type' => str_starts_with($file->getMimeType(), 'image/')
                        ? 'image'
                        : 'file',
                ];
            }
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'] ?? null,
            'attachments' => empty($attachments) ? null : $attachments,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return back();
    }
}
