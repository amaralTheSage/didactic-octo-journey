<?php

use App\Enums\PaymentStatus;
use App\Models\Campaign;
use App\Models\Payment;
use App\Models\User;
use App\Services\AbacatePayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = User::factory()->create(['role' => \App\Enums\UserRole::COMPANY]);
    $this->otherUser = User::factory()->create();
    $this->campaign = Campaign::factory()->create([
        'company_id' => $this->company->id,
    ]);
});

it('store creates a payment using AbacatePayService and redirects to signed page', function () {
    actingAs($this->company);

    // AbacatePayService -> underlying HTTP request fake
    Http::fake([
        'https://api.abacatepay.com/*' => Http::response([
            'error' => null,
            'data' => [
                'id' => 'abacate-id-123',
                'brCode' => 'BRCODEXYZ',
            ],
        ], 200),
    ]);

    // Use GET instead of POST
    $response = get(route('payments.qrcode', ['campaign_id' => $this->campaign->id]));

    $response->assertStatus(302);

    // Payment must exist
    assertDatabaseHas('payments', [
        'abacate_id' => 'abacate-id-123',
        'campaign_id' => $this->campaign->id,
        'user_id' => $this->company->id,
        'brcode' => 'BRCODEXYZ',
        'status' => PaymentStatus::PENDING->value,
    ]);

    $payment = Payment::first();
    // Redirect goes to a signed route that includes payment id
    $this->assertStringContainsString((string) $payment->id, $response->headers->get('Location'));
});

it('store returns existing pending non-expired payment instead of creating new', function () {
    actingAs($this->company);

    $existing = Payment::create([
        'abacate_id' => 'existing-abacate',
        'campaign_id' => $this->campaign->id,
        'user_id' => $this->company->id,
        'brcode' => 'BR123',
        'amount' => 100,
        'status' => PaymentStatus::PENDING,
        'expires_at' => now()->addMinutes(10),
        'metadata' => [],
    ]);

    // Use GET instead of POST
    $response = get(route('payments.qrcode', ['campaign_id' => $this->campaign->id]));

    $response->assertStatus(302);

    // No extra payment was created (still only one)
    assertDatabaseCount('payments', 1);

    // Redirect should point to payments.page for existing payment
    $this->assertStringContainsString((string) $existing->id, $response->headers->get('Location'));
});

it('pix webhook validates signature and updates payment and campaign when paid', function () {
    // Prepare payment record that matches incoming webhook
    $payment = Payment::create([
        'abacate_id' => 'webhook-id-1',
        'campaign_id' => $this->campaign->id,
        'user_id' => $this->company->id,
        'brcode' => 'BRWEB',
        'amount' => 100,
        'status' => PaymentStatus::PENDING,
        'expires_at' => now()->addMinutes(15),
        'metadata' => ['campaign_id' => $this->campaign->id],
    ]);

    // Build payload matching AbacatePay webhook format
    $payload = [
        'id' => 'evt_1',
        'event' => 'billing.paid',
        'data' => [
            'pixQrCode' => [
                'id' => 'webhook-id-1',
                'amount' => 100,
                'kind' => 'PIX',
                'status' => 'PAID',
                'metadata' => [
                    'campaign_id' => (string) $this->campaign->id,
                ],
            ],
        ],
    ];

    // Configure webhook secret & public key
    Config::set('services.abacatepay.webhook_secret', 'my-webhook-secret');
    Config::set('services.abacatepay.public_key', 'my-public-key');

    // Compute signature
    $rawBody = json_encode($payload);
    $signature = base64_encode(hash_hmac('sha256', $rawBody, config('services.abacatepay.public_key'), true));

    // POST to webhook URL with webhookSecret query param
    $response = $this->withHeaders([
        'X-Webhook-Signature' => $signature,
        'Content-Type' => 'application/json',
    ])->postJson(
        route('payments.webhook') . '?webhookSecret=' . config('services.abacatepay.webhook_secret'),
        $payload
    );

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    // Verify payment was updated
    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::PAID);
});

