import Footer from '@/components/landing/footer';
import Header from '@/components/landing/header';
import { Button } from '@/components/ui/button';
import {
    ArrowRight,
    Briefcase,
    FileText,
    MessageSquare,
    PiggyBank,
    TrendingUp,
    Users,
} from 'lucide-react';

export default function ParaAgencias() {
    return (
        <div className="min-h-screen bg-background">
            <Header />

            <main>
                {/* Hero Section */}
                <section className="relative flex min-h-[80vh] items-center px-6 pt-16">
                    <div className="mx-auto max-w-7xl">
                        <div className="grid items-center gap-12 lg:grid-cols-2">
                            <div>
                                <p className="text-sm font-medium tracking-wider text-secondary uppercase">
                                    Para Agências
                                </p>
                                <h1 className="mt-4 text-4xl font-bold tracking-tight text-balance md:text-5xl lg:text-6xl">
                                    Capte campanhas e gerencie seus
                                    influenciadores
                                </h1>
                                <p className="mt-6 text-lg text-muted-foreground">
                                    Acesse oportunidades de campanhas de grandes
                                    empresas, gerencie seu portfólio de
                                    influenciadores e submeta propostas
                                    competitivas. Controle comissões e negocie
                                    diretamente na plataforma.
                                </p>
                                <div className="mt-10 flex flex-col gap-4 sm:flex-row">
                                    <Button
                                        asChild
                                        size="lg"
                                        className="rounded-full px-8"
                                    >
                                        <a href="/dashboard/login">
                                            Cadastrar Minha Agência
                                            <ArrowRight className="ml-2 h-4 w-4" />
                                        </a>
                                    </Button>
                                    <Button
                                        asChild
                                        variant="outline"
                                        size="lg"
                                        className="rounded-full bg-transparent px-8"
                                    >
                                        <a href="#como-funciona">
                                            Ver Como Funciona
                                        </a>
                                    </Button>
                                </div>
                            </div>
                            <div className="relative hidden lg:block">
                                <div className="aspect-square rounded-3xl bg-secondary/10 p-8">
                                    <div className="flex h-full items-center justify-center">
                                        <Briefcase className="h-32 w-32 text-secondary/50" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Benefits Section */}
                <section className="border-t border-border px-6 py-24">
                    <div className="mx-auto max-w-7xl">
                        <div className="mb-16 text-center">
                            <p className="text-sm font-medium tracking-wider text-secondary uppercase">
                                Benefícios
                            </p>
                            <h2 className="mt-4 text-3xl font-bold md:text-4xl">
                                Potencialize sua agência
                            </h2>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Users className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Gestão de Portfólio
                                </h3>
                                <p className="text-muted-foreground">
                                    Cadastre e gerencie todos os seus
                                    influenciadores em um só lugar. Organize por
                                    nicho, alcance e especialidade.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <FileText className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Propostas Detalhadas
                                </h3>
                                <p className="text-muted-foreground">
                                    Submeta propostas completas com seleção de
                                    influenciadores e precificação customizada
                                    por campanha.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <PiggyBank className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Controle de Comissões
                                </h3>
                                <p className="text-muted-foreground">
                                    Negocie suas comissões diretamente com as
                                    empresas. Defina valores transparentes para
                                    cada campanha.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <MessageSquare className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Negociação Direta
                                </h3>
                                <p className="text-muted-foreground">
                                    Chat em tempo real com empresas e seus
                                    influenciadores. Alinhe expectativas e feche
                                    negócios.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <TrendingUp className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Acesso a Oportunidades
                                </h3>
                                <p className="text-muted-foreground">
                                    Navegue por campanhas de diversas empresas.
                                    Encontre as melhores oportunidades para seu
                                    portfólio.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Briefcase className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Preços Flexíveis
                                </h3>
                                <p className="text-muted-foreground">
                                    Ajuste os preços dos influenciadores por
                                    campanha. Valores podem diferir das tabelas
                                    base.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* How it Works Section */}
                <section
                    id="como-funciona"
                    className="border-t border-border bg-card px-6 py-24"
                >
                    <div className="mx-auto max-w-7xl">
                        <div className="mb-16 text-center">
                            <p className="text-sm font-medium tracking-wider text-secondary uppercase">
                                Como Funciona
                            </p>
                            <h2 className="mt-4 text-3xl font-bold md:text-4xl">
                                Da captação à entrega
                            </h2>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    1
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Cadastre seu Portfólio
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Adicione seus influenciadores com perfis
                                    completos e tabelas de preço.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    2
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Encontre Campanhas
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Navegue pelas campanhas disponíveis e
                                    identifique as melhores oportunidades.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    3
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Envie Propostas
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Selecione influenciadores, defina preços e
                                    submeta sua proposta.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    4
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Gerencie e Entregue
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Acompanhe aprovações e coordene a execução
                                    da campanha.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="border-t border-border px-6 py-24">
                    <div className="mx-auto max-w-3xl text-center">
                        <h2 className="text-3xl font-bold md:text-4xl">
                            Expanda sua agência com o Hubinflu
                        </h2>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Acesse campanhas de grandes marcas e gerencie seu
                            portfólio com eficiência.
                        </p>
                        <Button
                            asChild
                            size="lg"
                            className="mt-8 rounded-full px-8"
                        >
                            <a href="/dashboard/login">
                                Criar Conta de Agência
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
