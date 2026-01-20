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
        <header
            className={`sticky top-0 right-0 left-0 z-50 bg-white/90 shadow-md md:relative ${
                scrolled || mobileMenuOpen
                    ? 'py-6 shadow-sm backdrop-blur-md'
                    : 'bg-transparent py-6'
            }`}
        >
            <nav className="container mx-auto flex items-center justify-between px-6 md:px-12">
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
                    <Link
                        href="/para-curadorias"
                        className="transition-colors hover:text-black"
                    >
                        Curadorias
                    </Link>
                </div>

                {/* CTA */}
                <div className="hidden items-center gap-4 md:flex">
                    <a
                        href={'/dashboard/login'}
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
            </nav>

            {/* Mobile Menu */}
            {mobileMenuOpen && (
                <nav className="absolute top-full right-0 left-0 flex flex-col gap-4 border-b border-gray-100 bg-white p-6 px-8 text-right leading-relaxed font-medium shadow-xl md:hidden md:px-12">
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
                    <Link
                        href="/para-curadorias"
                        className="transition-colors hover:text-black"
                    >
                        Curadorias
                    </Link>

                    <a
                        href={'/dashboard/login'}
                        className="mx-auto mt-8 flex w-2/3 items-center justify-center gap-2 rounded-full bg-black px-6 py-3 font-medium text-primary-foreground transition-colors hover:bg-primary"
                    >
                        Log In
                        <ArrowRight size={16} />
                    </a>
                </nav>
            )}
        </header>
    );
}
