import { ArrowRight } from 'lucide-react';

interface CTASectionProps {
    title: string;
    description: string;
    ctaText: string;
    ctaHref: string;
    backgroundColor?: string;
}

export default function CTASection({
    title,
    description,
    ctaText,
    ctaHref,
    backgroundColor = 'bg-white',
}: CTASectionProps) {
    const titleColor = 'text-slate-950';
    const descColor = 'text-slate-500';
    const buttonClass = 'bg-slate-900 text-white hover:bg-slate-800';

    return (
        <section
            className={`border-t border-slate-100 px-4 py-48 md:px-8 lg:px-16 ${backgroundColor}`}
        >
            <div className="mx-auto max-w-5xl text-center">
                <h2
                    className={`mb-8 text-5xl leading-[0.95] font-medium tracking-tighter md:text-7xl ${titleColor}`}
                >
                    {title}
                </h2>
                <p
                    className={`mx-auto mb-12 max-w-3xl text-xl leading-relaxed font-light md:text-2xl ${descColor}`}
                >
                    {description}
                </p>
                <div className="flex justify-center">
                    <a
                        href={ctaHref}
                        className={`group relative inline-flex items-center gap-3 rounded-full px-10 py-5 text-lg font-bold tracking-wide shadow-2xl shadow-slate-900/20 transition-all hover:scale-105 ${buttonClass}`}
                    >
                        {ctaText}
                        <ArrowRight className="h-5 w-5 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>
            </div>
        </section>
    );
}
