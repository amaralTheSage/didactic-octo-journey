import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getDefaultChatName } from '@/lib/utils';
import { ChevronRight } from 'lucide-react';
import type { ChatType } from './types';

interface ChatHeaderProps {
    chat: ChatType;
    onToggleSidebar: () => void;
    sidebarOpen: boolean;
    onHeaderClick: () => void;
}

export function ChatHeader({
    chat,
    onToggleSidebar,
    sidebarOpen,
    onHeaderClick,
}: ChatHeaderProps) {
    return (
        <header
            className="m-2 flex cursor-pointer items-center justify-between rounded-sm border-b border-border bg-card px-3 py-2 hover:bg-secondary/20"
            onClick={onHeaderClick}
        >
            <div className="flex items-center gap-3">
                {!sidebarOpen && (
                    <button
                        className="mr-2 aspect-square rounded-full p-1 transition-colors hover:bg-secondary/30"
                        onClick={(e) => {
                            e.stopPropagation();
                            onToggleSidebar();
                        }}
                    >
                        <ChevronRight className="ml-0.5 h-6 w-6 text-muted-foreground" />
                    </button>
                )}
                <button className="-mx-2 -my-1 flex items-center gap-3 rounded-lg px-2 py-1 transition-colors">
                    {/* <Avatar className="h-10 w-10">
                        <AvatarImage
                            src={chat.avatar || '/placeholder.svg'}
                        />
                        <AvatarFallback>{chat.name}</AvatarFallback>
                    </Avatar> */}
                    <div className="text-left">
                        <h2 className="font-semibold text-foreground">
                            {chat.name || getDefaultChatName(chat.users)}
                        </h2>
                        <p className="text-xs text-muted-foreground">
                            {`${chat.users.length} members`}
                        </p>
                    </div>
                </button>
            </div>

            <div className="flex items-center gap-1">
                <div className="mr-3 flex -space-x-2">
                    {chat.users.slice(0, 4).map((user) => (
                        <Avatar
                            key={user.id}
                            className="h-8 w-8 border-2 border-card"
                        >
                            <AvatarImage
                                src={user.avatar_url || '/placeholder.svg'}
                                alt={user.name}
                            />
                            <AvatarFallback>{user.name}</AvatarFallback>
                        </Avatar>
                    ))}
                </div>
            </div>
        </header>
    );
}
