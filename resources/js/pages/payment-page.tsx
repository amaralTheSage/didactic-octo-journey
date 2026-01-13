import { Head } from '@inertiajs/react';
import { useEcho } from '@laravel/echo-react';
import { CheckCircle2, ChevronLeft, Copy } from 'lucide-react';
import { useState } from 'react';
import QRCode from 'react-qr-code';

export default function PaymentPage({ payment, brcode, db_status }) {
    const [paid, setPaid] = useState(db_status === 'PAID');
    const [copied, setCopied] = useState(false);

    useEcho(
        'payments',
        'PaymentReceived',
        ({ abacateId, status, campaignId }) => {
            setPaid(true);
            console.log(abacateId, status, campaignId);
        },
    );

    console.log(db_status);

    const handleCopyCode = async () => {
        try {
            await navigator.clipboard.writeText(brcode);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
    };

    return (
        <>
            <Head title="Pagamento PIX" />

            <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-12">
                <div className="mx-auto max-w-5xl">
                    <a
                        href="/dashboard/campaign-announcements"
                        className="inline-flex items-center gap-2 text-lg font-semibold text-blue-600 transition-colors hover:text-blue-700"
                    >
                        <ChevronLeft className="h-6 w-6" />
                        <span>Voltar</span>
                    </a>

                    {!paid ? (
                        <div className="space-y-6">
                            {/* QR Code Section */}
                            <div className="transform rounded-2xl border border-slate-200 bg-white p-8 shadow-lg transition-all duration-300 hover:shadow-xl">
                                <h2 className="mb-6 flex items-center gap-3 text-2xl font-bold text-slate-800">
                                    <span className="h-8 w-2 rounded-full bg-blue-600"></span>
                                    Escaneie o código QR para pagar
                                </h2>

                                <ol className="mb-8 space-y-3 text-slate-600">
                                    <li className="flex items-start gap-3">
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                            1
                                        </span>
                                        <span>
                                            Acesse seu Internet Banking ou app
                                            de pagamentos.
                                        </span>
                                    </li>
                                    <li className="flex items-start gap-3">
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                            2
                                        </span>
                                        <span>Escolha pagar via Pix.</span>
                                    </li>
                                    <li className="flex items-start gap-3">
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                            3
                                        </span>
                                        <span>Escaneie o seguinte código:</span>
                                    </li>
                                </ol>

                                <div className="mb-6 flex justify-center">
                                    <div className="rounded-2xl border-2 border-slate-200 bg-white p-6 shadow-md">
                                        <QRCode value={brcode} />
                                    </div>
                                </div>

                                <div className="mb-4 rounded-lg border-l-4 border-secondary bg-gradient-to-r from-green-50 to-emerald-50 p-4">
                                    <p className="flex items-center gap-2 text-sm text-green-800">
                                        <span className="text-lg">⏱️</span>
                                        <span className="font-medium">
                                            Pague e será creditado na hora.
                                        </span>
                                    </p>
                                </div>

                                <div className="rounded-lg border-l-4 border-secondary bg-blue-50 p-4">
                                    <p className="flex items-start gap-2 text-sm text-blue-800">
                                        <span className="shrink-0 text-lg">
                                            ℹ️
                                        </span>
                                        <span>
                                            Confirmaremos a validação da
                                            campanha quando o pagamento for
                                            aprovado.
                                        </span>
                                    </p>
                                </div>
                            </div>

                            {/* Copy/Paste Code Section */}
                            <div className="transform rounded-2xl border border-slate-200 bg-white p-8 shadow-lg transition-all duration-300 hover:shadow-xl">
                                <h2 className="mb-6 flex items-center gap-3 text-2xl font-bold text-slate-800">
                                    <span className="h-8 w-2 rounded-full bg-secondary"></span>
                                    Ou copie este código para fazer o pagamento
                                </h2>

                                <p className="mb-6 text-sm text-slate-600">
                                    Escolha pagar via Pix pelo seu Internet
                                    Banking ou app de pagamentos. Depois, cole o
                                    seguinte código:
                                </p>

                                <div className="space-y-4">
                                    <div className="max-h-32 overflow-y-auto rounded-xl border-2 border-slate-200 bg-slate-50 p-4 font-mono text-sm break-all text-slate-700">
                                        {brcode}
                                    </div>

                                    <button
                                        onClick={handleCopyCode}
                                        className="flex w-full transform items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-semibold text-white shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-blue-800 hover:shadow-lg"
                                    >
                                        <Copy className="h-5 w-5" />
                                        {copied
                                            ? 'Código Copiado!'
                                            : 'Copiar código'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="transform animate-in rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-2xl transition-all duration-500 fade-in zoom-in">
                            <div className="mb-6 flex justify-center">
                                <div className="flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                                    <CheckCircle2 className="h-12 w-12 text-green-600" />
                                </div>
                            </div>

                            <h2 className="mb-4 text-3xl font-bold text-green-600">
                                Pagamento Confirmado!
                            </h2>

                            <p className="mb-8 text-lg text-slate-600">
                                Seu pagamento foi recebido com sucesso.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
