<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Thin client around the Rapyd Collect API.
 *
 * The platform only ever charges the online booking fee through Rapyd; the
 * remaining balance is settled in cash directly with the host on arrival, so
 * this service intentionally implements no payout / transfer calls.
 */
class RapydService
{
    private string $accessKey;

    private string $secretKey;

    private string $baseUrl;

    private string $webhookSecret;

    public function __construct()
    {
        // Prefer credentials saved via Admin → Payment Methods; fall back to .env.
        // Previously only .env was read, so keys entered in the admin UI were ignored
        // and checkout creation failed — while the SPA still showed "confirmed".
        $dbConfig = [];
        try {
            $dbConfig = PaymentMethod::query()->where('code', 'rapyd_card')->value('config') ?? [];
        } catch (\Throwable) {
            $dbConfig = [];
        }
        if (! is_array($dbConfig)) {
            $dbConfig = [];
        }

        $this->accessKey = (string) (
            (! empty($dbConfig['access_key']) ? $dbConfig['access_key'] : null)
            ?? config('rapyd.access_key')
            ?? ''
        );
        $this->secretKey = (string) (
            (! empty($dbConfig['secret_key']) ? $dbConfig['secret_key'] : null)
            ?? config('rapyd.secret_key')
            ?? ''
        );
        $this->webhookSecret = (string) (
            (! empty($dbConfig['webhook_secret']) ? $dbConfig['webhook_secret'] : null)
            ?? config('rapyd.webhook_secret')
            ?? ''
        );

        $environment = (string) ($dbConfig['environment'] ?? '');
        if ($environment === 'production') {
            $this->baseUrl = 'https://api.rapyd.net';
        } elseif ($environment === 'sandbox') {
            $this->baseUrl = 'https://sandboxapi.rapyd.net';
        } else {
            $this->baseUrl = rtrim((string) config('rapyd.base_url', 'https://sandboxapi.rapyd.net'), '/');
        }
    }

    /**
     * Whether access/secret keys are present (from admin config or .env).
     */
    public function isConfigured(): bool
    {
        return $this->accessKey !== '' && $this->secretKey !== '';
    }

    /**
     * Safe diagnostics for logs (no secrets). Useful when .env was updated but
     * Admin → Payment Methods still supplies older keys / environment.
     *
     * @return array{base_url: string, credential_source: string, environment: string, access_key_last4: string}
     */
    public function diagnosticInfo(): array
    {
        $dbConfig = [];
        try {
            $dbConfig = PaymentMethod::query()->where('code', 'rapyd_card')->value('config') ?? [];
        } catch (\Throwable) {
            $dbConfig = [];
        }
        if (! is_array($dbConfig)) {
            $dbConfig = [];
        }

        $fromDb = ! empty($dbConfig['access_key']) || ! empty($dbConfig['secret_key']);
        $environment = (string) ($dbConfig['environment'] ?? '');
        if ($environment === '') {
            $environment = str_contains($this->baseUrl, 'sandbox') ? 'sandbox' : 'production';
        }

        return [
            'base_url' => $this->baseUrl,
            'credential_source' => $fromDb ? 'admin_db' : 'env',
            'environment' => $environment,
            'access_key_last4' => $this->accessKey !== '' ? substr($this->accessKey, -4) : '',
        ];
    }

