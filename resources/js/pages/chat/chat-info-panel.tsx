import { update } from '@/actions/App/Http/Controllers/ChatController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { getDefaultChatName, getTranslatedRole } from '@/lib/utils';
import { router, useForm, usePage } from '@inertiajs/react';
import { Calendar, Check, Info, Pencil, Upload, Users, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { GroupDefaultImage } from './group-default-image';
import type { ChatType } from './types';

interface ChatInfoPanelProps {
    chat: ChatType;
    isOpen: boolean;
    onClose: () => void;
}

export function ChatInfoPanel({ chat, isOpen, onClose }: ChatInfoPanelProps) {
    const [editingName, setEditingName] = useState(false);
    const [editingDescription, setEditingDescription] = useState(false);

    const [isUploading, setIsUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { auth } = usePage().props as any;

    const form = useForm({
        image: chat.image || '',
        name: chat.name || '',
        description: chat.description || '',
    });

    useEffect(() => {
        console.log(form.data.image);
    }, [form.data]);

    const handleSaveNameAndDescription = () => {
        form.submit(update({ chat: chat.id }));
    };

    const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];

        form.setData('image', file);

        if (!file) return;

        setIsUploading(true);
        form.patch(update({ chat: chat.id }), {
            preserveScroll: true,
            onFinish: () => setIsUploading(false),
            forceFormData: true,
        });
    };

    const handleDeleteImage = () => {
        if (confirm('Are you sure you want to delete the chat image?')) {
            router.delete(chatsDeleteImage.url({ chat: chat.id }), {
                preserveScroll: true,
            });
        }
    };
    return (
        <>
            {/* Backdrop */}
            <div
                className={`fixed inset-0 z-40 bg-black/50 transition-opacity duration-300 ${
                    isOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
                }`}
                onClick={onClose}
            />

            {/* Panel */}
            <div
                className={`fixed top-0 right-0 z-50 h-full w-full max-w-md transform border-l border-border bg-card transition-transform duration-300 ease-out ${
                    isOpen ? 'translate-x-0' : 'translate-x-full'
                }`}
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b border-border px-6 py-4">
                    <h2 className="text-lg font-semibold text-foreground">
                        Chat Info
                    </h2>
                    <button
                        onClick={onClose}
                        className="rounded-lg p-2 transition-colors hover:bg-secondary"
                    >
                        <X className="h-5 w-5 text-muted-foreground" />
                    </button>
                </div>

                <div className="h-[calc(100%-65px)] overflow-y-auto p-6">
                    {/* Avatar and Name */}
                    <div className="mb-8 flex flex-col items-center">
                        <div className="group relative">
                            {chat.image ? (
                                <Avatar className="h-32 w-32">
                                    <AvatarImage
                                        src={`/storage/${chat.image}`}
                                        alt={chat.name || ''}
                                    />
                                    <AvatarFallback>
                                        {chat.name || ''}
                                    </AvatarFallback>
                                </Avatar>
                            ) : (
                                <GroupDefaultImage
                                    users={chat.users}
                                    size={120}
                                />
                            )}

                            {/* Image Upload Overlay */}
                            <div className="absolute inset-0 flex items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                <div className="flex gap-2">
                                    <button
                                        onClick={() =>
                                            fileInputRef.current?.click()
                                        }
                                        disabled={isUploading}
                                        className="rounded-lg bg-primary p-2 text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50"
                                        title="Upload image"
                                    >
                                        <Upload className="h-5 w-5" />
                                    </button>
                                    {chat.image && (
                                        <button
                                            onClick={handleDeleteImage}
                                            className="rounded-lg bg-destructive p-2 text-destructive-foreground transition-colors hover:bg-destructive/90"
                                            title="Delete image"
                                        >
                                            <X className="h-5 w-5" />
                                        </button>
                                    )}
                                </div>
                            </div>

                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/*"
                                className="hidden"
                                onChange={handleImageUpload}
                            />
                        </div>

                        {/* Editable Name */}
                        <div className="mt-4 flex w-full items-center justify-center gap-2">
                            {editingName ? (
                                <div className="flex items-center gap-2">
                                    <input
                                        type="text"
                                        defaultValue={
                                            form.data.name ||
                                            getDefaultChatName(chat.users)
                                        }
                                        onChange={(e) =>
                                            form.setData('name', e.target.value)
                                        }
                                        className="rounded-lg border border-border bg-secondary/10 px-3 py-1 text-center text-lg font-semibold text-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                                        autoFocus
                                        onKeyDown={(e) =>
                                            e.key === 'Enter' &&
                                            handleSaveNameAndDescription()
                                        }
                                    />
                                    <button
                                        onClick={handleSaveNameAndDescription}
                                        className="rounded-lg bg-primary p-3 text-primary-foreground transition-colors hover:bg-primary/90"
                                    >
                                        <Check className="h-4 w-4" />
                                    </button>
                                </div>
                            ) : (
                                <div className="group flex items-center gap-2">
                                    <h3 className="text-center text-xl font-semibold text-foreground">
                                        {chat.name ||
                                            getDefaultChatName(chat.users)}
                                    </h3>
                                    <button
                                        onClick={() => setEditingName(true)}
                                        className="rounded-lg p-1.5 opacity-0 transition-all group-hover:opacity-100 hover:bg-secondary"
                                    >
                                        <Pencil className="h-4 w-4 text-muted-foreground" />
                                    </button>
                                </div>
                            )}
                        </div>

                        <p className="mt-1 text-sm text-muted-foreground">
                            {`${chat.users.length} users`}
                        </p>
                    </div>

                    {/* Description Section */}
                    <div className="mb-6">
                        <div className="mb-3 flex items-center gap-2">
                            <Info className="h-4 w-4 text-muted-foreground" />
                            <span className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                                Description
                            </span>
                        </div>
                        {editingDescription ? (
                            <div className="flex flex-col gap-2">
                                <textarea
                                    defaultValue={form.data.description || ''}
                                    onChange={(e) =>
                                        form.setData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    className="resize-none rounded-lg border border-border bg-secondary/10 px-4 py-3 text-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                                    rows={3}
                                    autoFocus
                                />
                                <button
                                    onClick={handleSaveNameAndDescription}
                                    className="self-end rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Save
                                </button>
                            </div>
                        ) : (
                            <div
                                className="group cursor-pointer rounded-lg border border-border px-4 py-3 transition-colors hover:bg-secondary/10"
                                onClick={() => setEditingDescription(true)}
                            >
                                <p className="text-foreground">
                                    {chat.description || 'Add a description...'}
                                </p>
                                <p className="mt-2 text-xs text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100">
                                    Click to edit
                                </p>
                            </div>
                        )}
                    </div>

                    {/* Creation Date */}
                    <div className="mb-6">
                        <div className="mb-3 flex items-center gap-2">
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                            <span className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                                Created
                            </span>
                        </div>
                        <p className="px-4 text-foreground">
                            {new Date(chat.created_at).toLocaleDateString(
                                'pt-BR',
                                {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                },
                            )}
                        </p>
                    </div>

                    {/* Members Section */}
                    {chat && (
                        <div>
                            <div className="mb-3 flex items-center gap-2">
                                <Users className="h-4 w-4 text-muted-foreground" />
                                <span className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                                    Members ({chat.users.length})
                                </span>
                            </div>
                            <div className="space-y-1">
                                {chat.users.map((member) => (
                                    <div
                                        key={member.id}
                                        className={` ${
                                            member.id === auth.user.id &&
                                            'bg-primary/30'
                                        } flex items-start justify-between rounded-lg px-4 py-3 font-medium text-foreground transition-colors hover:bg-secondary/10`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="relative">
                                                <Avatar className="h-10 w-10">
                                                    <AvatarImage
                                                        src={
                                                            member.avatar_url ||
                                                            '/placeholder.svg'
                                                        }
                                                    />
                                                    <AvatarFallback>
                                                        {member.name}
                                                    </AvatarFallback>
                                                </Avatar>
                                            </div>
                                            <div className="line-clamp-2 min-w-0 flex-1 wrap-break-word">
                                                <span>{member.name}</span>
                                            </div>
                                        </div>

                                        <Badge>
                                            {getTranslatedRole(member.role)}
                                        </Badge>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
