import Footer from '@/components/landing/footer';
import Header from '@/components/landing/header';

export default function LandingsLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="min-h-screen bg-white font-sans text-[#111827]">
            <Header />
            <main>{children}</main>
            <Footer />
        </div>
    );
}
