import AudienceGrid from '@/components/landing/audience-grid';
import CTASection from '@/components/landing/audience-pages/CTASection';
import Hero from '@/components/landing/hero';
import Testimonials from '@/components/landing/testimonials';
import '../../css/app.css';
import LandingsLayout from './dedicated-landings/landings-layouts';

export default function Landing() {
    return (
        <LandingsLayout>
            <Hero />
            <AudienceGrid />
            <Testimonials />
            <CTASection />
        </LandingsLayout>
    );
}
