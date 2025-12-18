import { User } from '@/types';

type GroupAvatarProps = {
    users: User[];
    size?: number;
};

type AvatarCellProps = {
    user: User;
    count: number;
    index: number;
};

export function GroupDefaultImage({ users, size = 48 }: GroupAvatarProps) {
    const visibleUsers = users.slice(0, 4);
    const count = visibleUsers.length;

    const gridClass = (() => {
        switch (count) {
            case 2:
                return 'grid-cols-2';
            case 3:
            case 4:
                return 'grid-cols-2';
            default:
                return 'grid-cols-1';
        }
    })();

    return (
        <div
            className={`grid ${gridClass} overflow-hidden rounded-full`}
            style={{
                width: size,
                height: size,
            }}
        >
            {count === 3 ? (
                <>
                    {visibleUsers.slice(0, 2).map((u) => (
                        <AvatarCell key={u.id} user={u} count={3} index={0} />
                    ))}
                    <div className="col-span-2">
                        <AvatarCell
                            user={visibleUsers[2]}
                            count={3}
                            index={2}
                        />
                    </div>
                </>
            ) : (
                visibleUsers.map((user) => (
                    <AvatarCell
                        key={user.id}
                        user={user}
                        count={count}
                        index={0}
                    />
                ))
            )}
        </div>
    );
}

function AvatarCell({ user, count }: AvatarCellProps) {
    const fallback = user.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <div className="relative h-full w-full bg-gray-200">
            {user.avatar_url ? (
                <img
                    src={user.avatar_url}
                    alt={user.name}
                    className="h-full w-full object-cover"
                />
            ) : (
                <div className="flex h-full w-full items-center justify-center text-xs font-medium text-gray-600">
                    {fallback}
                </div>
            )}
        </div>
    );
}
