import { useAppearance } from '@/hooks/use-appearance';
import { ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ChatSidebar } from './chat-sidebar';
import { GroupChat } from './group-chat';
import { ChatType } from './types';

export default function Chats({
    allChats,
    chat,
}: {
    allChats: ChatType[];
    chat?: ChatType;
}) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [activeConversation, setActiveConversation] = useState(chat?.id);
    const [infoPanelOpen, setInfoPanelOpen] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const { appearance, updateAppearance } = useAppearance();

    useEffect(() => {
        updateAppearance(localStorage.theme);
    }, [localStorage.theme]);

    return (
        <main className={`flex min-h-screen`}>
            <ChatSidebar
                isOpen={sidebarOpen}
                allChats={allChats}
                onToggle={() => setSidebarOpen(!sidebarOpen)}
                activeConversation={activeConversation}
                onSelectConversation={setActiveConversation}
            />

            {chat ? (
                <GroupChat
                    allChats={allChats}
                    chat={chat}
                    onToggleSidebar={() => setSidebarOpen(!sidebarOpen)}
                    sidebarOpen={sidebarOpen}
                />
            ) : (
                <div className="bg-background px-5 py-5">
                    {!sidebarOpen && (
                        <button
                            className="mr-2 aspect-square rounded-full p-1 transition-colors hover:bg-secondary/30"
                            onClick={(e) => {
                                e.stopPropagation();
                                setSidebarOpen(!sidebarOpen);
                            }}
                        >
                            <ChevronRight className="ml-0.5 h-6 w-6 text-muted-foreground" />
                        </button>
                    )}
                </div>
            )}
        </main>
    );
}
