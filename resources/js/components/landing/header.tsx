import { Button } from '@/components/ui/button';
import AppearanceToggleDropdown from '../appearance-dropdown';

export default function Header() {
    return (
        <header className="fixed top-0 right-0 left-0 z-50 border-b border-border/40 bg-background/80 backdrop-blur-md">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <a href="/" className="flex items-center gap-2">
                    <img
                        src="/assets/hubinflu-logo.png"
                        alt="Hubinflu"
                        width={140}
                        height={40}
                        className="fi-logo h-8 w-auto"
                    />
                </a>
                <nav className="hidden items-center gap-8 md:flex">
                    <a
                        href="/para-empresas"
                        className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Para Empresas
                    </a>
                    <a
                        href="/para-agencias"
                        className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Para Agências
                    </a>
                    <a
                        href="/para-influenciadores"
                        className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Para Influenciadores
                    </a>
                </nav>
                <div className="flex items-center gap-2">
                    <AppearanceToggleDropdown />
                    <Button
                        asChild
                        variant="outline"
                        className="rounded-full bg-transparent"
                    >
                        <a href="/dashboard/login">Entrar</a>
                    </Button>
                </div>
            </div>
        </header>
    );
}
