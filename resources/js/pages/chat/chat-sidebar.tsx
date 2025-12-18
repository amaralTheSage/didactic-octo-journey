import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { cn, getDefaultChatName } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import { GroupDefaultImage } from './group-default-image';
import type { ChatType } from './types';

interface ChatSidebarProps {
    isOpen: boolean;
    onToggle: () => void;
    allChats: ChatType[];
    activeConversation: string;
}

export function ChatSidebar({
    isOpen,
    onToggle,
    allChats,
    activeConversation,
}: ChatSidebarProps) {
    const getLastMessage = (chat: ChatType) => chat.messages.at(-1);

    return (
        <aside
            className={cn(
                'flex h-screen flex-col border-r border-sidebar-border bg-sidebar transition-all duration-300',
                isOpen ? 'w-96' : 'w-0 overflow-hidden',
            )}
        >
            {/* Header */}
            <div className="flex items-center justify-between border-b p-4">
                <a href={'/dashboard'}>
                    <h1 className="text-xl font-semibold">HubInflu</h1>
                </a>
                <div className="flex items-center gap-2">
                    <button
                        onClick={onToggle}
                        className="aspect-square rounded-full p-1 hover:bg-sidebar-accent"
                    >
                        <ChevronLeft className="mr-0.5 h-6 w-6 text-muted-foreground" />
                    </button>
                </div>
            </div>

            {/* Chat list */}
            <div className="flex-1 overflow-y-auto">
                {allChats.map((chat) => {
                    const lastMessage = getLastMessage(chat);
                    const isActive = activeConversation === chat.id;
                    const isGroup = chat.users.length > 2;

                    return (
                        <button
                            key={chat.id}
                            onClick={() => router.visit(`/chats/${chat.id}`)}
                            className={cn(
                                'flex w-full items-center gap-3 px-4 py-3 text-left transition-colors',
                                isActive
                                    ? 'bg-sidebar-primary/10'
                                    : 'hover:bg-sidebar-accent',
                            )}
                        >
                            <div className="relative">
                                {chat.image ? (
                                    <Avatar className="h-12 w-12">
                                        <AvatarImage
                                            src={
                                                chat.image || '/placeholder.svg'
                                            }
                                            alt={chat.name || ''}
                                        />
                                        <AvatarFallback>
                                            {chat.name || ''}
                                        </AvatarFallback>
                                    </Avatar>
                                ) : (
                                    <GroupDefaultImage users={chat.users} />
                                )}

                                {isGroup && (
                                    <span className="absolute -right-0.5 -bottom-0.5 flex h-5 w-5 items-center justify-center rounded-full border-2 bg-sidebar-primary text-[10px] font-medium text-sidebar-primary-foreground">
                                        {chat.users.length}
                                    </span>
                                )}
                            </div>

                            <div className="min-w-0 flex-1">
                                <div className="flex items-center justify-between">
                                    <span className="truncate font-medium">
                                        {chat.name ||
                                            getDefaultChatName(chat.users)}
                                    </span>
                                    {lastMessage && (
                                        <span className="text-xs text-muted-foreground">
                                            {new Date(
                                                lastMessage.created_at,
                                            ).toLocaleDateString()}
                                        </span>
                                    )}
                                </div>

                                <p className="truncate text-sm text-muted-foreground">
                                    {lastMessage?.content ?? 'No messages yet'}
                                </p>
                            </div>
                        </button>
                    );
                })}
            </div>
        </aside>
    );
}
