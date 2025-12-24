import type React from 'react';

import { store } from '@/actions/App/Http/Controllers/MessageController';
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/react';
import { FileText, Image, Paperclip, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { Attachment } from './types';

interface ChatInputProps {
    chatId: number;
}

export function ChatInput({ chatId }: ChatInputProps) {
    const [showAttachMenu, setShowAttachMenu] = useState(false);
    const [isSending, setIsSending] = useState(false);
    const [attachments, setAttachments] = useState<Attachment[]>([]);

    const fileInputRef = useRef<HTMLInputElement>(null);
    const imageInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, submit } = useForm({
        content: '',
        files: [] as File[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.content.trim() && data.files.length === 0) return;
        if (isSending) return;

        setIsSending(true);

        submit(store(chatId), {
            forceFormData: true,
            onSuccess: () => {
                setData({ content: '', files: [] });
                setAttachments([]);
            },
            onFinish: () => {
                setIsSending(false);
            },
        });
    };

    const handleFileSelect = (
        e: React.ChangeEvent<HTMLInputElement>,
        type: 'image' | 'file',
    ) => {
        const files = Array.from(e.target.files ?? []);
        if (!files.length) return;

        setData('files', [...data.files, ...files]);

        setAttachments((prev) => [
            ...prev,
            ...files.map((file) => ({
                id: crypto.randomUUID(),
                type,
                name: file.name,
                url: type === 'image' ? URL.createObjectURL(file) : '#',
                size: formatFileSize(file.size),
            })),
        ]);

        setShowAttachMenu(false);
        e.target.value = '';
    };

    const removeAttachment = (id: string) => {
        const index = attachments.findIndex((a) => a.id === id);
        if (index === -1) return;

        setAttachments((prev) => prev.filter((a) => a.id !== id));
        setData(
            'files',
            data.files.filter((_, i) => i !== index),
        );
    };

    const formatFileSize = (bytes: number) => {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    };

    useEffect(() => {
        console.log(data);
    }, [data]);

    return (
        <form
            onSubmit={handleSubmit}
            className="my-3 mr-3 rounded-lg border border-border bg-card p-4"
            onKeyDown={(e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    handleSubmit(e);
                }
            }}
        >
            {attachments.length > 0 && (
                <div className="mb-3 flex flex-wrap gap-2 rounded-sm p-2">
                    {attachments.map((attachment) => (
                        <div key={attachment.id} className="group relative">
                            {attachment.type === 'image' ? (
                                <div className="relative aspect-auto h-20 overflow-hidden rounded-lg bg-white">
                                    <img
                                        src={attachment.url}
                                        alt={attachment.name}
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 rounded-lg bg-background px-3 py-2">
                                    <FileText className="h-4 w-4 text-primary" />
                                    <span className="max-w-24 truncate text-xs">
                                        {attachment.name}
                                    </span>
                                </div>
                            )}
                            <button
                                type="button"
                                onClick={() => removeAttachment(attachment.id)}
                                className="absolute -top-1.5 -right-1.5 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <X className="h-3 w-3" />
                            </button>
                        </div>
                    ))}
                </div>
            )}

            <div className="flex items-end gap-2">
                <div className="relative">
                    <button
                        type="button"
                        onClick={() => setShowAttachMenu(!showAttachMenu)}
                        className="rounded-sm p-2.5 transition-colors hover:bg-secondary/10"
                    >
                        <Paperclip className="h-5 w-5 text-muted-foreground" />
                    </button>

                    {showAttachMenu && (
                        <div className="absolute bottom-full -left-4 mb-7 w-40 rounded-sm border border-border bg-card shadow-xl">
                            <button
                                type="button"
                                onClick={() => imageInputRef.current?.click()}
                                className="flex w-full items-center gap-3 rounded-t-sm px-4 py-3 text-sm transition-colors hover:bg-secondary/10"
                            >
                                <Image className="h-4 w-4 text-secondary" />
                                <span>Image</span>
                            </button>
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="flex w-full items-center gap-3 rounded-b-sm px-4 py-3 text-sm transition-colors hover:bg-secondary/10"
                            >
                                <FileText className="h-4 w-4 text-secondary" />
                                <span>File</span>
                            </button>
                        </div>
                    )}

                    <input
                        ref={imageInputRef}
                        type="file"
                        accept="image/*"
                        multiple
                        className="hidden"
                        onChange={(e) => handleFileSelect(e, 'image')}
                    />
                    <input
                        ref={fileInputRef}
                        type="file"
                        multiple
                        className="hidden"
                        onChange={(e) => handleFileSelect(e, 'file')}
                    />
                </div>

                <div className="flex flex-1 items-end gap-2 rounded-sm border border-border px-4 py-[9px]">
                    <textarea
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                        placeholder="Type a message..."
                        rows={1}
                        disabled={isSending}
                        className="max-h-32 flex-1 resize-none bg-transparent text-sm placeholder:text-muted-foreground focus:outline-none disabled:opacity-50"
                    />
                </div>

                <button
                    type="submit"
                    disabled={
                        (!data.content.trim() && data.files.length === 0) ||
                        isSending
                    }
                    className={cn(
                        'cursor-pointer rounded-sm p-2 transition-all',
                        (data.content.trim() || data.files.length > 0) &&
                            !isSending
                            ? 'text-primary hover:bg-primary/10'
                            : 'text-muted-foreground opacity-50',
                    )}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        strokeWidth={1.5}
                        stroke="currentColor"
                        className="size-6"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"
                        />
                    </svg>
                </button>
            </div>
        </form>
    );
}
