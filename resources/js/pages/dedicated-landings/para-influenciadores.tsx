import Advantages from '@/components/landing/audience-pages/Advantages';
import FadeInEffect from '@/components/landing/audience-pages/FadeInEffect';
import FeatureCarousel from '@/components/landing/audience-pages/FeatureCarousel';
import Hero from '@/components/landing/audience-pages/Hero';
import StatsAccordion from '@/components/landing/audience-pages/StatsAccordion';
import { Bell, Camera, CreditCard, Star } from 'lucide-react';
import LandingsLayout from './landings-layouts';

export default function ParaInfluenciadores() {
    const advantages = [
        {
            icon: Camera,
            title: 'Foco na Criação',
            description:
                'Deixe a parte chata com a agência. Receba briefings claros e foque apenas em produzir conteúdo de qualidade.',
        },
        {
            icon: Star,
            title: 'Grandes Marcas',
            description:
                'Acesse campanhas de empresas verificadas que buscam profissionalismo e estão prontas para investir.',
        },
        {
            icon: CreditCard,
            title: 'Tabela Dinâmica',
            description:
                'Sua agência pode negociar valores específicos para cada projeto, garantindo que seu trabalho seja valorizado.',
        },
        {
            icon: Bell,
            title: 'Notificações Reais',
            description:
                'Saiba na hora quando uma marca se interessa pelo seu perfil ou quando uma proposta é aprovada.',
        },
    ];

    const slides = [
        {
            image: 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=1974&auto=format&fit=crop',
            title: 'Jobs na Palma da Mão',
            description:
                'Receba notificações de novas oportunidades diretamente no app. Aprove, negocie detalhes ou decline com um toque.',
            tags: ['MOBILE FIRST', 'PUSH NOTIFICATIONS', 'RÁPIDO'],
        },
        {
            image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2340&auto=format&fit=crop',
            title: 'Chat Integrado',
            description:
                'Tire dúvidas diretamente com a marca ou com sua agência através do nosso chat seguro e auditável.',
            tags: ['COMUNICAÇÃO', 'REGISTRO', 'DIRETO'],
        },
        {
            image: 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=2311&auto=format&fit=crop',
            title: 'Carteira Digital',
            description:
                'Acompanhe seus ganhos em tempo real. Saiba exatamente quanto vai receber por cada job e quando o pagamento será liberado.',
            tags: ['EXTRATO', 'TRANSPARÊNCIA', 'PREVISIBILIDADE'],
        },
    ];
    const statsItems = [
        {
            title: 'Media Kit Vivo',
            description:
                'Seu perfil é atualizado automaticamente com suas métricas mais recentes do Instagram e TikTok. Nunca mais envie um PDF desatualizado.',
            bullets: [
                'Sincronização via API',
                'Link Compartilhável',
                'Design Premium',
            ],
        },
        {
            title: 'Negociação Segura',
            description:
                'Todas as conversas e acordos ficam registrados na plataforma. Contratos digitais protegem seu trabalho e garantem o recebimento.',
            bullets: ['Chat Auditável', 'Contrato Digital', 'Suporte Jurídico'],
        },
        {
            title: 'Previsibilidade',
            description:
                'Visualize seu fluxo de caixa futuro. Saiba exatamente quanto e quando você vai receber pelos jobs realizados.',
            bullets: [
                'Calendário de Recebimentos',
                'Notificações de Pagamento',
                'Histórico Financeiro',
            ],
        },
    ];

    return (
        <LandingsLayout>
            <Hero
                role="Influenciadores"
                headlineTop="SUA VOZ"
                headlineBottom="VALORIZADA"
                subPillText="MONETIZE"
                description="Receba propostas que fazem sentido. Deixe sua agência negociar os detalhes burocráticos enquanto você foca no que faz de melhor: criar."
                stats={[
                    { value: 'Top', label: 'Marcas Nacionais' },
                    { value: 'R$', label: 'Tabela Dinâmica' },
                    { value: 'Chat', label: 'Direto c/ Marca' },
                ]}
                bgImage="https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2370&auto=format&fit=crop"
                floatingCardTitle="PERFIL"
                floatingCardSubtitle="Seu media kit atualizado automaticamente."
                ctaText="Sou Criador"
            />
            <FadeInEffect>
                <Advantages
                    title="Carreira em Ascensão"
                    subtitle="Tudo o que você precisa para profissionalizar sua jornada e fechar parcerias duradouras."
                    items={advantages}
                />
            </FadeInEffect>

            <FadeInEffect>
                <FeatureCarousel
                    headingStart="Sua criatividade"
                    headingHighlight="profissionalizada."
                    slides={slides}
                />{' '}
            </FadeInEffect>

            <FadeInEffect>
                <StatsAccordion
                    eyebrow="CREATOR STUDIO"
                    headlineParts={['Crie.', 'Engaje.', 'Fature.']}
                    description="Ferramentas profissionais para quem leva a criação de conteúdo a sério e busca estabilidade financeira."
                    items={statsItems}
                />
            </FadeInEffect>
        </LandingsLayout>
    );
}
