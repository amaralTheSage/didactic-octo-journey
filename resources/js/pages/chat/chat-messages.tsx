import type React from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { cn, getTranslatedRole } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import { Check, Download, FileText, Loader2, Maximize2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { Attachment, Message, User } from './types';

import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useEchoPublic } from '@laravel/echo-react';
import { ReactFormState } from 'react-dom/client';

interface ChatMessagesProps {
    messages: Message[] & { is_sending: boolean };
    users: User[];
    setMessages: React.Dispatch<ReactFormState>;
}

interface MessageWithStatus extends Message {
    is_sending?: boolean;
}

export function ChatMessages({
    messages,
    setMessages,
    users,
}: ChatMessagesProps) {
    const { auth } = usePage().props as any;
    const currentUserId = String(auth.user.id);
    const messagesEndRef = useRef<HTMLDivElement | null>(null);
    const [lightboxImage, setLightboxImage] = useState<string | null>(null);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({
            behavior: 'smooth',
            block: 'end',
        });
    }, [messages]);

    useEchoPublic<{ message: Message }>(
        'messages',
        'MessageSent',
        ({ message }) => {
            console.log('PAYLOAD: ', message);

            setMessages((prev) => {
                const tempIndex = prev.findIndex(
                    (m) =>
                        m.is_sending &&
                        m.user_id === message.user_id &&
                        m.content === message.content,
                );

                if (tempIndex !== -1) {
                    const copy = [...prev];
                    copy[tempIndex] = { ...message, is_sending: false };
                    return copy;
                }

                if (prev.some((m) => m.id === message.id)) {
                    return prev;
                }

                return [...prev, { ...message, is_sending: false }];
            });
        },
    );
    const getUser = (user_id: string | number) =>
        users.find((u) => String(u.id) === String(user_id));

    const renderAttachment = (attachment: Attachment) => {
        if (attachment.type === 'image') {
            return (
                <button
                    key={attachment.id}
                    onClick={() => setLightboxImage(attachment.url)}
                    className="group relative max-w-xs overflow-hidden rounded-xl transition-all hover:ring-2 hover:ring-primary/50"
                >
                    <img
                        src={attachment.url || '/placeholder.svg'}
                        alt={attachment.name}
                        className="h-auto max-h-64 w-full object-cover"
                    />
                    <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/30">
                        <Maximize2 className="h-6 w-6 text-white opacity-0 transition-opacity group-hover:opacity-100" />
                    </div>
                    <div className="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2">
                        <p className="truncate text-xs text-white/90">
                            {attachment.name}
                        </p>
                        <p className="text-xs text-white/70">
                            {attachment.size}
                        </p>
                    </div>
                </button>
            );
        }

        return (
            <div
                key={attachment.id}
                className="flex max-w-xs items-center gap-3 rounded-xl bg-secondary p-3 transition-colors hover:bg-secondary/80"
            >
                <div className="rounded-lg bg-primary/10 p-2">
                    <FileText className="h-5 w-5 text-primary" />
                </div>
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">
                        {attachment.name}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        {attachment.size}
                    </p>
                </div>
                <a
                    href={attachment.url}
                    download={attachment.name}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="rounded-lg p-2 transition-colors hover:bg-background/50"
                >
                    <Download className="h-4 w-4 text-muted-foreground" />
                </a>
            </div>
        );
    };

    return (
        <>
            <div className="flex-1 space-y-4 overflow-y-auto p-4">
                {messages.map((message, index) => {
                    const user = getUser(message.user_id);
                    const isCurrentUser =
                        String(message.user_id) === currentUserId;

                    const showAvatar =
                        index === 0 ||
                        String(messages[index - 1].user_id) !==
                            String(message.user_id);

                    return (
                        <div
                            key={message.id}
                            className={cn(
                                'flex gap-3',
                                isCurrentUser ? 'flex-row-reverse' : 'flex-row',
                            )}
                        >
                            {showAvatar ? (
                                <Avatar className="h-10 w-10 shrink-0">
                                    <AvatarImage
                                        src={
                                            user?.avatar_url ||
                                            '/placeholder.svg'
                                        }
                                        alt={user?.name}
                                    />
                                    <AvatarFallback>
                                        {user?.name ?? 'Conta Excluída'}
                                    </AvatarFallback>
                                </Avatar>
                            ) : (
                                <div className="w-10 shrink-0" />
                            )}

                            <div
                                className={cn(
                                    'flex max-w-lg flex-col',
                                    isCurrentUser ? 'items-end' : 'items-start',
                                )}
                            >
                                {showAvatar && (
                                    <div
                                        className={cn(
                                            'relative mb-1 flex items-center gap-2',
                                            isCurrentUser
                                                ? 'flex-row-reverse'
                                                : 'flex-row',
                                        )}
                                    >
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <p className="text-sm font-semibold">
                                                    {user?.name ??
                                                        'Conta excluída'}
                                                </p>
                                            </TooltipTrigger>
                                            <TooltipContent
                                                side="right"
                                                align="end"
                                                className={`${isCurrentUser && 'bg-secondary text-white'}`}
                                                arrowClasses={`${isCurrentUser && 'bg-secondary fill-secondary'}`}
                                            >
                                                {getTranslatedRole(user?.role)}
                                            </TooltipContent>
                                        </Tooltip>
                                    </div>
                                )}

                                {message.content && (
                                    <div
                                        className={cn(
                                            'rounded-2xl px-4 py-2.5 text-sm leading-relaxed font-semibold',
                                            isCurrentUser
                                                ? 'rounded-br-md bg-primary text-primary-foreground'
                                                : 'rounded-bl-md bg-secondary text-secondary-foreground',
                                        )}
                                    >
                                        <div className="relative flex items-center gap-2">
                                            <span>{message.content}</span>
                                            {isCurrentUser && (
                                                <span className="relative top-2 left-2 inline-flex shrink-0">
                                                    {message.is_sending ? (
                                                        <Loader2 className="h-3 w-3 animate-spin opacity-70" />
                                                    ) : (
                                                        <Check className="h-3 w-3 opacity-70" />
                                                    )}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {message.attachments &&
                                    message.attachments.length > 0 && (
                                        <div
                                            className={cn(
                                                'mt-2 flex flex-wrap gap-2',
                                                isCurrentUser
                                                    ? 'justify-end'
                                                    : 'justify-start',
                                            )}
                                        >
                                            {message.attachments.map(
                                                renderAttachment,
                                            )}
                                        </div>
                                    )}
                            </div>
                        </div>
                    );
                })}
                <div ref={messagesEndRef} />
            </div>

            {lightboxImage && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                    onClick={() => setLightboxImage(null)}
                >
                    <button
                        className="absolute top-4 right-4 rounded-full bg-white/10 p-2 transition-colors hover:bg-white/20"
                        onClick={() => setLightboxImage(null)}
                    >
                        <X className="h-6 w-6 text-white" />
                    </button>
                    <img
                        src={lightboxImage}
                        alt="Preview"
                        className="max-h-full max-w-full rounded-lg object-contain"
                    />
                </div>
            )}
        </>
    );
}

// Export setter for parent component
export { type MessageWithStatus };
