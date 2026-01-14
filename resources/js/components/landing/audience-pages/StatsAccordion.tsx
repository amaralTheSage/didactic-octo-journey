import { ArrowRight, Loader2 } from 'lucide-react';
import { useState } from 'react';

interface AccordionItem {
    title: string;
    description: string;
    bullets?: string[];
}

interface StatsAccordionProps {
    eyebrow: string;
    headlineParts: [string, string, string]; // e.g. ["Build.", "Deploy.", "Scale."]
    description: string;
    items: AccordionItem[];
}

export default function StatsAccordion({
    eyebrow,
    headlineParts,
    description,
    items,
}: StatsAccordionProps) {
    const [openIndex, setOpenIndex] = useState(0);

    return (
        <section className="relative overflow-hidden bg-slate-200 px-4 py-24 text-slate-900 md:px-8 lg:px-16">
            {/* Subtle background glow */}
            <div className="from-brand-100/50 pointer-events-none absolute top-0 left-0 h-full w-full bg-gradient-to-br to-transparent"></div>

            <div className="relative z-10 mx-auto grid max-w-7xl grid-cols-1 gap-16 lg:grid-cols-2 lg:gap-24">
                {/* Left Column - Headline & Intro */}
                <div className="flex flex-col justify-center">
                    <div className="mb-6 flex items-center gap-2">
                        <div className="bg-brand-600 h-2 w-2 rounded-full"></div>
                        <span className="text-brand-600 text-xs font-bold tracking-widest uppercase">
                            {eyebrow}
                        </span>
                    </div>

                    <h2 className="mb-8 text-6xl leading-[0.9] font-medium tracking-tighter text-slate-950 md:text-7xl lg:text-8xl">
                        {headlineParts[0]}
                        <br />
                        <span className="flex items-center gap-4 text-cyan-700">
                            <Loader2
                                className="animate-spin text-cyan-300"
                                size={48}
                                strokeWidth={1}
                            />
                            {headlineParts[1]}
                        </span>
                        {headlineParts[2]}
                    </h2>

                    <p className="max-w-md text-lg leading-relaxed text-slate-500 md:text-xl">
                        {description}
                    </p>
                </div>

                {/* Right Column - Accordion */}
                <div className="flex flex-col">
                    {items.map((item, index) => {
                        const isOpen = index === openIndex;
                        const num = (index + 1).toString().padStart(2, '0');

                        return (
                            <div
                                key={index}
                                className={`border-t border-slate-200 transition-all duration-500 ease-in-out ${isOpen ? 'py-8' : 'group cursor-pointer py-6'}`}
                                onClick={() => setOpenIndex(index)}
                            >
                                <div className="mb-4 flex items-center justify-between">
                                    <div className="flex items-baseline gap-6">
                                        <span
                                            className={`font-mono text-sm ${isOpen ? 'text-brand-600' : 'text-cyan-700'}`}
                                        >
                                            {num}
                                        </span>
                                        <h3
                                            className={`text-2xl font-medium tracking-tight transition-colors md:text-3xl ${isOpen ? 'text-slate-950' : 'text-cyan-700 group-hover:text-slate-600'}`}
                                        >
                                            {item.title}
                                        </h3>
                                    </div>

                                    <button
                                        className={`flex h-12 w-12 cursor-pointer items-center justify-center rounded-full transition-all duration-300 ${
                                            isOpen
                                                ? 'rotate-0 bg-slate-950 text-white'
                                                : '-rotate-45 border border-cyan-700 text-cyan-700 group-hover:border-slate-600 group-hover:text-slate-600'
                                        }`}
                                    >
                                        <ArrowRight size={20} />
                                    </button>
                                </div>

                                <div
                                    className={`overflow-hidden transition-all duration-500 ease-in-out ${isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'}`}
                                >
                                    <div className="pl-12 md:pl-16">
                                        <p className="mb-6 max-w-lg leading-relaxed text-slate-600">
                                            {item.description}
                                        </p>
                                        {item.bullets && (
                                            <ul className="space-y-3">
                                                {item.bullets.map(
                                                    (bullet, idx) => (
                                                        <li
                                                            key={idx}
                                                            className="flex items-center gap-3 text-sm font-medium text-slate-700"
                                                        >
                                                            <div className="bg-brand-500 h-1.5 w-1.5 rounded-full"></div>
                                                            {bullet}
                                                        </li>
                                                    ),
                                                )}
                                            </ul>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                    {/* Bottom border for last item */}
                    <div className="border-t border-slate-200"></div>
                </div>
            </div>
        </section>
    );
}
