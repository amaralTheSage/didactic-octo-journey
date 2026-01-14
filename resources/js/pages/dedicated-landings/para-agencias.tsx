import Advantages from '@/components/landing/audience-pages/Advantages';
import FeatureCarousel from '@/components/landing/audience-pages/FeatureCarousel';
import Hero from '@/components/landing/audience-pages/Hero';
import StatsAccordion from '@/components/landing/audience-pages/StatsAccordion';
import { Briefcase, DollarSign, Users, Zap } from 'lucide-react';
import LandingsLayout from './landings-layouts';

export default function ParaAgencias() {
    const advantages = [
        {
            icon: Users,
            title: 'Gestão Centralizada',
            description:
                'Gerencie dezenas de influenciadores em um único painel. Acabou a planilha de excel descentralizada.',
        },
        {
            icon: Zap,
            title: 'Propostas em Lote',
            description:
                'Selecione múltiplos talentos para uma única campanha e envie propostas completas em segundos.',
        },
        {
            icon: DollarSign,
            title: 'Comissionamento',
            description:
                "Defina seu 'cut' de agência de forma transparente e ajuste preços por influenciador para cada job.",
        },
        {
            icon: Briefcase,
            title: 'Portfólio Vivo',
            description:
                'Os perfis dos seus agenciados são atualizados automaticamente, servindo como uma vitrine constante.',
        },
    ];

    const slides = [
        {
            image: 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2340&auto=format&fit=crop',
            title: 'Casting Unificado',
            description:
                'Gerencie perfis, tabelas de preço e disponibilidade de todo o seu casting em um só lugar. Mantenha os dados sempre atualizados para as marcas.',
            tags: ['MULTI-PERFIL', 'TABELA DE PREÇO', 'MEDIA KIT'],
        },
        {
            image: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2340&auto=format&fit=crop',
            title: 'Negociação em Lote',
            description:
                'Recebeu um briefing? Selecione 5, 10 ou 50 influenciadores que dão match e envie uma proposta consolidada em segundos.',
            tags: ['BULK ACTIONS', 'AGILIDADE', 'PROPOSTA ÚNICA'],
        },
        {
            image: 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?q=80&w=2340&auto=format&fit=crop',
            title: 'Transparência Financeira',
            description:
                'Visualize exatamente quanto sua agência está faturando em comissões e quanto será repassado a cada influenciador.',
            tags: ['SPLIT DE PAGAMENTO', 'RELATÓRIOS', 'AUDITÁVEL'],
        },
    ];

    const statsItems = [
        {
            title: 'Gestão de Propostas',
            description:
                'Envie e acompanhe múltiplas propostas simultaneamente. Mantenha um histórico completo de todas as negociações em andamento e encerradas.',
            bullets: [
                'Versionamento de Propostas',
                'Status em Tempo Real',
                'Exportação PDF',
            ],
        },
        {
            title: 'Performance do Casting',
            description:
                'Compare o desempenho dos seus influenciadores lado a lado para identificar quem está entregando o melhor ROI para as marcas.',
            bullets: [
                'Rankings Internos',
                'Histórico de Engajamento',
                'Taxa de Aprovação',
            ],
        },
        {
            title: 'Financeiro Automatizado',
            description:
                'Simplifique o repasse de cachês. O sistema calcula automaticamente as comissões da agência e gera os valores líquidos para os influenciadores.',
            bullets: [
                'Split de Pagamento',
                'Notas Fiscais',
                'Extrato Unificado',
            ],
        },
    ];

    return (
        <LandingsLayout>
            <Hero
                role="AGENCY"
                headlineTop="GESTÃO DE"
                headlineBottom="PORTFÓLIO"
                subPillText="ESCALE"
                description="Centralize seus influenciadores. Envie propostas em lote, negocie comissões transparentes e gerencie múltiplos talentos em um só dashboard."
                stats={[
                    { value: '+50', label: 'Brands Ativas' },
                    { value: '0%', label: 'Taxa Setup' },
                    { value: 'Multi', label: 'Gestão de Cast' },
                ]}
                bgImage="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2301&auto=format&fit=crop"
                floatingCardTitle="FINANCEIRO"
                floatingCardSubtitle="Comissões de R$ 45k validadas este mês."
                ctaText="Cadastrar Agência"
            />
            <Advantages
                title="Potencialize sua Agência"
                subtitle="Recursos desenvolvidos para escalar sua operação e facilitar a gestão do seu casting."
                items={advantages}
            />
            <FeatureCarousel
                headingStart="Operação de escala"
                headingHighlight="simplificada."
                slides={slides}
            />

            <StatsAccordion
                eyebrow="AGENCY OS"
                headlineParts={['Gerencie.', 'Venda.', 'Cresça.']}
                description="O sistema operacional completo para agências de influência que buscam eficiência operacional e crescimento escalável."
                items={statsItems}
            />
        </LandingsLayout>
    );
}
