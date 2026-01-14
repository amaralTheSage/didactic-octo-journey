import { BadgeCheck } from 'lucide-react';
import { useEffect, useState } from 'react';

const testimonials = [
    {
        text: 'A Hubinflu simplificou nossa validação. A segurança do PIX traz uma tranquilidade inédita.',
        name: 'Mariana Costa',
        role: 'TechGrowth',
        tag: 'SEGURANÇA',
    },
    {
        text: 'Gerenciar 50 influenciadores nunca foi tão fácil. O sistema é transparente para todos.',
        name: 'Carlos Mendes',
        role: 'Agência Viral',
        tag: 'ESCALA',
    },
    {
        text: 'Finalmente uma plataforma que valoriza o criador. Recebo em dia, sem burocracia.',
        name: 'Júlia Silva',
        role: 'Influencer',
        tag: 'PAGAMENTOS',
    },
    {
        text: 'A métrica de ROI melhorou 40% desde que começamos a usar a Hubinflu.',
        name: 'Roberto Almeida',
        role: 'FoodBrands',
        tag: 'RESULTADOS',
    },
];

export default function Testimonials() {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isAnimating, setIsAnimating] = useState(false);

    useEffect(() => {
        const interval = setInterval(() => {
            setIsAnimating(true);
            setTimeout(() => {
                setCurrentIndex((prev) => (prev + 1) % testimonials.length);
                setIsAnimating(false);
            }, 500); // Wait for fade out to complete
        }, 5000); // Change every 5 seconds

        return () => clearInterval(interval);
    }, []);

    const current = testimonials[currentIndex];

    return (
        <section className="relative mt-32 overflow-hidden bg-secondary/30 py-32 text-gray-900 md:mt-44 md:py-48">
            {/* Background Ambience - Subtle Light Blue Glow */}
            <div className="pointer-events-none absolute top-0 left-1/2 h-[400px] w-[800px] -translate-x-1/2 rounded-full bg-blue-300/30 mix-blend-multiply blur-[120px]" />

            <div className="relative z-10 container mx-auto px-6 md:px-12">
                <div className="mx-auto flex max-w-5xl flex-col items-center text-center">
                    {/* Animated Text Container */}
                    <div
                        className={`transform transition-all duration-500 ${
                            isAnimating
                                ? 'translate-y-8 opacity-0 blur-sm'
                                : 'blur-0 translate-y-0 opacity-100'
                        }`}
                    >
                        <h2 className="mb-12 text-4xl leading-[1.1] font-bold tracking-tight text-gray-900 uppercase md:text-6xl lg:text-7xl">
                            "{current.text}"
                        </h2>

                        <div className="flex flex-col items-center justify-center gap-4">
                            {/* Name & Role */}
                            <div className="flex items-center gap-3">
                                {/* Inverted for Light Mode: Black Badge on Light Background */}
                                <div className="flex items-center gap-2 bg-black px-3 py-1 text-sm font-bold tracking-wider text-white uppercase shadow-lg">
                                    <BadgeCheck
                                        size={16}
                                        className="fill-blue-500 text-white"
                                    />
                                    {current.name}
                                </div>
                                <span className="text-sm font-bold tracking-widest text-gray-500 uppercase">
                                    {current.role}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Progress Indicators */}
                    <div className="mt-20 flex gap-3">
                        {testimonials.map((_, idx) => (
                            <button
                                key={idx}
                                onClick={() => setCurrentIndex(idx)}
                                className={`h-1.5 rounded-full transition-all duration-300 ${
                                    idx === currentIndex
                                        ? 'w-12 bg-black'
                                        : 'w-2 bg-gray-400 hover:bg-gray-500'
                                }`}
                                aria-label={`Go to testimonial ${idx + 1}`}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
