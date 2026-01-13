<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Events\PaymentReceived;
use App\Models\CampaignAnnouncement;
use App\Models\Payment;
use App\Services\AbacatePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class PaymentController extends Controller
{
    public function page(Request $request)
    {
        Gate::authorize('is_company');

        return Inertia::render('payment-page', [
            'payment' => $request['payment'],
            'qrcode' => $request['qrcode'],
            'brcode' => $request['brcode'],
            'db_status' => Payment::whereId($request['payment'])->first()->status,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Iniciando criação de pagamento via AbacatePay');

        $amount = 1;
        Gate::authorize('is_company');

        $validated = $request->validate([
            'campaign_id' => 'required|exists:App\Models\CampaignAnnouncement,id',
        ]);

        $response = (new AbacatePayService)->createPayment(
            amount: $amount,
            campaignId: $validated['campaign_id']
        );

        $payment = Payment::create([
            'abacate_id' => $response['id'],
            'campaign_id' => $validated['campaign_id'],
            'user_id' => Auth::id(),
            'amount' => $amount * 100,
            'status' => PaymentStatus::PENDING,
            'expires_at' => now()->addMinutes(15),
            'metadata' => [
                'abacate_response' => $response,
            ],
        ]);

        return to_route('payments.page', ['payment' => $payment->id, 'qrcode' => $response['brCodeBase64'], 'brcode' => $response['brCode']]);
    }

    public function pixwebhook(Request $request)
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
                ['chave pública do abacate' => config('services.abacatepay.public_key')]
            );
            abort(401, 'Invalid signature');
        }

        try {
            $validated = $request->validate([
                'id' => 'required|string',
                'event' => 'required|string|in:billing.paid',

                'data' => 'required|array',
                'data.pixQrCode' => 'required|array',

                'data.pixQrCode.id' => 'required|string',
                'data.pixQrCode.amount' => 'required|integer|min:0',
                'data.pixQrCode.kind' => 'required|string|in:PIX',
                'data.pixQrCode.status' => ['required', 'string', Rule::enum(PaymentStatus::class)],

                'data.pixQrCode.metadata' => 'required|array',
                'data.pixQrCode.metadata.campaign_id' => 'required|integer|exists:campaign_announcements,id',
            ]);
        } catch (ValidationException $e) {
            Log::error('Webhook pix: Webhook falhou durante validação', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            throw $e;
        }

        try {
            $campaignId = $validated['data']['pixQrCode']['metadata']['campaign_id'];
            $paymentStatus = $validated['data']['pixQrCode']['status'];
            Payment::where('campaign_id', $campaignId)->where('abacate_id', $abacateId)
                ->update(['status' => $paymentStatus]);

            if ($paymentStatus === PaymentStatus::PAID) {
                Log::info('Webhook pix: Disparando evento PaymentReceived', [
                    'abacate_id' => $abacateId,
                    'status' => $paymentStatus,
                    'campaign_id' => $campaignId,
                ]);

                Payment::where('campaign_id', $campaignId)->where('abacate_id', $abacateId)
                    ->update(['paid_at' => now()]);
            }

            Log::info('Webhook pix: Status do pagamento atualizados com sucesso', [
                'campanha' => CampaignAnnouncement::whereId($campaignId)->first(),
                'status' => $paymentStatus,
            ]);
        } catch (Throwable $e) {
            Log::error('Webhook pix: Webhook falhou na atualização de status dos pagamentos', [
                'error' => $e->getMessage(),
                'validated data' => $validated,
            ]);

            throw $e;
        }

        Log::info('Webhook pix: Pagamento processado com sucesso!');

        return response()->json(['success' => true], 200);
    }
}
