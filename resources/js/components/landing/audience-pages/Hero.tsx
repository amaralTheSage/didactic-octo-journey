export default function Hero({
    headlineTop,
    headlineBottom,
    subPillText,
    description,
    stats,
    bgImage,
    floatingCardTitle,
    floatingCardSubtitle,
    role,
    ctaText,
    invert,
}) {
    return (
        <section className="min-h-screen overflow-hidden bg-white px-4 pt-24 pb-12 text-slate-900 md:px-8">
            <div className="mx-auto grid grid-cols-1 items-center gap-12 lg:grid-cols-5 lg:gap-20">
                {/* Left Column */}

                <div className="group relative col-span-2 h-[600px] w-full overflow-hidden shadow-2xl shadow-slate-200 md:h-[750px]">
                    <img
                        src={bgImage}
                        alt="Hero visual"
                        className="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/40"></div>

                    {/* Bottom Text/CTA on Image */}
                    <div className="absolute right-8 bottom-10 left-8 z-20 flex items-end justify-between">
                        <div className="max-w-xs text-white drop-shadow-lg">
                            <p className="mb-2 text-sm font-medium text-white/90">
                                Plataforma verificada
                            </p>
                            <h3 className="text-2xl leading-tight font-bold">
                                Hubinflu &copy; 2024
                            </h3>
                            <p className="mt-1 text-xs text-white/70">
                                Conectando o mercado brasileiro.
                            </p>
                        </div>
                        <button className="hover:bg-secondaryrounded-full bg-white px-6 py-3 text-xs font-bold tracking-widest text-slate-950 uppercase shadow-lg transition-colors">
                            Ver Detalhes
                        </button>
                    </div>
                </div>

                {/* Right Column  */}
                <div className="z-10 col-span-3 flex flex-col text-end">
                    <div className="max-w-4xl">
                        <h1 className="tracking-loose mb-6 text-6xl leading-[0.95] font-medium md:text-8xl">
                            {headlineTop} <br />
                            <span className="font-semibold text-secondary">
                                {headlineBottom}
                            </span>{' '}
                        </h1>
                    </div>

                    {/* Description & CTA */}
                    <div className="justify-items mb-12 flex flex-col items-end gap-8 md:flex-row md:justify-between">
                        <button className="group group relative flex items-center gap-3 rounded-full bg-primary px-8 py-4 font-medium tracking-wider whitespace-nowrap text-white shadow-xl shadow-slate-900/20 transition-all duration-300 hover:scale-105 hover:bg-primary">
                            {ctaText}
                            <span className="absolute -top-1 -right-1 flex h-3 w-3">
                                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-secondary opacity-75"></span>
                                <span className="relative inline-flex h-3 w-3 rounded-full bg-secondary"></span>
                            </span>
                        </button>

                        <p className="max-w-lg text-lg leading-relaxed text-slate-500 md:text-xl">
                            {description}
                        </p>
                    </div>

                    {/* Stats Row */}
                    <div className="mb-12 grid grid-cols-3 gap-8">
                        {stats.map((stat, idx) => (
                            <div key={idx} className="flex items-end">
                                <span className="mr-2 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
                                    {stat.value}
                                </span>
                                <span className="mt-1 mb-1.5 w-min text-start text-xs font-medium tracking-wide text-wrap text-slate-500 uppercase">
                                    {stat.label}
                                </span>
                            </div>
                        ))}
                    </div>

                    {/* Bottom Abstract Card */}
                    <div className="group relative h-48 w-full cursor-pointer overflow-hidden shadow-2xl shadow-indigo-100 md:h-56">
                        {/* Mesh Gradient Background */}
                        <div className="absolute inset-0 bg-gradient-to-br from-secondary via-indigo-200 to-purple-200 opacity-80 transition-transform duration-700 group-hover:scale-105"></div>
                        <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-40 mix-blend-overlay"></div>

                        {/* Abstract Waves */}
                        <div className="absolute right-0 bottom-0 left-0 h-full">
                            <svg
                                viewBox="0 0 1440 320"
                                className="absolute bottom-0 h-full w-full fill-secondary opacity-60 mix-blend-multiply"
                            >
                                <path
                                    fillOpacity="1"
                                    d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,138.7C672,117,768,107,864,122.7C960,139,1056,181,1152,197.3C1248,213,1344,203,1392,197.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                                ></path>
                            </svg>
                            <svg
                                viewBox="0 0 1440 320"
                                className="absolute bottom-0 h-full w-full translate-x-20 fill-purple-500 opacity-60 mix-blend-multiply"
                            >
                                <path
                                    fillOpacity="1"
                                    d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,224C672,245,768,267,864,261.3C960,256,1056,224,1152,202.7C1248,181,1344,171,1392,165.3L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
