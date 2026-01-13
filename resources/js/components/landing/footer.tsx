export default function Footer() {
    return (
        <footer className="border-t border-border bg-card">
            <div className="mx-auto max-w-7xl px-6 py-12">
                <div className="grid gap-8 md:grid-cols-4">
                    <div className="space-y-4">
                        <img
                            src="/assets/hubinflu-logo.png"
                            alt="Hubinflu"
                            width={120}
                            height={35}
                            className="fi-logo h-7 w-auto"
                        />
                        <p className="text-sm text-muted-foreground">
                            O marketplace de marketing de influência do Brasil.
                        </p>
                    </div>
                    <div>
                        <h4 className="mb-4 text-sm font-semibold">
                            Plataforma
                        </h4>
                        <ul className="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="/para-empresas"
                                    className="hover:text-foreground"
                                >
                                    Para Empresas
                                </a>
                            </li>
                            <li>
                                <a
                                    href="/para-agencias"
                                    className="hover:text-foreground"
                                >
                                    Para Agências
                                </a>
                            </li>
                            <li>
                                <a
                                    href="/para-influenciadores"
                                    className="hover:text-foreground"
                                >
                                    Para Influenciadores
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 className="mb-4 text-sm font-semibold">Recursos</h4>
                        <ul className="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" className="hover:text-foreground">
                                    Central de Ajuda
                                </a>
                            </li>
                            <li>
                                <a href="#" className="hover:text-foreground">
                                    Blog
                                </a>
                            </li>
                            <li>
                                <a href="#" className="hover:text-foreground">
                                    Contato
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 className="mb-4 text-sm font-semibold">Legal</h4>
                        <ul className="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" className="hover:text-foreground">
                                    Termos de Uso
                                </a>
                            </li>
                            <li>
                                <a href="#" className="hover:text-foreground">
                                    Política de Privacidade
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div className="mt-12 border-t border-border pt-8 text-center text-sm text-muted-foreground">
                    © {new Date().getFullYear()} Hubinflu. Todos os direitos
                    reservados.
                </div>
            </div>
        </footer>
    );
}
