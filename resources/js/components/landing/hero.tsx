import { TrendingUp } from 'lucide-react';

export default function Hero() {
    return (
        <section className="relative w-full overflow-hidden pt-24 pb-32 md:pt-40 md:pb-44">
            <div className="container mx-auto px-6 md:px-12">
                {/* Text Header */}
                <div className="mb-12 flex flex-col items-end justify-between gap-8 md:mb-20 md:flex-row">
                    <div className="max-w-4xl">
                        <h1 className="mb-6 text-6xl leading-[0.95] font-medium tracking-tighter md:text-8xl">
                            Orquestrando <br />
                            <span className="text-gray-400">
                                a publicidade
                            </span>{' '}
                            <br />
                            no Brasil
                        </h1>
                    </div>

                    <div className="mb-2 flex flex-col items-start gap-6 md:items-end">
                        <p className="max-w-sm text-lg leading-relaxed font-medium tracking-tight text-gray-500 md:text-right">
                            O ecossistema definitivo para gerenciar, negociar e
                            escalar campanhas de marketing de influência com
                            segurança PIX.
                        </p>
                        <button
                            className="group flex items-center gap-3 rounded-full bg-primary px-8 py-4 font-medium text-white transition-all hover:scale-105"
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
                <div className="group relative aspect-[4/3] w-full overflow-hidden md:aspect-[21/9]">
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
