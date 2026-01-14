import { ArrowRight } from 'lucide-react';

export default function CTASection() {
    return (
        <section className="border-t border-slate-100 bg-white px-4 py-32 md:px-8 lg:px-16">
            <div className="mx-auto max-w-5xl text-center">
                <h2 className="mb-8 text-5xl leading-[0.95] font-medium tracking-tighter text-slate-950 md:text-7xl">
                    Pronto para começar?
                </h2>
                <p className="mx-auto mb-12 max-w-3xl text-xl leading-relaxed font-light text-slate-500 md:text-2xl">
                    Junte-se ao marketplace mais completo do Brasil. Conecte-se,
                    negocie e escale seus resultados com segurança e
                    transparência.
                </p>
                <div className="flex justify-center">
                    <a
                        href={'/dashboard/register'}
                        className="group hover:bg-brand-600 relative inline-flex items-center gap-3 rounded-full bg-slate-950 px-10 py-5 text-lg font-bold tracking-wide text-white shadow-2xl shadow-slate-900/20 transition-all hover:scale-105"
                    >
                        Criar Conta Grátis
                        <ArrowRight className="h-5 w-5 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>
            </div>
        </section>
    );
}