    /**
     * ISO 4217 currencies that have no minor unit (amounts must be whole numbers).
     *
     * @var list<string>
     */
    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'PYG',
        'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Number of decimal places Rapyd expects for a currency's amount.
     */
    public static function decimalsFor(string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true) ? 0 : 2;
    }

    /**
     * Format an amount to the precision Rapyd expects for the given currency.
     * Zero-decimal currencies (ISK, JPY, …) are returned as integers.
     *
     * @return int|float
     */
    public static function formatAmount(float $amount, string $currency)
    {
        $decimals = self::decimalsFor($currency);

        return $decimals === 0 ? (int) round($amount) : round($amount, $decimals);
    }

    /**
     * Create a hosted checkout page for the platform fee (a percentage of the total).
     *
     * @param  array<string, mixed>  $data  Expects: amount (platform fee, major units),
     *                                       currency, country, merchant_reference_id, metadata[]
     * @return array{checkout_id: string, redirect_url: string, raw: array<string, mixed>}
     */
    public function createCheckoutPage(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Rapyd is not configured. Add RAPYD_ACCESS_KEY / RAPYD_SECRET_KEY to .env, or save keys under Admin → Payment Methods.'
            );
        }

        $currency = strtoupper((string) ($data['currency'] ?? config('rapyd.currency', 'ISK')));

        $payload = [
            // Rapyd expects the amount in the currency's standard unit (e.g. 10.50
            // for USD, 3000 for ISK). Zero-decimal currencies such as ISK/JPY must
            // NOT carry decimals, so we format the amount per the currency.
            'amount' => self::formatAmount((float) ($data['amount'] ?? 0), $currency),
            'currency' => $currency,
            // Always Iceland: TerraBook collects for Iceland listings only.
            // Do not take country from the request/config so a mis-set env cannot
            // switch Rapyd to another country's payment methods.
            'country' => 'IS',
            'merchant_reference_id' => (string) ($data['merchant_reference_id'] ?? ''),
            // Hosted Checkout uses complete/cancel checkout URLs. The older
            // complete_payment_url / error_payment_url pair is also sent for
            // redirect-style card flows.
            'complete_checkout_url' => $data['complete_payment_url'] ?? config('rapyd.complete_payment_url'),
            'cancel_checkout_url' => $data['error_payment_url'] ?? config('rapyd.error_payment_url'),
            'complete_payment_url' => $data['complete_payment_url'] ?? config('rapyd.complete_payment_url'),
            'error_payment_url' => $data['error_payment_url'] ?? config('rapyd.error_payment_url'),
            'metadata' => $data['metadata'] ?? [],
        ];

        // Only constrain methods when explicitly configured. Rapyd expects
        // `payment_method_types_include` (not `payment_method_types`).
        // Otherwise default to the card category only — Hosted Checkout also
        // requires Card to be enabled under Client Portal → Settings → Branding.
        $methodTypes = $data['payment_method_types'] ?? config('rapyd.payment_method_types');
        if (is_array($methodTypes) && $methodTypes !== []) {
            $payload['payment_method_types_include'] = array_values($methodTypes);
        } else {
            $payload['payment_method_type_categories'] = ['card'];
        }

        $response = $this->request('post', '/v1/checkout', $payload);
        $body = $response->json();
        $checkout = $body['data'] ?? [];

        if (! $response->successful() || empty($checkout['id'])) {
            Log::error('Rapyd checkout creation failed', [
                'status' => $response->status(),
                'body' => $body,
                'rapyd' => $this->diagnosticInfo(),
                'country' => $payload['country'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'has_method_types_include' => isset($payload['payment_method_types_include']),
            ]);
            $rapydMessage = (string) data_get($body, 'status.message', '');
            $rapydCode = (string) data_get($body, 'status.error_code', '');
            $hint = $rapydMessage !== ''
                ? $rapydMessage
                : 'Unable to create Rapyd checkout page.';
            if ($rapydCode === 'ERROR_HOSTED_PAGE_PAYMENT_METHOD_TYPE_CATEGORIES_NOT_ENABLED') {
                $diag = $this->diagnosticInfo();
                $hint = 'Rapyd Hosted Checkout cannot show Card yet'
                    .' ('.$diag['environment'].' via '.$diag['credential_source'].', '.$diag['base_url'].').'
                    .' Settings → Payment Methods alone is not enough: open Settings → Branding,'
                    .' select Hosted Checkout Page, enable the Card category, Save, then retry.'
                    .' Also confirm the portal is on Production if you use live keys (api.rapyd.net).'
                    .' Note: keys saved under Admin → Payment Methods override .env.';
            }
            throw new RuntimeException($hint.($rapydCode !== '' ? " [{$rapydCode}]" : ''));
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
        // Rapyd signs the hex HMAC digest, then base64-encodes that hex string.
        $expected = base64_encode(hash_hmac('sha256', $toSign, $this->secretKey, false));

        // Also accept a dashboard-configured shared webhook secret if provided.
        if ($this->webhookSecret !== '') {
            $expectedSecret = base64_encode(hash_hmac('sha256', $toSign, $this->webhookSecret, false));
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

        $toSign = $method.$urlPath.$salt.$timestamp.$this->accessKey.$this->secretKey.$jsonBody;
        // Rapyd signs the *hex* HMAC digest, then base64-encodes that hex string.
        $signature = base64_encode(hash_hmac('sha256', $toSign, $this->secretKey, false));

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
        return $client->withBody($jsonBody, 'application/json')->{$method}($url);
    }

    private function salt(): string
    {
        return Str::lower(Str::random(8));
    }
}
