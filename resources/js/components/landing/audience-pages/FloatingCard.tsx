import { DollarSign, TrendingUp, Users } from 'lucide-react';

interface FloatingCardProps {
    title: string;
    subtitle: string;
    type: 'chart' | 'profile' | 'payment';
}

export default function FloatingCard({
    title,
    subtitle,
    type,
}: FloatingCardProps) {
    return (
        <div className="w-64 rounded-2xl border border-white/50 bg-white/90 p-4 shadow-xl backdrop-blur-md">
            <div className="mb-4 flex items-start justify-between">
                <div>
                    <h4 className="mb-1 text-xs font-bold tracking-wider text-slate-500 uppercase">
                        {title}
                    </h4>
                    <p className="text-sm leading-tight font-medium text-slate-800">
                        {subtitle}
                    </p>
                </div>
                <div className="rounded-full bg-secondary p-2 text-secondary">
                    {type === 'chart' && <TrendingUp size={16} />}
                    {type === 'profile' && <Users size={16} />}
                    {type === 'payment' && <DollarSign size={16} />}
                </div>
            </div>

            {/* Abstract Visualization based on type */}
            <div className="flex h-12 items-end space-x-1">
                {type === 'chart' && (
                    <>
                        <div className="h-[40%] w-1/5 rounded-t-sm bg-secondary"></div>
                        <div className="h-[60%] w-1/5 rounded-t-sm bg-secondary"></div>
                        <div className="h-[30%] w-1/5 rounded-t-sm bg-secondary"></div>
                        <div className="h-[80%] w-1/5 rounded-t-sm bg-secondary"></div>
                        <div className="h-[100%] w-1/5 rounded-t-sm bg-secondary shadow-lg shadow-secondary/30"></div>
                    </>
                )}
                {type === 'profile' && (
                    <div className="flex -space-x-2">
                        {[1, 2, 3].map((i) => (
                            <div
                                key={i}
                                className="h-8 w-8 overflow-hidden rounded-full border-2 border-white bg-slate-200"
                            >
                                <img
                                    src={`https://picsum.photos/50/50?random=${i + 10}`}
                                    alt="Avatar"
                                    className="h-full w-full object-cover"
                                />
                            </div>
                        ))}
                        <div className="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-secondary text-[10px] font-bold text-secondary">
                            +12
                        </div>
                    </div>
                )}
                {type === 'payment' && (
                    <div className="w-full">
                        <div className="mb-2 h-2 w-full rounded-full bg-slate-100">
                            <div className="h-2 w-3/4 rounded-full bg-green-500"></div>
                        </div>
                        <div className="flex justify-between font-mono text-[10px] text-slate-400">
                            <span>PENDING</span>
                            <span className="font-bold text-green-600">
                                PAID
                            </span>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
