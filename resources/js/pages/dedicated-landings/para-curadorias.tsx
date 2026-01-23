import Advantages from '@/components/landing/audience-pages/Advantages';
import CTASection from '@/components/landing/audience-pages/CTASection';
import FadeInEffect from '@/components/landing/audience-pages/FadeInEffect';
import FeatureCarousel from '@/components/landing/audience-pages/FeatureCarousel';
import Hero from '@/components/landing/audience-pages/Hero';
import StatsAccordion from '@/components/landing/audience-pages/StatsAccordion';
import { Shield, Sparkles, TrendingUp, UserCheck } from 'lucide-react';
import LandingsLayout from './landings-layouts';

export default function ParaCuradorias() {
    const advantages = [
        {
            icon: UserCheck,
            title: 'Gestão White Label',
            description:
                'Opere campanhas em nome das empresas que você atende, mantendo total controle e transparência.',
        },
        {
            icon: Shield,
            title: 'Acesso Multi-Cliente',
            description:
                'Gerencie múltiplas empresas em um único login. Alterne entre clientes com um clique.',
        },
        {
            icon: Sparkles,
            title: 'Curadoria Estratégica',
            description:
                'Selecione os melhores influenciadores para cada marca com base em dados reais de performance.',
        },
        {
            icon: TrendingUp,
            title: 'Relatórios Completos',
            description:
                'Apresente resultados detalhados para seus clientes com dashboards automatizados e exportáveis.',
        },
    ];

    const slides = [
        {
            image: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?q=80&w=2340&auto=format&fit=crop',
            title: 'Multi-Empresa',
            description:
                'Gerencie todas as empresas que você atende em um único painel. Crie campanhas, envie propostas e acompanhe resultados sem trocar de plataforma.',
            tags: ['MULTI-CLIENTE', 'GESTÃO UNIFICADA', 'EFICIÊNCIA'],
        },
        {
            image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2415&auto=format&fit=crop',
            title: 'Curadoria Inteligente',
            description:
                'Acesse uma base completa de influenciadores e selecione os perfis ideais para cada briefing. Filtre por nicho, engajamento e histórico.',
            tags: ['DATABASE', 'FILTROS AVANÇADOS', 'MATCH PERFEITO'],
        },
        {
            image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2340&auto=format&fit=crop',
            title: 'Prestação de Contas',
            description:
                'Gere relatórios automáticos para seus clientes com métricas de performance, ROI e status de cada campanha em tempo real.',
            tags: ['DASHBOARDS', 'MÉTRICAS', 'TRANSPARÊNCIA'],
        },
    ];

    const statsItems = [
        {
            title: 'Operação Consultiva',
            description:
                'Atue como braço estratégico das empresas. Crie campanhas, negocie com influenciadores e aprove entregas mantendo a autonomia total.',
            bullets: [
                'Criação de Campanhas',
                'Negociação de Propostas',
                'Aprovação de Conteúdo',
            ],
        },
        {
            title: 'Visibilidade Total',
            description:
                'Acompanhe todas as métricas relevantes de cada cliente em painéis dedicados. Compare performance entre campanhas e otimize estratégias.',
            bullets: [
                'Dashboard por Cliente',
                'Comparativos de Performance',
                'Histórico Completo',
            ],
        },
        {
            title: 'Escalabilidade',
            description:
                'Gerencie 5, 10 ou 50 empresas simultaneamente sem perder eficiência. Processos padronizados que crescem junto com sua operação.',
            bullets: [
                'Templates de Campanha',
                'Workflows Reutilizáveis',
                'Automações Inteligentes',
            ],
        },
    ];

    const ctaData = {
        title: 'Pronto para operar campanhas em escala?',
        description:
            'Gerencie múltiplos clientes, execute curadorias estratégicas e entregue relatórios profissionais com total autonomia e transparência.',
        ctaText: 'Se cadastre aqui',
        ctaHref: '/dashboard/register',
        // backgroundColor: 'bg-cyan-200',
    };

    return (
        <LandingsLayout>
            <Hero
                role="Curadorias"
                headlineTop="GESTÃO ESTRATÉGICA"
                headlineBottom="MULTI-CLIENTE"
                subPillText="OPERE"
                description="Gerencie campanhas de influência para múltiplas empresas. Curadoria profissional, processos escaláveis e relatórios que impressionam."
                stats={[
                    { value: 'Multi', label: 'Empresas' },
                    { value: '100%', label: 'White Label' },
                    { value: 'Full', label: 'Autonomia' },
                ]}
                bgImage="https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=2340&auto=format&fit=crop"
                floatingCardTitle="RESULTADOS"
                floatingCardSubtitle="15 campanhas ativas gerenciadas este mês."
                ctaText="Se cadastre aqui"
                variant={2}
            />
            <FadeInEffect>
                <Advantages
                    title="Opere como Profissional"
                    subtitle="Ferramentas desenvolvidas para curadores que gerenciam estratégias de influência para múltiplos clientes."
                    items={advantages}
                />
            </FadeInEffect>

            <FadeInEffect>
                <FeatureCarousel
                    headingStart="Consultoria de influência"
                    headingHighlight="profissionalizada."
                    slides={slides}
                />
            </FadeInEffect>

            <FadeInEffect>
                <StatsAccordion
                    eyebrow="CURATOR OS"
                    headlineParts={['Curate.', 'Gerencie.', 'Entregue.']}
                    description="A plataforma completa para profissionais que gerenciam estratégias de marketing de influência para empresas."
                    items={statsItems}
                    variant={2}
                />
            </FadeInEffect>

            <FadeInEffect>
                <CTASection {...ctaData} />
            </FadeInEffect>
        </LandingsLayout>
    );
}
