<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AbacatePayService
{
    private PendingRequest $abacateClient;

    public function __construct()
    {
        $this->abacateClient = Http::baseUrl('https://api.abacatepay.com/v1')
            ->withToken(config('services.abacatepay.token'))
            ->timeout(60)
            ->asJson();
    }

    public function createPayment(float $amount, int $campaignId)
    {
        /** @var Response $response */
        $response = $this->abacateClient->post('/pixQrCode/create', [
            'amount' => $amount * 100,
            'expiresIn' => 15 * 60,
            'description' => 'Teste de pagamento via AbacatePay',
            'metadata' => [
                'campaign_id' => $campaignId,
            ],
        ]);

        $data = $response->json();

        if ($data['error']) {
            throw new Exception($data['error']);
        }

        return $data['data'];
    }

    public static function IsRequestSignatureValid(Request $request)
    {
        $rawBody = $request->getContent();

        $signatureFromHeader = $request->header('X-Webhook-Signature');

        if (! $signatureFromHeader) {
            return false;
        }

        $expectedSignature = base64_encode(
            hash_hmac('sha256', $rawBody, config('services.abacate.public_key'), true)
        );

        $isValidSignature = hash_equals($expectedSignature, $signatureFromHeader);

        if ($isValidSignature) {
            return true;
        } else {
            return false;
        }
    }
}
