<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $chat = Chat::create([
                'name' => "Chat {$i}",
                'description' => "Description for chat {$i}",
            ]);

            $userIds = collect(range(2, 50))
                ->random(rand(2, 5))
                ->push(1) // always includes user 1
                ->unique();

            $chat->users()->attach($userIds);
        }

        // Messages
        Chat::with('users')->get()->each(function (Chat $chat) {
            for ($i = 1; $i <= rand(5, 20); $i++) {
                Message::create([
                    'chat_id' => $chat->id,
                    'user_id' => $chat->users->random()->id,
                    'content' => "Message {$i} in chat {$chat->id}",
                ]);
            }
        });
    }
}
