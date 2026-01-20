interface Stat {
    value: string;
    label: string;
}

interface HeroProps {
    headlineTop: string;
    headlineBottom: string;
    subPillText?: string;
    description: string;
    stats: Stat[];
    bgImage: string;
    floatingCardTitle?: string;
    floatingCardSubtitle?: string;
    role: string;
    ctaText: string;
    invert?: boolean;
    /**
     * Layout variation.
     * 1: Original (Image Left, Text Right)
     * 2: Modern (Text Left, Image Right, Rounded UI)
     * @default 1
     */
    variant?: 1 | 2;
}

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
    variant = 1,
}: HeroProps) {
    // ========================================================================
    // VARIANT 1: The Original Layout (Image Left, Text Right)
    // ========================================================================
    if (variant === 1) {
        return (
            <section className="min-h-screen overflow-hidden bg-white px-4 pt-24 pb-12 text-slate-900 md:px-8 lg:px-16">
                <div className="mx-auto grid grid-cols-1 items-center lg:grid-cols-5 lg:gap-20">
                    {/* Left Column */}
                    <div className="group relative col-span-2 h-[600px] w-full overflow-hidden shadow-2xl shadow-slate-200 md:h-[750px]">
                        <img
                            src={bgImage}
                            alt="Hero visual"
                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-105"
                        />

                        <div className="absolute top-6 left-6">
                            <div className="rounded border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white uppercase backdrop-blur-xs">
                                {role}
                            </div>
                        </div>

                        <div className="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/40"></div>

                        {/* Bottom Text/CTA on Image */}
                        <div className="absolute right-8 bottom-10 left-8 z-20 flex items-end justify-between">
                            <div className="max-w-xs text-white drop-shadow-lg">
                                <p className="mb-2 text-sm font-medium text-white/90">
                                    Plataforma verificada
                                </p>
                                <h3 className="text-2xl leading-tight font-bold">
                                    Hubinflu &copy; 2026
                                </h3>
                                <p className="mt-1 text-xs text-white/70">
                                    Conectando o mercado brasileiro.
                                </p>
                            </div>
                            <button className="cursor-pointer rounded-full bg-white px-6 py-3 text-xs font-bold tracking-widest text-slate-950 uppercase shadow-lg transition-colors hover:bg-secondary">
                                Ver Detalhes
                            </button>
                        </div>
                    </div>

                    {/* Right Column  */}
                    <div className="relative bottom-7 z-10 col-span-3 flex flex-col text-end lg:bottom-0">
                        <div className="max-w-4xl">
                            <h1 className="tracking-loose mb-6 text-7xl leading-[0.95] font-medium md:text-8xl">
                                {headlineTop} <br />
                                <span className="font-semibold text-secondary">
                                    {headlineBottom}
                                </span>{' '}
                            </h1>
                        </div>

                        {/* Description & CTA */}
                        <div className="justify-items mb-12 flex flex-col items-end gap-8 md:flex-row md:justify-between">
                            <button className="group relative flex cursor-pointer items-center gap-3 rounded-full bg-primary px-8 py-4 font-medium tracking-wider whitespace-nowrap text-white shadow-xl shadow-slate-900/20 transition-all duration-300 hover:scale-105 hover:bg-primary">
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
                        <div className="wrap mb-12 flex flex-wrap justify-around gap-12">
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

    // ========================================================================
    // VARIANT 2: The Modern Layout (Text Left, Image Right, Clean UI)
    // ========================================================================
    return (
        <section className="relative min-h-screen overflow-hidden bg-white px-6 pt-32 pb-12 lg:px-12 xl:px-20">
            {/* Background Decor Layer (Abstract Waves from V1 moved to BG) */}
            <div className="absolute top-0 right-0 h-[600px] w-[600px] translate-x-1/4 -translate-y-1/2 rounded-full bg-secondary/5 blur-[120px]"></div>
            <div className="absolute bottom-0 left-0 -z-10 h-96 w-full opacity-30">
                <svg
                    viewBox="0 0 1440 320"
                    className="absolute bottom-0 h-full w-full fill-slate-100"
                    preserveAspectRatio="none"
                >
                    <path
                        fillOpacity="1"
                        d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                    ></path>
                </svg>
            </div>

            <div className="mx-auto grid max-w-7xl grid-cols-1 items-center gap-16 lg:grid-cols-2 lg:gap-24">
                {/* Left Column (Text) */}
                <div className="relative z-10 flex flex-col items-start text-left">
                    {/* Role Pill */}
                    <div className="mb-6 inline-flex items-center rounded-full border border-secondary/20 bg-secondary/5 px-4 py-1.5">
                        <span className="mr-2 flex h-2 w-2 animate-pulse rounded-full bg-secondary"></span>
                        <span className="text-xs font-bold tracking-widest text-secondary uppercase">
                            {role}
                        </span>
                    </div>

                    {/* Headline */}
                    <h1 className="mb-6 text-6xl font-medium tracking-tight text-slate-900 sm:text-7xl lg:text-8xl lg:leading-[0.9]">
                        {headlineTop} <br />
                        <span className="relative inline-block text-secondary">
                            {headlineBottom}
                            {/* Underline Decoration */}
                            <svg
                                className="absolute -bottom-2 left-0 w-full text-indigo-200"
                                viewBox="0 0 200 9"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M2.00025 6.99999C49.5002 0.500004 150.5 -3.49999 198 3.00001"
                                    stroke="currentColor"
                                    strokeWidth="3"
                                    strokeLinecap="round"
                                />
                            </svg>
                        </span>
                    </h1>

                    {/* Description */}
                    <p className="mb-10 max-w-lg text-lg leading-relaxed text-slate-500 lg:text-xl">
                        {description}
                    </p>

                    {/* CTA Row */}
                    <div className="mb-12 flex w-full flex-col items-center gap-6 sm:w-auto sm:flex-row">
                        <button className="group relative w-full overflow-hidden rounded-full bg-slate-900 px-8 py-4 font-semibold text-white shadow-xl shadow-slate-900/20 transition-all duration-300 hover:scale-105 hover:bg-slate-800 sm:w-auto">
                            <span className="relative z-10 flex items-center justify-center gap-2">
                                {ctaText}
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    className="transition-transform group-hover:translate-x-1"
                                >
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </span>
                        </button>
                    </div>

                    {/* Stats Row - Clean Divider Style */}
                    <div className="grid w-full grid-cols-3 gap-8 border-t border-slate-100 pt-8">
                        {stats.map((stat, idx) => (
                            <div key={idx} className="flex flex-col">
                                <span className="text-3xl font-bold text-slate-900">
                                    {stat.value}
                                </span>
                                <span className="mt-1 text-xs font-semibold tracking-wider text-slate-400 uppercase">
                                    {stat.label}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Right Column (Image) */}
                <div className="relative lg:order-last">
                    <div className="relative mx-auto aspect-[4/5] w-full max-w-md lg:max-w-full">
                        {/* Image Card */}
                        <div className="absolute inset-0 overflow-hidden rounded-[2rem] shadow-2xl shadow-indigo-200/50">
                            <img
                                src={bgImage}
                                alt="Hero Variant 2"
                                className="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>

                            {/* Floating Content inside Image (Hubinflu Copy) */}
                            <div className="absolute right-8 bottom-8 left-8 text-white">
                                <div className="flex items-center gap-2">
                                    <div className="h-1 w-8 rounded-full bg-white/50"></div>
                                    <p className="text-xs font-medium tracking-widest text-white/80 uppercase">
                                        Plataforma Verificada
                                    </p>
                                </div>
                                <h3 className="my-3 text-2xl leading-tight font-bold">
                                    Hubinflu &copy; 2026
                                </h3>
                                <p className="text-sm leading-relaxed text-white/90">
                                    &quot;A infraestrutura mais completa para o
                                    mercado de creators do Brasil.&quot;
                                </p>
                            </div>
                        </div>

                        {/* Floating Abstract "Card" Element - Repurposed as a floating badge */}
                        <div className="animate-bounce-slow absolute -top-6 -right-6 z-20 max-w-[240px] rounded-2xl bg-white p-5 shadow-xl ring-1 ring-slate-100">
                            <div className="mb-3 flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                    <span className="text-[10px] font-bold text-slate-400 uppercase">
                                        Ao vivo
                                    </span>
                                </div>
                                <span className="text-xs font-bold text-secondary">
                                    Now
                                </span>
                            </div>
                            <h4 className="text-lg leading-tight font-bold text-slate-900">
                                {floatingCardTitle || 'Hubinflu'}
                            </h4>
                            <p className="mt-1 text-xs text-slate-500">
                                {floatingCardSubtitle ||
                                    'Conectando o mercado.'}
                            </p>

                            {/* Mini Graph Decoration */}
                            <div className="mt-3 flex h-8 items-end gap-1">
                                <div className="h-full w-1/5 rounded-t-sm bg-indigo-100"></div>
                                <div className="h-[60%] w-1/5 rounded-t-sm bg-indigo-200"></div>
                                <div className="h-[80%] w-1/5 rounded-t-sm bg-indigo-300"></div>
                                <div className="h-[40%] w-1/5 rounded-t-sm bg-indigo-400"></div>
                                <div className="h-[90%] w-1/5 rounded-t-sm bg-secondary"></div>
                            </div>
                        </div>

                        {/* Decorative background circle behind image */}
                        <div className="absolute -top-10 -right-10 -z-10 h-64 w-64 rounded-full bg-secondary/10 blur-3xl"></div>
                    </div>
                </div>
            </div>
        </section>
    );
}
