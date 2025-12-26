import { useState } from 'react';
import { ChatHeader } from './chat-header';
import { ChatInfoPanel } from './chat-info-panel';
import { ChatInput } from './chat-input';
import { ChatMessages } from './chat-messages';
import type { ChatType, Message } from './types';
export function GroupChat({
    chat,
    sidebarOpen,
    onToggleSidebar,
}: {
    chat: ChatType;
    sidebarOpen: boolean;
    onToggleSidebar: () => void;
}) {
    const [infoPanelOpen, setInfoPanelOpen] = useState(false);
    const [messages, setMessages] = useState<Message[]>(chat.messages ?? []);

    return (
        <div className="flex h-screen w-full">
            <div className="flex min-w-0 flex-1 flex-col">
                <ChatHeader
                    chat={chat}
                    onToggleSidebar={onToggleSidebar}
                    sidebarOpen={sidebarOpen}
                    onHeaderClick={() => setInfoPanelOpen(true)}
                />

                <ChatMessages
                    messages={messages}
                    setMessages={setMessages}
                    users={chat.users ?? []}
                />

                <ChatInput chatId={chat.id} setMessages={setMessages} />
            </div>

            <ChatInfoPanel
                chat={chat}
                isOpen={infoPanelOpen}
                onClose={() => setInfoPanelOpen(false)}
            />
        </div>
    );
}
