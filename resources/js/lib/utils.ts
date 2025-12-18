import { User } from '@/types';
import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function isSameUrl(
    url1: NonNullable<InertiaLinkProps['href']>,
    url2: NonNullable<InertiaLinkProps['href']>,
) {
    return resolveUrl(url1) === resolveUrl(url2);
}

export function resolveUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function getDefaultChatName(users: User[]): string {
    return users
        .map((user) => user.name.trim().split(/\s+/).slice(0, 2).join(' '))
        .join(', ');
}

export function getTranslatedRole(role: string): string {
    let translated;

    switch (role) {
        case 'admin':
            translated = 'Administrador';
            break;

        case 'influencer':
            translated = 'Influenciador';
            break;

        case 'company':
            translated = 'Empresa';
            break;

        case 'agency':
            translated = 'Agência';
            break;

        default:
            translated = 'erro';
            break;
    }

    return translated;
}
