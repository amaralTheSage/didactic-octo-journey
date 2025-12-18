import { useRef, useState } from 'react';
import { ChatHeader } from './chat-header';
import { ChatInfoPanel } from './chat-info-panel';
import { ChatInput } from './chat-input';
import { ChatMessages } from './chat-messages';
import type { Attachment, ChatType } from './types';

export function GroupChat({
    chat,
    sidebarOpen,
    onToggleSidebar,
}: {
    sidebarOpen: boolean;
    onToggleSidebar: () => void;
    allChats?: ChatType[];
}) {
    const [infoPanelOpen, setInfoPanelOpen] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    console.log(chat);
    const messages = chat.messages ?? [];
    const users = chat.users ?? [];

    const handleSendMessage = (content: string, attachments: Attachment[]) => {
        console.log(content);
        // const newMessage: Message = {
        //     id: Date.now().toString(),
        //     userId: '1', // current user
        //     content,
        //     timestamp: new Date(),
        //     attachments: attachments.length ? attachments : undefined,
        // };

        // // optimistic update (adjust if using API)
        // chat.messages.push(newMessage);

        // setTimeout(() => {
        //     messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
        // }, 100);
    };

    const handleUpdateConversation = (updates: Partial<ChatType>) => {
        Object.assign(chat, updates);
    };

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
                    users={users}
                    messagesEndRef={messagesEndRef}
                />

                <ChatInput chatId={chat.id} onSendMessage={handleSendMessage} />
            </div>

            <ChatInfoPanel
                chat={chat}
                isOpen={infoPanelOpen}
                onClose={() => setInfoPanelOpen(false)}
                onUpdateConversation={handleUpdateConversation}
            />
        </div>
    );
}
