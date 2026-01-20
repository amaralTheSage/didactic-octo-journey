import AudienceGrid from '@/components/landing/audience-grid';
import CTASection from '@/components/landing/audience-pages/CTASection';
import FadeInEffect from '@/components/landing/audience-pages/FadeInEffect';
import Hero from '@/components/landing/hero';
import Testimonials from '@/components/landing/testimonials';
import '../../css/app.css';
import LandingsLayout from './dedicated-landings/landings-layouts';

const ctaData = {
    title: 'Pronto para começar?',
    description:
        'Junte-se ao marketplace mais completo do Brasil. Conecte-se, negocie e escale seus resultados com segurança e transparência.',
    ctaText: 'Criar Conta Grátis',
    ctaHref: '/dashboard/register',
};

export default function Landing() {
    return (
        <LandingsLayout>
            <Hero />
            <FadeInEffect>
                <AudienceGrid />
            </FadeInEffect>

            <FadeInEffect>
                <Testimonials />
            </FadeInEffect>

            <FadeInEffect>
                <CTASection {...ctaData} />
            </FadeInEffect>
        </LandingsLayout>
    );
}
