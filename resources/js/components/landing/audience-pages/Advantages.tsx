import { LucideIcon } from 'lucide-react';

interface AdvantageItem {
    icon: LucideIcon;
    title: string;
    description: string;
}

interface AdvantagesProps {
    title: string;
    subtitle: string;
    items: AdvantageItem[];
}

export default function Advantages({
    title,
    subtitle,
    items,
}: AdvantagesProps) {
    return (
        <section className="mx-auto max-w-7xl bg-white py-20 md:mx-8 lg:mx-16">
            <div className="mx-auto">
                <div className="mb-12">
                    <h2 className="mb-4 text-3xl font-bold tracking-tight text-slate-950 md:text-4xl">
                        {title}
                    </h2>
                    <p className="max-w-2xl text-lg leading-relaxed text-slate-500">
                        {subtitle}
                    </p>
                </div>

                <div className="overflow-hidden">
                    <div className="grid grid-cols-1 bg-white md:grid-cols-2 lg:grid-cols-4">
                        {items.map((item, index) => (
                            <div
                                key={index}
                                className="group p-8 transition-colors duration-300 hover:bg-slate-100"
                            >
                                <div className="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-900 transition-colors">
                                    <item.icon size={20} strokeWidth={2} />
                                </div>
                                <h3 className="mb-3 text-lg font-bold text-slate-900">
                                    {item.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-slate-500">
                                    {item.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
