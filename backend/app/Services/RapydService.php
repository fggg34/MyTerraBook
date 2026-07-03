<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Thin client around the Rapyd Collect API.
 *
 * The platform only ever charges the 20% booking fee through Rapyd; the
 * remaining 80% is settled in cash directly with the host on arrival, so this
 * service intentionally implements no payout / transfer calls.
 */
class RapydService
{
    private string $accessKey;

    private string $secretKey;

    private string $baseUrl;

    private string $webhookSecret;

    public function __construct()
    {
        $this->accessKey = (string) config('rapyd.access_key');
        $this->secretKey = (string) config('rapyd.secret_key');
        $this->baseUrl = rtrim((string) config('rapyd.base_url'), '/');
        $this->webhookSecret = (string) config('rapyd.webhook_secret');
    }

    /**
     * Create a hosted checkout page for the platform fee (20% of the total).
     *
     * @param  array<string, mixed>  $data  Expects: amount (platform fee, major units),
     *                                       currency, country, merchant_reference_id, metadata[]
     * @return array{checkout_id: string, redirect_url: string, raw: array<string, mixed>}
     */
    public function createCheckoutPage(array $data): array
    {
        $amountMajor = round((float) ($data['amount'] ?? 0), 2);

        $payload = [
            // Rapyd expects the amount in the currency's major unit for checkout,
            // but we keep cents-precision by rounding to 2 decimals. For strict
            // "smallest unit" APIs, multiply by 100 — Rapyd /v1/checkout uses major units.
            'amount' => $amountMajor,
            'currency' => $data['currency'] ?? config('rapyd.currency', 'USD'),
            'country' => $data['country'] ?? config('rapyd.country', 'US'),
            'payment_method_types' => $data['payment_method_types'] ?? config('rapyd.payment_method_types'),
            'merchant_reference_id' => (string) ($data['merchant_reference_id'] ?? ''),
            'complete_payment_url' => $data['complete_payment_url'] ?? config('rapyd.complete_payment_url'),
            'error_payment_url' => $data['error_payment_url'] ?? config('rapyd.error_payment_url'),
            'metadata' => $data['metadata'] ?? [],
        ];

        $response = $this->request('post', '/v1/checkout', $payload);
        $body = $response->json();
        $checkout = $body['data'] ?? [];

        if (! $response->successful() || empty($checkout['id'])) {
            Log::error('Rapyd checkout creation failed', ['status' => $response->status(), 'body' => $body]);
            throw new RuntimeException('Unable to create Rapyd checkout page.');
        }

        return [
            'checkout_id' => (string) $checkout['id'],
            'redirect_url' => (string) ($checkout['redirect_url'] ?? ''),
            'raw' => $checkout,
        ];
    }

    /**
     * Retrieve the current status / details of a checkout.
     *
     * @return array<string, mixed>
     */
    public function getCheckoutStatus(string $checkoutId): array
    {
        $response = $this->request('get', '/v1/checkout/'.$checkoutId);
        $body = $response->json();

        if (! $response->successful()) {
            Log::error('Rapyd checkout status failed', ['status' => $response->status(), 'body' => $body]);
            throw new RuntimeException('Unable to fetch Rapyd checkout status.');
        }

        return $body['data'] ?? [];
    }

    /**
     * Verify an incoming Rapyd webhook signature.
     *
     * Rapyd signs webhooks with: base64( hmac_sha256( url_path + salt + timestamp + access_key + secret_key + body ) )
     * The signature and metadata arrive as request headers.
     */
    public function verifyWebhook(Request $request): bool
    {
        $signature = (string) $request->header('signature', '');
        $salt = (string) $request->header('salt', '');
        $timestamp = (string) $request->header('timestamp', '');
        $accessKey = (string) $request->header('access_key', '');

        if ($signature === '' || $salt === '' || $timestamp === '') {
            return false;
        }

        // Rapyd hashes against the full webhook URL path (the value it was registered with).
        $urlPath = $request->getPathInfo();
        $body = $request->getContent();

        $toSign = $urlPath.$salt.$timestamp.$accessKey.$this->secretKey.$body;
        $expected = base64_encode(hash_hmac('sha256', $toSign, $this->secretKey, true));

        // Also accept a dashboard-configured shared webhook secret if provided.
        if ($this->webhookSecret !== '') {
            $expectedSecret = base64_encode(hash_hmac('sha256', $toSign, $this->webhookSecret, true));
            if (hash_equals($expectedSecret, $signature)) {
                return true;
            }
        }

        return hash_equals($expected, $signature);
    }

    /**
     * Perform a signed request against the Rapyd API.
     *
     * @param  array<string, mixed>  $body
     */
    private function request(string $method, string $urlPath, array $body = []): Response
    {
        $method = strtolower($method);
        $salt = $this->salt();
        $timestamp = (string) time();

        // GET requests must sign against an empty body.
        $jsonBody = ($method === 'get' || $body === []) ? '' : json_encode($body, JSON_UNESCAPED_SLASHES);
        if ($jsonBody === '{}') {
            $jsonBody = '';
        }

        $toSign = $method.$urlPath.$salt.$timestamp.$this->accessKey.$this->secretKey.$jsonBody;
        $signature = base64_encode(hash_hmac('sha256', $toSign, $this->secretKey, true));

        $headers = [
            'access_key' => $this->accessKey,
            'salt' => $salt,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'Content-Type' => 'application/json',
        ];

        $client = Http::withHeaders($headers)
            ->timeout(30)
            ->acceptJson();

        $url = $this->baseUrl.$urlPath;

        if ($method === 'get') {
            return $client->get($url);
        }

        // Send the exact signed JSON string so the signature matches the body byte-for-byte.
        return $client->withBody($jsonBody === '' ? '{}' : $jsonBody, 'application/json')->{$method}($url);
    }

    private function salt(): string
    {
        return Str::lower(Str::random(8));
    }
}