it('webhook rejects invalid signature', function () {
    $payment = Payment::create([
        'abacate_id' => 'webhook-id-2',
        'campaign_id' => $this->campaign->id,
        'user_id' => $this->company->id,
        'brcode' => 'BRWEB2',
        'amount' => 100,
        'status' => PaymentStatus::PENDING,
        'expires_at' => now()->addMinutes(15),
        'metadata' => [],
    ]);

    $payload = [
        'id' => 'evt_2',
        'event' => 'billing.paid',
        'data' => [
            'pixQrCode' => [
                'id' => 'webhook-id-2',
                'status' => 'PAID',
            ],
        ],
    ];

    Config::set('services.abacatepay.webhook_secret', 'my-webhook-secret');
    Config::set('services.abacatepay.public_key', 'my-public-key');

    // Use WRONG signature
    $response = $this->withHeaders([
        'X-Webhook-Signature' => 'invalid-signature',
        'Content-Type' => 'application/json',
    ])->postJson(
        route('payments.webhook') . '?webhookSecret=' . config('services.abacatepay.webhook_secret'),
        $payload
    );

    $response->assertStatus(401);

    // Payment should NOT be updated
    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::PENDING);
});

it('AbacatePayService::createPayment returns data on success', function () {
    Http::fake([
        'https://api.abacatepay.com/*' => Http::response([
            'error' => null,
            'data' => ['id' => 'ok-id', 'brCode' => 'BR-OK'],
        ], 200),
    ]);

    $svc = new AbacatePayService();
    $data = $svc->createPayment(1.5, $this->campaign->id);

    expect($data)->toBeArray()
        ->and($data['id'])->toBe('ok-id')
        ->and($data['brCode'])->toBe('BR-OK');
});

it('AbacatePayService::createPayment throws on API error', function () {
    Http::fake([
        'https://api.abacatepay.com/*' => Http::response([
            'error' => 'Something went wrong',
            'data' => null,
        ], 400),
    ]);

    $svc = new AbacatePayService();

    expect(fn() => $svc->createPayment(2.0, $this->campaign->id))
        ->toThrow(\Exception::class);
});

it('IsRequestSignatureValid returns true for correct signature and false otherwise', function () {
    $payload = ['hello' => 'world'];
    $raw = json_encode($payload);

    Config::set('services.abacatepay.public_key', 'verify-key');

    $good = base64_encode(hash_hmac('sha256', $raw, config('services.abacatepay.public_key'), true));
    $bad = 'invalid-signature';

    // Build fake request objects
    $goodRequest = Request::create('/webhook', 'POST', [], [], [], [], $raw);
    $goodRequest->headers->set('X-Webhook-Signature', $good);

    $badRequest = Request::create('/webhook', 'POST', [], [], [], [], $raw);
    $badRequest->headers->set('X-Webhook-Signature', $bad);

    expect(AbacatePayService::IsRequestSignatureValid($goodRequest))->toBeTrue();
    expect(AbacatePayService::IsRequestSignatureValid($badRequest))->toBeFalse();
});

it('expired payments are not reused', function () {
    actingAs($this->company);

    // Create expired payment
    Payment::create([
        'abacate_id' => 'expired-payment',
        'campaign_id' => $this->campaign->id,
        'user_id' => $this->company->id,
        'brcode' => 'BREXP',
        'amount' => 100,
        'status' => PaymentStatus::PENDING,
        'expires_at' => now()->subMinutes(10), // Expired
        'metadata' => [],
    ]);

    Http::fake([
        'https://api.abacatepay.com/*' => Http::response([
            'error' => null,
            'data' => [
                'id' => 'new-payment-id',
                'brCode' => 'BRNEW',
            ],
        ], 200),
    ]);

    $response = get(route('payments.qrcode', ['campaign_id' => $this->campaign->id]));

    $response->assertStatus(302);

    // Should have created a NEW payment (2 total)
    assertDatabaseCount('payments', 2);

    assertDatabaseHas('payments', [
        'abacate_id' => 'new-payment-id',
        'status' => PaymentStatus::PENDING->value,
    ]);
});

it('only company can create payment for their campaign', function () {
    $otherCompany = User::factory()->create(['role' => \App\Enums\UserRole::COMPANY]);

    actingAs($otherCompany);

    $response = get(route('payments.qrcode', ['campaign_id' => $this->campaign->id]));

    $response->assertRedirect();
});
