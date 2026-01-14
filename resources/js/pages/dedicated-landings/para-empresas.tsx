import Advantages from '@/components/landing/audience-pages/Advantages';
import FadeInEffect from '@/components/landing/audience-pages/FadeInEffect';
import FeatureCarousel from '@/components/landing/audience-pages/FeatureCarousel';
import Hero from '@/components/landing/audience-pages/Hero';
import StatsAccordion from '@/components/landing/audience-pages/StatsAccordion';
import {
    FileSearch,
    MessageSquare,
    ShieldCheck,
    TrendingUp,
} from 'lucide-react';
import LandingsLayout from './landings-layouts';

export default function ParaEmpresas() {
    const advantages = [
        {
            icon: FileSearch,
            title: 'Auditoria Completa',
            description:
                'Rastreie cada alteração nas propostas com logs detalhados. Saiba exatamente o que mudou e quando.',
        },
        {
            icon: ShieldCheck,
            title: 'Pagamento via PIX',
            description:
                'Valide campanhas instantaneamente, garantindo prioridade e confiança no marketplace.',
        },
        {
            icon: MessageSquare,
            title: 'Negociação Direta',
            description:
                'Chat integrado entre empresa, agência e influenciador para alinhar expectativas sem ruídos de comunicação.',
        },
        {
            icon: TrendingUp,
            title: 'Foco em ROI',
            description:
                'Métricas claras e definição precisa de entregáveis (reels, stories) para maximizar seu retorno.',
        },
    ];

    const slides = [
        {
            image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2340&auto=format&fit=crop',
            title: 'Controle Total do Funil',
            description:
                'Acompanhe suas campanhas desde o rascunho até a entrega. Nossa dashboard oferece visão panorâmica do status de todas as propostas em andamento, permitindo decisões rápidas.',
            tags: ['DASHBOARD LIVE', 'METRIFICADO', 'INTUITIVO'],
        },
        {
            image: 'https://images.unsplash.com/photo-1563986768609-322da13575f3?q=80&w=1470&auto=format&fit=crop',
            title: 'Pagamentos Seguros',
            description:
                'Elimine a burocracia financeira. Com a integração PIX nativa, você valida orçamentos instantaneamente, garantindo a reserva dos influenciadores e agilizando o início dos trabalhos.',
            tags: ['PIX INSTANTÂNEO', 'NOTA FISCAL', 'AUTOMATIZADO'],
        },
        {
            image: 'https://images.unsplash.com/photo-1556155092-490a1ba16284?q=80&w=1470&auto=format&fit=crop',
            title: 'Marketplace de Talentos',
            description:
                'Filtre influenciadores por nicho, engajamento e localização. Nossa busca avançada conecta sua marca aos criadores que realmente conversam com seu público-alvo.',
            tags: ['BUSCA AVANÇADA', 'FILTROS REAIS', 'AUDITADO'],
        },
    ];
    const statsItems = [
        {
            title: 'Análise de Mercado',
            description:
                'Dashboards abrangentes cobrindo alcance, engajamento e conversão. Analisamos volume e tendências históricas para te dar a vantagem competitiva.',
            bullets: [
                'KPIs em Tempo Real',
                'Análise de Sentimento',
                'Relatórios Diários',
            ],
        },
        {
            title: 'Previsão de Custos',
            description:
                'Algoritmos inteligentes que estimam o custo ideal por engajamento baseado no histórico de milhares de campanhas no marketplace.',
            bullets: [
                'Benchmarking de Preços',
                'Estimativa de ROI',
                'Alocação de Budget',
            ],
        },
        {
            title: 'Auditoria de Entrega',
            description:
                'Sistema automatizado que verifica se os stories e reels contratados foram postados e mantidos no ar pelo tempo acordado.',
            bullets: [
                'Verificação Automática',
                'Alertas de Discrepância',
                'Backup de Conteúdo',
            ],
        },
    ];

    return (
        <LandingsLayout>
            <Hero
                role="COMPANY"
                headlineTop="Campanhas"
                headlineBottom="Eficientes"
                description="Lance campanhas para o público brasileiro, receba propostas de agências de elite e valide pagamentos via PIX para prioridade máxima no marketplace."
                stats={[
                    { value: '100%', label: 'Segurança PIX' },
                    { value: '+500', label: 'Influenciadores' },
                    { value: '24h', label: 'Aprovação Média' },
                ]}
                bgImage="https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2426&auto=format&fit=crop"
                floatingCardTitle="ANÁLISE DE DADOS"
                floatingCardSubtitle="Alcance previsto de +2.4M no Q3 baseado em propostas."
                ctaText="Anunciar Campanha"
            />

            <FadeInEffect>
                <Advantages
                    title="A Vantagem Corporativa"
                    subtitle="Ferramentas construídas para garantir segurança, transparência e eficiência em cada campanha."
                    items={advantages}
                />
            </FadeInEffect>

            <FadeInEffect>
                <FeatureCarousel
                    headingStart="Gestão moderna encontra"
                    headingHighlight="dados reais."
                    slides={slides}
                />{' '}
            </FadeInEffect>

            <FadeInEffect>
                <StatsAccordion
                    eyebrow="ANALYTICS CORE"
                    headlineParts={['Metrifique.', 'Otimize.', 'Escale.']}
                    description="Infraestrutura de dados que adapta suas campanhas em tempo real sem comprometer o budget ou a qualidade da entrega."
                    items={statsItems}
                />
            </FadeInEffect>
        </LandingsLayout>
    );
}
