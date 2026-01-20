import { ArrowRight, CheckCircle2 } from 'lucide-react';
import { useEffect, useState } from 'react';

interface FeatureSlide {
    image: string;
    title: string;
    description: string;
    tags: string[];
}

interface FeatureCarouselProps {
    headingStart: string;
    headingHighlight: string;
    headingEnd?: string;
    slides: FeatureSlide[];
    /**
     * Layout variation.
     * 1: Original (Image Left, Text Right, Bottom Progress)
     * 2: Modern (Vertical Interactive Tabs Left, Image Right)
     * @default 1
     */
    variant?: 1 | 2;
}

export default function FeatureCarousel({
    headingStart,
    headingHighlight,
    headingEnd,
    slides,
    variant = 1,
}: FeatureCarouselProps) {
    const [current, setCurrent] = useState(0);

    // Auto-advance logic
    useEffect(() => {
        const timer = setInterval(() => {
            setCurrent((prev) => (prev + 1) % slides.length);
        }, 5000);
        return () => clearInterval(timer);
    }, [slides.length, current]); // Added current to dep array to reset timer on manual click effectively? Actually standard practice is just length, but creating new interval on current change resets duration.

    // ========================================================================
    // VARIANT 1: Original (Image Left, Text Right)
    // ========================================================================
    if (variant === 1) {
        return (
            <section className="overflow-hidden bg-white px-4 py-24 md:px-8 lg:px-16">
                <div className="mx-auto grid max-w-7xl grid-cols-1 items-center gap-16 lg:grid-cols-2">
                    {/* Left Column - Image Carousel */}
                    <div className="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100 shadow-2xl shadow-slate-200">
                        {slides.map((slide, index) => (
                            <div
                                key={index}
                                className={`absolute inset-0 transition-opacity duration-1000 ease-in-out ${index === current ? 'opacity-100' : 'opacity-0'}`}
                            >
                                <img
                                    src={slide.image}
                                    alt={slide.title}
                                    className="h-full w-full object-cover"
                                />
                                {/* Overlay gradient for depth */}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            </div>
                        ))}

                        {/* Simple Indicators overlaid on image */}
                        <div className="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2">
                            {slides.map((_, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => setCurrent(idx)}
                                    className={`h-1.5 rounded-full transition-all duration-300 ${idx === current ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'}`}
                                    aria-label={`Go to slide ${idx + 1}`}
                                />
                            ))}
                        </div>
                    </div>

                    {/* Right Column - Text Content */}
                    <div className="flex flex-col justify-center">
                        <h2 className="mb-8 text-4xl leading-tight font-black tracking-tight text-slate-950 md:text-5xl">
                            {headingStart}{' '}
                            <span className="text-indigo-500">
                                {headingHighlight}
                            </span>{' '}
                            {headingEnd}
                        </h2>

                        <div className="relative min-h-[240px]">
                            {slides.map((slide, index) => (
                                <div
                                    key={index}
                                    className={`absolute inset-0 transition-all duration-500 ${
                                        index === current
                                            ? 'pointer-events-auto translate-y-0 opacity-100'
                                            : 'pointer-events-none translate-y-4 opacity-0'
                                    }`}
                                >
                                    <h3 className="mb-4 text-2xl font-bold text-slate-900">
                                        {slide.title}
                                    </h3>
                                    <p className="mb-8 text-lg leading-relaxed text-slate-500">
                                        {slide.description}
                                    </p>

                                    <div className="flex flex-wrap gap-4">
                                        {slide.tags.map((tag, tIdx) => (
                                            <div
                                                key={tIdx}
                                                className="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1"
                                            >
                                                <CheckCircle2
                                                    className="text-indigo-600"
                                                    size={16}
                                                    strokeWidth={3}
                                                />
                                                <span className="text-[10px] font-bold tracking-wider text-slate-700 uppercase">
                                                    {tag}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Progress Bar for Current Slide */}
                        <div className="mt-12 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                            <div
                                key={current} // Reset animation on change
                                className="animate-progress h-full origin-left bg-indigo-600"
                                style={{
                                    animationDuration: '5000ms',
                                    animationTimingFunction: 'linear',
                                }}
                            ></div>
                        </div>
                    </div>
                </div>

                {/* Custom Keyframe for the progress bar */}
                <style>{`
                @keyframes progress {
                    from { width: 0%; }
                    to { width: 100%; }
                }
                .animate-progress {
                    animation-name: progress;
                }
            `}</style>
            </section>
        );
    }

    // ========================================================================
    // VARIANT 2: Modern (Vertical Tabs Left, Image Right)
    // ========================================================================
    return (
        <section className="bg-slate-50 px-4 py-24 md:px-8 lg:px-16">
            <div className="mx-auto grid max-w-7xl grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-20">
                {/* Left Column - Navigation List (Span 5) */}
                <div className="flex flex-col justify-center lg:col-span-5">
                    <h2 className="mb-10 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
                        {headingStart}{' '}
                        <span className="border-b-4 border-indigo-200 text-indigo-600">
                            {headingHighlight}
                        </span>
                        {headingEnd && ` ${headingEnd}`}
                    </h2>

                    <div className="flex flex-col gap-4">
                        {slides.map((slide, index) => {
                            const isActive = current === index;
                            return (
                                <button
                                    key={index}
                                    onClick={() => setCurrent(index)}
                                    className={`group relative overflow-hidden rounded-xl border p-6 text-left transition-all duration-300 ${
                                        isActive
                                            ? 'scale-100 border-indigo-100 bg-white shadow-xl shadow-indigo-100/50'
                                            : 'scale-95 border-transparent bg-transparent opacity-60 hover:bg-white/50 hover:opacity-100'
                                    }`}
                                >
                                    <div className="mb-2 flex items-center justify-between">
                                        <h3
                                            className={`text-lg font-bold transition-colors ${isActive ? 'text-indigo-900' : 'text-slate-600'}`}
                                        >
                                            {slide.title}
                                        </h3>
                                        {isActive && (
                                            <ArrowRight
                                                className="animate-pulse text-indigo-500"
                                                size={16}
                                            />
                                        )}
                                    </div>

                                    {/* Expandable Content */}
                                    <div
                                        className={`grid transition-all duration-500 ease-in-out ${
                                            isActive
                                                ? 'grid-rows-[1fr] opacity-100'
                                                : 'grid-rows-[0fr] opacity-0'
                                        }`}
                                    >
                                        <div className="overflow-hidden">
                                            <p className="mb-4 text-sm leading-relaxed text-slate-500">
                                                {slide.description}
                                            </p>

                                            {/* Integrated Progress Bar */}
                                            <div className="h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                                <div
                                                    className="animate-progress h-full origin-left bg-indigo-500"
                                                    style={{
                                                        animationDuration:
                                                            '5000ms',
                                                        animationTimingFunction:
                                                            'linear',
                                                    }}
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* Right Column - Image Display (Span 7) */}
                <div className="relative aspect-4/3 w-full overflow-hidden rounded-3xl bg-slate-200 shadow-2xl ring-4 ring-white lg:col-span-7">
                    {slides.map((slide, index) => (
                        <div
                            key={index}
                            className={`absolute inset-0 transform transition-all duration-700 ease-out ${
                                index === current
                                    ? 'translate-x-0 scale-100 opacity-100'
                                    : 'translate-x-8 scale-110 opacity-0'
                            }`}
                        >
                            <img
                                src={slide.image}
                                alt={slide.title}
                                className="h-full w-full object-cover"
                            />

                            {/* Gradient & Tags Overlay */}
                            <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>

                            <div className="absolute right-8 bottom-8 left-8">
                                <div className="flex flex-wrap gap-2">
                                    {slide.tags.map((tag, tIdx) => (
                                        <span
                                            key={tIdx}
                                            className="rounded-full border border-white/10 bg-white/20 px-3 py-1 text-xs font-bold text-white backdrop-blur-md"
                                        >
                                            {tag}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
            {/* Keyframe style reused */}
            <style>{`
                @keyframes progress {
                    from { width: 0%; }
                    to { width: 100%; }
                }
                .animate-progress {
                    animation-name: progress;
                }
            `}</style>
        </section>
    );
}
