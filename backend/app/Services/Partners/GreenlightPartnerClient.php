<?php

namespace App\Services\Partners;

use App\Exceptions\GreenlightPartnerException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GreenlightPartnerClient
{
    public function __construct(
        private readonly GreenlightSettings $settings,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function locations(): array
    {
        return $this->list('locations');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function categories(): array
    {
        return $this->list('categories');
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    public function vehicles(array $query = []): array
    {
        return $this->list('vehicles', $query);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function insurancePlans(): array
    {
        return $this->list('insurance-plans');
    }

    /**
     * @return array<string, mixed>
     */
    public function availability(string $vehicleId, string $pickupLocationId, string $pickupAt, string $returnAt): array
    {
        return $this->get('availability', [
            'vehicleId' => $vehicleId,
            'pickupLocationId' => $pickupLocationId,
            'pickupAt' => $pickupAt,
            'returnAt' => $returnAt,
        ]);
    }

    /**
     * @return array{vehicleId?: string, totalUnits?: int, bookableUnits?: int, blockedDays?: list<string>}
     */
    public function blockedDays(string $vehicleId, ?string $from = null, ?string $to = null, ?string $pickupLocationId = null): array
    {
        return $this->get('blocked-days', array_filter([
            'vehicleId' => $vehicleId,
            'from' => $from,
            'to' => $to,
            'pickupLocationId' => $pickupLocationId,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function quote(array $payload): array
    {
        return $this->post('quote', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createReservation(array $payload): array
    {
        return $this->post('reservations', $payload, 201);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmReservation(string $reference): array
    {
        return $this->post('reservations/'.rawurlencode($reference).'/confirm', []);
    }

    public function cancelReservation(string $reference): array
    {
        return $this->post('reservations/'.rawurlencode($reference).'/cancel', []);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function list(string $path, array $query = []): array
    {
        $data = $this->get($path, $query);

        return array_values(is_array($data) ? $data : []);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $response = $this->request()->get($this->url($path), $query);

        return $this->unwrap($response);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload, int $ok = 200): array
    {
        $response = $this->request()->post($this->url($path), $payload);

        return $this->unwrap($response, $ok);
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        if ($this->settings->apiKey() === '') {
            throw new GreenlightPartnerException('Greenlight API key is missing. Paste it in Global Configuration, then Save or Sync.');
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(20)
            ->withToken($this->settings->apiKey())
            ->withHeaders(['X-Api-Key' => $this->settings->apiKey()]);
    }

    private function url(string $path): string
    {
        return $this->settings->baseUrl().'/'.ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    private function unwrap(Response $response, int $ok = 200): array
    {
        if ($response->status() !== $ok && ! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', 'Greenlight request failed.');

            throw new GreenlightPartnerException($message);
        }

        $json = $response->json();
        $data = is_array($json) ? ($json['data'] ?? $json) : [];

        return is_array($data) ? $data : [];
    }
}
