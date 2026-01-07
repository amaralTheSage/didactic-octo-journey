<?php

namespace App\Http\Controllers;

use App\Events\PaymentReceived;
use App\Models\Payment;
use App\Services\AbacatePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $amount = 1;
        Gate::authorize('is_company');

        $validated = $request->validate([
            'campaign_id' => 'required|exists:App\Models\CampaignAnnouncement,id',
        ]);

        $response = (new AbacatePayService)->createPayment(
            amount: $amount,
            campaignId: $validated['campaign_id']
        );

        // Salvar o pagamento no banco
        $payment = Payment::create([
            'abacate_id' => $response['id'],
            'campaign_id' => $validated['campaign_id'],
            'user_id' => Auth::id(),
            'amount' => $amount * 100,
            'status' => 'PENDING',
            'qrcode_base64' => $response['brCodeBase64'],
            'qrcode_url' => $response['qrCodeUrl'] ?? null,
            'expires_at' => now()->addMinutes(15),
            'metadata' => [
                'abacate_response' => $response,
            ],
        ]);

        return to_route('filament.admin.pages.pix-qr-code', [
            'payment_id' => $payment->id,
            'qrcode_base64' => $response['brCodeBase64'],
        ]);
    }

    public function pix_webhook(Request $request)
    {
        Log::info('Webhook pix: Pagamento recebido, iniciando processamento...');

        Log::info($request);

        $abacateId = $request->input('data.pixQrCode.id');
        $status = $request->input('data.pixQrCode.status');
        $campaignId = $request->input('data.pixQrCode.metadata.campaign_id');

        PaymentReceived::dispatch($abacateId, $status, $campaignId);

        $webhookSecret = $request->query('webhookSecret');

        if (! $webhookSecret || $webhookSecret !== config('services.abacatepay.webhook_secret')) {
            Log::alert(
                'webhook pix: Webhook de pix FALHOU, segredo passado é diferente do servidor',
                ['invalid_secret' => $webhookSecret]
            );
            abort(401, 'Invalid webhook secret');
        }

        $isSignatureValid = AbacatePayService::IsRequestSignatureValid($request);

        Log::info($request);

        if (! $isSignatureValid) {
            Log::alert(
                'webhook pix: Webhook de pix FALHOU, assinatura da requisição é INVÁLIDA',
                ['chave pública do abacate' => config('services.services.abacate.public_key')]
            );
            abort(401, 'Invalid signature');
        }

        // try {
        //     $validated = $request->validate([
        //         'id' => 'required|string',
        //         'event' => 'required|string|in:billing.paid',

        //         'data' => 'required|array',
        //         'data.pixQrCode' => 'required|array',

        //         'data.pixQrCode.id' => 'required|string',
        //         'data.pixQrCode.amount' => 'required|integer|min:0',
        //         'data.pixQrCode.kind' => 'required|string|in:PIX',
        //         'data.pixQrCode.status' => ['required', 'string', Rule::enum(PaymentStatus::class)],

        //         'data.pixQrCode.metadata' => 'required|array',
        //         'data.pixQrCode.metadata.paymentIds' => 'required|array',
        //         'data.pixQrCode.metadata.paymentIds.*' => 'required|string|ulid',
        //     ]);
        // } catch (ValidationException $e) {
        //     Log::error('Webhook pix: Webhook falhou durante validação', [
        //         'errors' => $e->errors(),
        //         'request_data' => $request->all(),
        //         'url' => $request->fullUrl(),
        //         'ip' => $request->ip(),
        //     ]);

        //     throw $e;
        // }

        // try {
        //     $paymentIds = $validated['data']['pixQrCode']['metadata']['paymentIds'];
        //     $paymentStatus = $validated['data']['pixQrCode']['status'];
        //     Payment::whereIn('id', $paymentIds)
        //         ->update(['status' => $paymentStatus]);

        //     Log::info('Webhook pix: Status dos pagamentos atualizados com sucesso', [
        //         'ids' => $paymentIds,
        //         'status' => $paymentStatus
        //     ]);
        // } catch (Throwable $e) {
        //     Log::error('Webhook pix: Webhook falhou na atualização de status dos pagamentos', [
        //         'error' => $e->getMessage(),
        //         'validated data' => $validated,
        //     ]);
        // }

        Log::info('Webhook pix: Pagamento processado com sucesso!');

        return response()->json(['success' => true], 200);
    }
}
