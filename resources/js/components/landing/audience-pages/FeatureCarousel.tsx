import { CheckCircle2 } from 'lucide-react';
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
}

export default function FeatureCarousel({
    headingStart,
    headingHighlight,
    headingEnd,
    slides,
}: FeatureCarouselProps) {
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => {
            setCurrent((prev) => (prev + 1) % slides.length);
        }, 5000);
        return () => clearInterval(timer);
    }, [slides.length]);

    return (
        <section className="overflow-hidden bg-white px-4 py-24 md:px-8 lg:px-16">
            <div className="mx-auto grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
                {/* Left Column - Image Carousel */}
                <div className="relative aspect-[4/3] overflow-hidden bg-slate-100 shadow-2xl shadow-slate-200">
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
                            <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                    ))}

                    {/* Simple Indicators overlaid on image */}
                    <div className="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
                        {slides.map((_, idx) => (
                            <button
                                key={idx}
                                onClick={() => setCurrent(idx)}
                                className={`h-2 w-2 rounded-full transition-all duration-300 ${idx === current ? 'w-6 bg-white' : 'bg-white/50 hover:bg-white/80'}`}
                                aria-label={`Go to slide ${idx + 1}`}
                            />
                        ))}
                    </div>
                </div>

                {/* Right Column - Text Content */}
                <div className="flex flex-col justify-center">
                    <h2 className="mb-8 text-4xl leading-tight font-black tracking-tight text-slate-950 md:text-5xl">
                        {headingStart}{' '}
                        <span className="text-slate-400">
                            {headingHighlight}
                        </span>{' '}
                        {headingEnd}
                    </h2>

                    <div className="relative min-h-[200px]">
                        {slides.map((slide, index) => (
                            <div
                                key={index}
                                className={`absolute inset-0 transition-all duration-500 ${
                                    index === current
                                        ? 'pointer-events-auto translate-y-0 opacity-100'
                                        : 'pointer-events-none translate-y-4 opacity-0'
                                }`}
                            >
                                <h3 className="mb-4 text-xl font-bold text-slate-900">
                                    {slide.title}
                                </h3>
                                <p className="mb-8 text-lg leading-relaxed text-slate-500">
                                    {slide.description}
                                </p>

                                <div className="flex flex-wrap gap-6">
                                    {slide.tags.map((tag, tIdx) => (
                                        <div
                                            key={tIdx}
                                            className="flex items-center gap-2"
                                        >
                                            <CheckCircle2
                                                className="text-secondary"
                                                size={18}
                                                strokeWidth={3}
                                            />
                                            <span className="text-xs font-bold tracking-wider text-slate-900 uppercase">
                                                {tag}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Progress Bar for Current Slide */}
                    <div className="mt-8 h-1 w-full overflow-hidden rounded-full bg-slate-200">
                        <div
                            key={current} // Reset animation on change
                            className="animate-progress h-full origin-left bg-primary"
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
