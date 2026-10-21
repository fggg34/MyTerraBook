<?php

namespace App\Services\Partners;

use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FoxiePartnerClient
{
    public const BASE_URL = 'https://foxiecampers.is/api/v1';

    /**
     * Read locations and vehicle details. This never calls POST /bookings.
     *
     * @return array{locations: list<array<string, mixed>>, vehicles: list<array<string, mixed>>}
     */
    public function catalogue(User $host): array
    {
        $locations = $this->list($host, 'locations');
        $vehicles = [];
        foreach ($this->list($host, 'vehicles') as $row) {
            $id = $this->id($row);
            $detail = $this->get($host, 'vehicles/'.$id);
            if (array_is_list($detail)) {
                throw new RuntimeException('Foxie returned an unexpected vehicle.');
            }
            $vehicles[] = ['id' => $id] + $detail;
        }

        return ['locations' => $locations, 'vehicles' => $vehicles];
    }

    /**
     * @return array<string, mixed>
     */
    public function vehicle(User $host, int $vehicleId): array
    {
        $detail = $this->get($host, 'vehicles/'.$vehicleId);
        if (array_is_list($detail)) {
            throw new RuntimeException('Foxie returned an unexpected vehicle.');
        }

        return ['id' => $vehicleId] + $detail;
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    public function availability(User $host, array $query): array
    {
        $body = $this->get($host, 'availability', $query);
        if (! array_key_exists('available', $body)) {
            throw new RuntimeException('Foxie availability could not be checked.');
        }

        return $body;
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    public function pricing(User $host, array $query): array
    {
        return $this->get($host, 'quotes', $query);
    }

    /**
     * @param  array<string, scalar>  $query
     * @return list<array<string, mixed>>
     */
    private function list(User $host, string $path, array $query = []): array
    {
        $body = $this->get($host, $path, $query);
        if (! array_is_list($body)) {
            throw new RuntimeException('Foxie returned an unexpected list.');
        }
        foreach ($body as $row) {
            if (! is_array($row)) {
                throw new RuntimeException('Foxie returned an unexpected list.');
            }
        }

        return $body;
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    private function get(User $host, string $path, array $query = []): array
    {
        $this->assertHost($host);
        $response = Http::acceptJson()
            ->withHeaders([
                'X-API-Key' => trim((string) $host->partner_api_key),
                'User-Agent' => 'MyTerraBook/1.0',
            ])
            ->withOptions(['allow_redirects' => false])
            ->timeout(20)
            ->get(self::BASE_URL.'/'.ltrim($path, '/'), $query);

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('Foxie returned an unexpected response.');
        }
        if ($response->status() === 404 && $this->isNoRate($body)) {
            throw new FoxieNoRateException('Foxie has no rate for these dates.');
        }
        if (! $response->successful()) {
            throw new RuntimeException('Foxie request failed ('.$this->errorCode($body).').');
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function isNoRate(array $body): bool
    {
        if (($body['code'] ?? null) === 'no_price' || ($body['error'] ?? null) === 'no_price') {
            return true;
        }
        $message = strtolower((string) ($body['message'] ?? ''));

        return str_contains($message, 'no price') || str_contains($message, 'no rate');
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function errorCode(array $body): string
    {
        $raw = $body['code'] ?? $body['error'] ?? null;
        $code = match ($raw) {
            'forbidden' => 'invalid_api_key',
            'unauthorized' => 'missing_api_key',
            default => is_string($raw) ? $raw : 'request_failed',
        };

        return preg_match('/^[a-z0-9_]{1,64}$/', $code) ? $code : 'request_failed';
    }

    private function assertHost(User $host): void
    {
        if ($host->partner_provider !== 'foxie' || ! $host->partner_api_enabled || blank($host->partner_api_key)) {
            throw new RuntimeException('Foxie is not connected for this host.');
        }
        $base = rtrim((string) $host->partner_api_base_url, '/');
        if ($base !== '' && $base !== self::BASE_URL) {
            throw new RuntimeException('Foxie only accepts its published API URL.');
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function id(array $row): int
    {
        $id = $row['id'] ?? null;
        if (! is_numeric($id) || (int) $id < 1) {
            throw new RuntimeException('Foxie returned a vehicle without an ID.');
        }

        return (int) $id;
    }
}
