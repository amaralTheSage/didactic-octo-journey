import { TrendingUp } from 'lucide-react';
import { useEffect, useState } from 'react';

const HeadlineTypewriter = ({
    prefix,
    highlight,
    suffix,
}: {
    prefix: string;
    highlight: string;
    suffix?: string;
}) => {
    const [t1, setT1] = useState('');
    const [t2, setT2] = useState('');
    const [t3, setT3] = useState('');
    const [activeLine, setActiveLine] = useState(1);
    const [isDone, setIsDone] = useState(false);

    useEffect(() => {
        let timeoutId: ReturnType<typeof setTimeout>;
        let charIndex = 0;
        let currentLine = 1;

        // Reset
        setT1('');
        setT2('');
        setT3('');
        setActiveLine(1);
        setIsDone(false);

        const type = () => {
            if (currentLine === 1) {
                if (charIndex < prefix.length) {
                    setT1(prefix.slice(0, charIndex + 1));
                    charIndex++;
                    timeoutId = setTimeout(type, 50);
                } else {
                    currentLine = 2;
                    charIndex = 0;
                    setActiveLine(2);
                    timeoutId = setTimeout(type, 150);
                }
            } else if (currentLine === 2) {
                if (charIndex < highlight.length) {
                    setT2(highlight.slice(0, charIndex + 1));
                    charIndex++;
                    timeoutId = setTimeout(type, 50);
                } else {
                    if (suffix) {
                        currentLine = 3;
                        charIndex = 0;
                        setActiveLine(3);
                        timeoutId = setTimeout(type, 150);
                    } else {
                        setActiveLine(0);
                        setIsDone(true);
                    }
                }
            } else if (currentLine === 3 && suffix) {
                if (charIndex < suffix.length) {
                    setT3(suffix.slice(0, charIndex + 1));
                    charIndex++;
                    timeoutId = setTimeout(type, 50);
                } else {
                    setActiveLine(0);
                    setIsDone(true);
                }
            }
        };

        timeoutId = setTimeout(type, 500);
        return () => clearTimeout(timeoutId);
    }, [prefix, highlight, suffix]);

    const Cursor = () => (
        <span className="ml-1 inline-block h-[0.7em] w-[0.06em] animate-pulse bg-slate-950 align-baseline md:h-[0.75em]"></span>
    );

    return (
        <h1 className="mb-6 text-6xl leading-[0.95] font-medium tracking-tighter text-slate-950 md:text-8xl">
            <span className="block">
                {t1}
                {activeLine === 1 && <Cursor />}
            </span>
            <span className="block text-slate-400">
                {t2}
                {(activeLine === 2 || (isDone && !suffix)) && <Cursor />}
            </span>
            {suffix && (
                <span className="block">
                    {t3}
                    {(activeLine === 3 || isDone) && <Cursor />}
                </span>
            )}
        </h1>
    );
};

export default function Hero() {
    return (
        <section className="relative w-full overflow-hidden pt-24 pb-32 md:pt-40 md:pb-44">
            <div className="container mx-auto px-6 md:px-12">
                {/* Text Header */}
                <div className="mb-12 flex flex-col justify-between gap-8 md:mb-20 md:flex-row md:items-end">
                    <div className="max-w-4xl md:h-76">
                        <HeadlineTypewriter
                            prefix={'Orquestrando'}
                            highlight={'a publicidade'}
                            suffix={'no Brasil'}
                        />
                    </div>

                    <div className="mb-2 flex flex-col items-start gap-6 md:items-end">
                        <p className="max-w-sm text-lg leading-relaxed font-medium tracking-tight text-gray-500 md:text-right">
                            O ecossistema definitivo para gerenciar, negociar e
                            escalar campanhas de marketing de influência com
                            segurança PIX.
                        </p>
                        <button
                            className="group flex cursor-pointer items-center gap-3 rounded-full bg-primary px-8 py-4 font-medium text-white transition-all hover:scale-105"
                            onClick={() =>
                                document
                                    .getElementById('audience-grid')
                                    ?.scrollIntoView({ behavior: 'smooth' })
                            }
                        >
                            Comece agora <TrendingUp />
                        </button>
                    </div>
                </div>

                {/* Hero Image */}
                <div className="group relative hidden aspect-[21/9] w-full overflow-hidden md:block">
                    {/* Main Abstract/Artistic Image */}
                    <img
                        src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
                        alt="Abstract Influencer Art"
                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />

                    {/* Overlay UI Element - Bottom Left */}
                    <div className="animate-fade-in-up absolute bottom-6 left-6 w-full max-w-xs rounded-2xl bg-white shadow-2xl md:bottom-12 md:left-12 md:max-w-sm">
                        <img
                            src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fi.pinimg.com%2Foriginals%2Fd4%2F32%2F4c%2Fd4324c80dc12b6c7e9577b87f85971cc.png&f=1&nofb=1&ipt=76c0c08ac79d2ec8386900a2b4f744812117ed1f5895809bc57b30e8fd2c39d3"
                            alt=""
                            className="rounded-2xl"
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}
