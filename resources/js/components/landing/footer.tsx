import { Mail, MapPin, Send, Twitter } from 'lucide-react';

export default function Footer() {
    return (
        <footer className="border-t border-gray-100 bg-white pt-20 pb-10">
            <div className="container mx-auto px-6 md:px-12">
                <div className="mb-20 grid grid-cols-1 gap-12 md:grid-cols-12">
                    {/* Brand Column */}
                    <div className="md:col-span-6">
                        <h3 className="mb-6">
                            <img
                                src="/assets/hubinflu-logo.png"
                                alt=""
                                className="fi-logo w-28"
                            />
                        </h3>
                        <p className="max-w-sm text-sm leading-relaxed text-gray-500">
                            A plataforma líder de análise e marketplace para o
                            setor de influência. Proporcionando transparência e
                            insights acionáveis para o mercado moderno de
                            criadores.
                        </p>
                    </div>

                    {/* Platform Column */}
                    <div className="md:col-span-3">
                        <h4 className="mb-6 text-sm font-medium text-black">
                            Plataforma
                        </h4>
                        <ul className="space-y-4">
                            <li>
                                <a
                                    href="#"
                                    className="text-sm text-gray-500 transition-colors hover:text-black"
                                >
                                    Marketplace
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    className="text-sm text-gray-500 transition-colors hover:text-black"
                                >
                                    Modelos de Preço
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    className="text-sm text-gray-500 transition-colors hover:text-black"
                                >
                                    Termos de Uso
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    className="text-sm text-gray-500 transition-colors hover:text-black"
                                >
                                    Segurança PIX
                                </a>
                            </li>
                        </ul>
                    </div>

                    {/* Support Column */}
                    <div className="md:col-span-3">
                        <h4 className="mb-6 text-sm font-medium text-black">
                            Suporte
                        </h4>
                        <ul className="space-y-4">
                            <li className="flex items-center gap-3 text-sm text-gray-500">
                                <MapPin size={16} className="text-gray-400" />
                                <span>Brasil, Remoto</span>
                            </li>
                            <li className="group flex items-center gap-3 text-sm text-gray-500">
                                <Mail
                                    size={16}
                                    className="text-gray-400 transition-colors group-hover:text-black"
                                />
                                <a
                                    href="mailto:help@hubinflu.io"
                                    className="transition-colors hover:text-black"
                                >
                                    help@hubinflu.io
                                </a>
                            </li>
                            <li className="group flex items-center gap-3 text-sm text-gray-500">
                                <Send
                                    size={16}
                                    className="text-gray-400 transition-colors group-hover:text-black"
                                />
                                <a
                                    href="#"
                                    className="transition-colors hover:text-black"
                                >
                                    @HubinfluBot
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Bottom Bar */}
                <div className="flex flex-col items-center justify-between gap-4 border-t border-gray-100 pt-8 md:flex-row">
                    <p className="text-xs text-gray-400">
                        © {new Date().getFullYear()} Hubinflu. Todos os
                        direitos reservados.
                    </p>

                    <div className="flex items-center gap-6">
                        <a
                            href="#"
                            className="text-gray-400 transition-colors hover:text-black"
                        >
                            <Twitter size={18} />
                        </a>
                        <a
                            href="#"
                            className="text-gray-400 transition-colors hover:text-black"
                        >
                            <Send size={18} />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
