import Footer from '@/components/landing/footer';
import Header from '@/components/landing/header';
import { Button } from '@/components/ui/button';
import {
    ArrowRight,
    BarChart3,
    CreditCard,
    Megaphone,
    MessageSquare,
    ShieldCheck,
    Users,
} from 'lucide-react';

export default function ParaEmpresas() {
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
                                    Para Empresas
                                </p>
                                <h1 className="mt-4 text-4xl font-bold tracking-tight text-balance md:text-5xl lg:text-6xl">
                                    Anuncie para milhares de influenciadores em
                                    um só lugar
                                </h1>
                                <p className="mt-6 text-lg text-muted-foreground">
                                    Crie campanhas, receba propostas de agências
                                    qualificadas e gerencie todo o ciclo de
                                    marketing de influência com transparência e
                                    eficiência. Valide pagamentos via PIX e
                                    destaque suas campanhas no marketplace.
                                </p>
                                <div className="mt-10 flex flex-col gap-4 sm:flex-row">
                                    <Button
                                        asChild
                                        size="lg"
                                        className="rounded-full px-8"
                                    >
                                        <a href="/dashboard/login">
                                            Criar Minha Campanha
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
                                        <Megaphone className="h-32 w-32 text-secondary/50" />
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
                                Por que usar o Hubinflu?
                            </h2>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Megaphone className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Campanhas Segmentadas
                                </h3>
                                <p className="text-muted-foreground">
                                    Defina público-alvo, localização, nicho de
                                    conteúdo e orçamento. Alcance exatamente
                                    quem você precisa.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <Users className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Propostas Qualificadas
                                </h3>
                                <p className="text-muted-foreground">
                                    Receba propostas detalhadas de agências com
                                    os melhores influenciadores para sua
                                    campanha.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <MessageSquare className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Chat em Tempo Real
                                </h3>
                                <p className="text-muted-foreground">
                                    Comunique-se diretamente com agências e
                                    influenciadores. Negocie termos e alinhe
                                    expectativas.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <ShieldCheck className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Validação via PIX
                                </h3>
                                <p className="text-muted-foreground">
                                    Verifique suas campanhas com pagamento PIX e
                                    ganhe destaque no marketplace com selo de
                                    verificação.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <BarChart3 className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Histórico Completo
                                </h3>
                                <p className="text-muted-foreground">
                                    Acompanhe todas as mudanças nas propostas
                                    com logs de auditoria detalhados.
                                    Transparência total.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-border bg-card p-8">
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                                    <CreditCard className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-semibold">
                                    Controle de Orçamento
                                </h3>
                                <p className="text-muted-foreground">
                                    Defina seu orçamento e comissão da agência.
                                    Receba propostas dentro do seu limite.
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
                                Do anúncio à execução em 4 passos
                            </h2>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    1
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Crie sua Campanha
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Defina produto, orçamento, deliverables e
                                    público-alvo.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    2
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Receba Propostas
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Agências enviam propostas com
                                    influenciadores selecionados.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    3
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Negocie e Aprove
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Converse via chat e aprove a proposta ideal.
                                </p>
                            </div>

                            <div className="text-center">
                                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-secondary-foreground">
                                    4
                                </div>
                                <h3 className="mb-2 font-semibold">
                                    Execute a Campanha
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    Acompanhe a execução e valide via PIX.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="border-t border-border px-6 py-24">
                    <div className="mx-auto max-w-3xl text-center">
                        <h2 className="text-3xl font-bold md:text-4xl">
                            Comece a encontrar os influenciadores perfeitos
                        </h2>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Crie sua conta e publique sua primeira campanha em
                            minutos.
                        </p>
                        <Button
                            asChild
                            size="lg"
                            className="mt-8 rounded-full px-8"
                        >
                            <a href="/dashboard/login">
                                Criar Conta de Empresa
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
