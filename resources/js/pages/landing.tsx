import AudienceGrid from '@/components/landing/audience-grid';
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
        </LandingsLayout>
    );
}
