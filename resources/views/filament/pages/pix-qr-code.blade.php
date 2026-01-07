<x-filament::page>
    <div x-data="paymentListener({{ $payment }})" class=" max-w-5xl mx-auto fi-card" >
        <div x-show="!paid">
            <!-- QR Code Section -->
            <div class=" rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Escaneie um código QR para pagar</h2>
                
                <ol class="mb-6 space-y-2">
                    <li>1. Acesse seu Internet Banking ou app de pagamentos.</li>
                    <li>2. Escolha pagar via Pix.</li>
                    <li>3. Escaneie o seguinte código:</li>
                </ol>

                <div class="flex justify-center mb-4">
                    <img src="{{ $qrBase64 }}" alt="PIX QR Code" class="w-64 h-64">
                </div>

                <p class="text-sm  mb-4">⏱️ Pague e será creditado na hora.</p>

                <div class="border-l-4 border-secondary p-4">
                    <p class="text-sm text-primary">
                        ℹ️ Confirmaremos a validação da campanha quando o pagamento for aprovado.
                    </p>
                </div>
            </div>

            <!-- Copy/Paste Code Section -->
            <div class=" rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Ou copie este código para fazer o pagamento</h2>
                
                <p class="text-sm  mb-4">
                    Escolha pagar via Pix pelo seu Internet Banking ou app de pagamentos. Depois, cole o seguinte código:
                </p>

                <div >

                    <div class=" border rounded-lg p-3 mb-4 break-all text-sm font-mono">
                        {{  $payment->metadata['abacate_response']['brCode'] }}
                    </div>
                    
                    <div class="flex gap-4">
                        <button 
                        @click="navigator.clipboard.writeText('{{ $payment->metadata['abacate_response']['brCode']  }}')"
                        class="bg-primary text-primary-foreground hover:bg-blue-600  px-6 py-2 rounded-lg font-medium"
                        >
                        Copiar código
                    </button>
                </div>
            </div>
            </div>
        </div>

        <div x-show="paid" x-cloak class="bg-white rounded-lg shadow p-6 text-center">
            <h2 class="text-2xl font-bold text-green-600 mb-4">✅ Pagamento Confirmado!</h2>
            <p>Seu pagamento foi recebido com sucesso.</p>
        </div>
    </div>
</x-filament::page>

<script>
function paymentListener(payment) {
    const paymentId = payment.id

    return {
        paid: false,
        qrcodeBase64: '{{ $payment->qrcode_base64 }}',

        init() {
            // Subscribe to Reverb channel
            window.Echo.channel(`payment.${paymentId}`)
                .listen('.payment.received', (e) => {
                    console.log('Pagamento recebido!', e);
                    
                    if (e.status === 'PAID') {
                        this.paid = true;
                        
                        // Redirecionar ou fazer algo
                        setTimeout(() => {
                            window.location.href = '/payment/success';
                        }, 2000);
                    }
                });
        }
    }
}
</script>