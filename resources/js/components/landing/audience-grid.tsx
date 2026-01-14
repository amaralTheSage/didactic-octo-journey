import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import React from 'react';

const AudienceGrid: React.FC = () => {
    return (
        <section className="bg-white" id="audience-grid">
            <div className="container mx-auto px-6 py-4 md:px-12">
                {/* Section Header */}
                <div className="mb-16">
                    <h2 className="mb-4 text-4xl font-medium tracking-tight md:text-5xl">
                        A Plataforma
                    </h2>
                    <div className="flex items-center gap-2 text-sm font-medium tracking-wider text-gray-400 uppercase">
                        <span>/ Soluções</span>
                        <span>/ Ecossistema</span>
                    </div>
                </div>

                {/* The Grid - Masonry Style */}
                <div className="grid h-auto grid-cols-1 gap-4 md:h-[800px] md:grid-cols-3">
                    {/* Column 1: Agencies (Tall) */}
                    <Link
                        href="/para-agencias"
                        className="group relative h-[500px] w-full cursor-pointer overflow-hidden bg-gray-100 md:h-full"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop"
                            alt="Agencies"
                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:brightness-100 group-hover:grayscale-0 md:brightness-[0.8] md:grayscale md:filter"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-90" />

                        <div className="absolute top-6 right-6 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <div className="rounded-full bg-white/20 p-3 text-white backdrop-blur-md">
                                <ArrowUpRight size={24} />
                            </div>
                        </div>

                        <div className="absolute top-6 left-6">
                            <div className="rounded border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white uppercase backdrop-blur-sm">
                                Agências
                            </div>
                        </div>

                        <div className="absolute right-8 bottom-8 left-8">
                            <h3 className="mb-2 text-3xl leading-tight font-bold text-white">
                                Gestão de <br />
                                Portfólio
                            </h3>
                            <p className="max-w-[80%] transform text-sm text-gray-300 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100 md:translate-y-4 md:opacity-0">
                                Gerencie múltiplos influenciadores, envie
                                propostas em massa e negocie comissões em tempo
                                real.
                            </p>
                        </div>
                    </Link>

                    {/* Companies  */}
                    <Link
                        href="/para-empresas"
                        className="group relative h-[500px] flex-1 cursor-pointer overflow-hidden bg-gray-900 md:h-full"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2301&auto=format&fit=crop"
                            alt="Companies"
                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:grayscale-0 md:grayscale md:filter"
                        />

                        <div className="absolute top-6 right-6 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <div className="rounded-full bg-white/20 p-3 text-white backdrop-blur-md">
                                <ArrowUpRight size={24} />
                            </div>
                        </div>

                        <div className="absolute top-6 left-6">
                            <div className="rounded border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white uppercase backdrop-blur-sm">
                                Empresas
                            </div>
                        </div>

                        <div className="absolute right-8 bottom-8 left-8">
                            <h3 className="mb-2 text-3xl leading-tight font-bold text-white">
                                Alcance
                                <br />
                                Global
                            </h3>
                            <p className="max-w-[80%] transform text-sm text-gray-300 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100 md:translate-y-4 md:opacity-0">
                                Gerencie e anuncie suas campanhas com acesso
                                direto aos maiores influenciadores e agências do
                                Brasil.
                            </p>
                        </div>
                    </Link>

                    {/* Column 3: Influencers (Tall) */}
                    <Link
                        href="/para-influenciadores"
                        className="group relative h-[500px] w-full cursor-pointer overflow-hidden bg-gray-100 md:h-full"
                    >
                        <img
                            src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=1964&auto=format&fit=crop"
                            alt="Influencers"
                            className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:grayscale-0 md:grayscale md:filter"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-80" />

                        <div className="absolute top-6 right-6 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <div className="rounded-full bg-white/20 p-3 text-white backdrop-blur-md">
                                <ArrowUpRight size={24} />
                            </div>
                        </div>

                        <div className="absolute top-6 left-6">
                            <div className="rounded border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white uppercase backdrop-blur-sm">
                                Influenciadores
                            </div>
                        </div>

                        <div className="absolute right-8 bottom-8 left-8">
                            <h3 className="mb-2 text-3xl leading-tight font-bold text-white">
                                Monetize Sua <br />
                                Audiência
                            </h3>
                            <p className="max-w-[80%] transform text-sm text-gray-300 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100 md:translate-y-4 md:opacity-0">
                                Conecte-se com grandes marcas, defina seus
                                preços de tabela e receba com segurança.
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    );
};

export default AudienceGrid;
