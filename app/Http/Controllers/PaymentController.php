<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function store(Request $request)
    {

        Gate::authorize('is_company');


        $response = Http::withToken(config('services.abacatepay.token'))
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.abacatepay.com/v1/pixQrCode/create', [
                'amount' => 100,
                'expiresIn' => 30,
                'description' => 'Teste Vamo Dale',
                'metadata' => [
                    'campaign_id' => $request['campaign_id'],
                ],
            ]);


        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to create PIX QR Code',
                'error' => $response->json(),
            ], $response->status());
        }

        return back()->with('qrcode_base64', $response->json());
    }
}
