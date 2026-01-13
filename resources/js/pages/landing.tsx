import Footer from '@/components/landing/footer';
import Header from '@/components/landing/header';
import { Button } from '@/components/ui/button';
import { ArrowRight, Building2, Sparkles, Users } from 'lucide-react';

export default function Landing() {
    return (
        <div className="min-h-screen bg-background">
            <Header />

            <main>
                {/* Hero Section */}
                <section className="relative flex min-h-screen items-center justify-center px-6 pt-16">
                    <div className="mx-auto max-w-5xl text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-balance md:text-6xl lg:text-7xl">
                            O marketplace completo para{' '}
                            <span className="text-secondary">
                                marketing de influência
                            </span>
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground md:text-xl">
                            Conectamos empresas, agências e influenciadores em
                            uma única plataforma. Negocie, gerencie e execute
                            campanhas com transparência e eficiência.
                        </p>
                        <div className="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                            <Button
                                asChild
                                size="lg"
                                className="rounded-full px-8"
                            >
                                <a href="/dashboard/login">
                                    Começar Agora
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </a>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                size="lg"
                                className="rounded-full bg-transparent px-8"
                            >
                                <a href="#audiencias">Saiba Mais</a>
                            </Button>
                        </div>
                    </div>
                </section>

                {/* Audience Selection Section */}
                <section
                    id="audiencias"
                    className="border-t border-border px-6 py-24"
                >
                    <div className="mx-auto max-w-7xl">
                        <div className="mb-16 text-center">
                            <p className="text-sm font-medium tracking-wider text-secondary uppercase">
                                Para quem é o Hubinflu
                            </p>
                            <h2 className="mt-4 text-3xl font-bold md:text-4xl">
                                Escolha seu perfil
                            </h2>
                        </div>

                        <div className="grid gap-6 md:grid-cols-3">
                            {/* Empresas Card */}
                            <a
                                href="/para-empresas"
                                className="group relative flex flex-col rounded-2xl border border-border bg-card p-8 transition-all hover:border-secondary/50 hover:bg-card/80"
                            >
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Building2 className="h-7 w-7" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold">
                                    Sou Empresa
                                </h3>
                                <p className="mb-6 flex-1 text-muted-foreground">
                                    Lugar unificado para anunciar suas campanhas
                                    para milhares de influenciadores. Receba
                                    propostas, negocie e valide pagamentos com
                                    facilidade.
                                </p>
                                <div className="flex items-center text-sm font-medium text-secondary">
                                    Conhecer mais
                                    <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </a>

                            {/* Agências Card */}
                            <a
                                href="/para-agencias"
                                className="group relative flex flex-col rounded-2xl border border-border bg-card p-8 transition-all hover:border-secondary/50 hover:bg-card/80"
                            >
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Users className="h-7 w-7" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold">
                                    Sou Agência
                                </h3>
                                <p className="mb-6 flex-1 text-muted-foreground">
                                    Captação de campanhas para seus
                                    influenciadores e gestão completa do
                                    portfólio. Submeta propostas e negocie
                                    comissões de forma transparente.
                                </p>
                                <div className="flex items-center text-sm font-medium text-secondary">
                                    Conhecer mais
                                    <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </a>

                            {/* Influenciadores Card */}
                            <a
                                href="/para-influenciadores"
                                className="group relative flex flex-col rounded-2xl border border-border bg-card p-8 transition-all hover:border-secondary/50 hover:bg-card/80"
                            >
                                <div className="mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Sparkles className="h-7 w-7" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold">
                                    Sou Influenciador
                                </h3>
                                <p className="mb-6 flex-1 text-muted-foreground">
                                    Marketplace para anúncios de produtos e
                                    participação em campanhas. Crie seu perfil,
                                    defina seus preços e seja descoberto por
                                    grandes marcas.
                                </p>
                                <div className="flex items-center text-sm font-medium text-secondary">
                                    Conhecer mais
                                    <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </a>
                        </div>
                    </div>
                </section>

                {/* Stats Section */}
                <section className="border-t border-border px-6 py-24">
                    <div className="mx-auto grid max-w-7xl gap-8 md:grid-cols-4">
                        <div className="text-center">
                            <p className="text-4xl font-bold text-secondary">
                                1000+
                            </p>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Influenciadores cadastrados
                            </p>
                        </div>
                        <div className="text-center">
                            <p className="text-4xl font-bold text-secondary">
                                500+
                            </p>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Campanhas realizadas
                            </p>
                        </div>
                        <div className="text-center">
                            <p className="text-4xl font-bold text-secondary">
                                98%
                            </p>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Taxa de satisfação
                            </p>
                        </div>
                        <div className="text-center">
                            <p className="text-4xl font-bold text-secondary">
                                R$2M+
                            </p>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Negociados na plataforma
                            </p>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="border-t border-border bg-card px-6 py-24">
                    <div className="mx-auto max-w-3xl text-center">
                        <h2 className="text-3xl font-bold md:text-4xl">
                            Pronto para começar?
                        </h2>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Junte-se ao maior marketplace de marketing de
                            influência do Brasil.
                        </p>
                        <Button
                            asChild
                            size="lg"
                            className="mt-8 rounded-full px-8"
                        >
                            <a href="/dashboard/login">
                                Criar Conta Grátis
                                <ArrowRight className="ml-2 h-4 w-4" />
                            </a>
                        </Button>
                    </div>
                </section>
            </main>

            <Footer />
        </div>
    );
}
