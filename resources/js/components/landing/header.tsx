import { Link } from '@inertiajs/react';
import { ArrowRight, Menu, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function Header() {
    const [scrolled, setScrolled] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setScrolled(window.scrollY > 50);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <nav className={`bg-transparent pt-6 transition-all duration-300`}>
            <div className="container mx-auto flex items-center justify-between px-6 md:px-12">
                {/* Logo */}
                <Link href={`/`} className="flex items-center gap-2">
                    <img
                        src="/assets/hubinflu-logo.png"
                        alt=""
                        className="fi-logo w-28"
                    />
                </Link>

                {/* Desktop Links */}
                <div className="hidden items-center gap-8 text-sm leading-relaxed font-medium text-gray-600 md:flex">
                    <Link
                        href="/para-empresas"
                        className="transition-colors hover:text-black"
                    >
                        Empresas
                    </Link>
                    <Link
                        href="/para-agencias"
                        className="transition-colors hover:text-black"
                    >
                        Agências
                    </Link>
                    <Link
                        href="/para-influenciadores"
                        className="transition-colors hover:text-black"
                    >
                        Influenciadores
                    </Link>
                </div>

                {/* CTA */}
                <div className="hidden items-center gap-4 md:flex">
                    <a
                        href={'/login'}
                        className="flex items-center gap-2 rounded-full bg-black px-6 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary"
                    >
                        Log In
                        <ArrowRight size={16} />
                    </a>
                </div>

                {/* Mobile Toggle */}
                <button
                    className="p-2 text-gray-800 md:hidden"
                    onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                >
                    {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                </button>
            </div>

            {/* Mobile Menu */}
            {mobileMenuOpen && (
                <div className="absolute top-full right-0 left-0 flex flex-col gap-4 border-b border-gray-100 bg-white p-6 shadow-xl md:hidden">
                    <a
                        href="#companies"
                        className="py-2 text-lg font-medium text-gray-800"
                    >
                        Empresas
                    </a>
                    <a
                        href="#agencies"
                        className="py-2 text-lg font-medium text-gray-800"
                    >
                        Agências
                    </a>
                    <a
                        href="#influencers"
                        className="py-2 text-lg font-medium text-gray-800"
                    >
                        Influenciadores
                    </a>
                    <hr className="border-gray-100" />
                    <button className="bg-primary-600 mt-2 w-full rounded-xl py-3 font-medium text-white">
                        Começar Agora
                    </button>
                </div>
            )}
        </nav>
    );
}
