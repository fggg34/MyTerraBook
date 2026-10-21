<?php

namespace App\Services\Partners;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CarenPartnerClient
{
    public const BASE_URL = 'https://booking.caren.is';

    public function catalogue(User $host): array
    {
        return $this->withSession($host, function (string $session) use ($host): array {
            // A configured rental can be queried directly. Discovery is unnecessary and
            // can fail independently with Caren code 10000 even after successful login.
            if ($host->partner_rental_id) {
                $rental = ['Id' => $host->partner_rental_id, 'Name' => $host->name ?: 'Caren rental '.$host->partner_rental_id];
            } else {
                $rentals = $this->request($host, 'rental/list', ['Session' => $session])['Rentals'] ?? null;
                if (! is_array($rentals)) {
                    throw new RuntimeException('Caren did not return a rental list.');
                }
                $rental = count($rentals) === 1 ? $rentals[0] : null;
            }
            if (! $rental) {
                throw new RuntimeException('Select a rental ID that this Caren account can access.');
            }

            $payload = ['Session' => $session, 'RentalId' => $rental['Id'], 'Language' => 'en-GB'];
            $vehicles = $this->request($host, 'class/getlist', $payload + [
                'SkipDateCheck' => true, 'ShowPrices' => false, 'ShowImages' => true, 'ShowGroupNames' => true,
            ]);
            $pickups = $this->request($host, 'location/getpickuplist', $payload);
            $dropoffs = $this->request($host, 'location/getdropofflist', $payload);

            foreach ([[$vehicles, 'Classes'], [$pickups, 'Locations'], [$dropoffs, 'Locations']] as [$data, $key]) {
                if (! isset($data[$key]) || ! is_array($data[$key])) {
                    throw new RuntimeException('Caren returned an incomplete catalogue. No listings were changed.');
                }
            }

            return [
                'rental' => $rental,
                'vehicles' => $vehicles['Classes'],
                'pickups' => $pickups['Locations'],
                'dropoffs' => $dropoffs['Locations'],
            ];
        });
    }

    private function withSession(User $host, Closure $callback): mixed
    {
        if ($host->partner_provider !== 'caren' || ! $host->partner_api_enabled
            || blank($host->partner_api_key) || blank($host->partner_api_username) || blank($host->partner_api_password)) {
            throw new RuntimeException('Enable Caren and save the API key, API username and API password first.');
        }

        $login = $this->request($host, 'user/login', [
            'ApiKey' => $host->partner_api_key,
            'Username' => $host->partner_api_username,
            'Password' => $host->partner_api_password,
        ]);
        $session = $login['Session'] ?? null;
        if (! is_string($session) || $session === '') {
            throw new RuntimeException('Caren login failed. Check the saved Vehicle API credentials.');
        }

        // Caren can return the same session on subsequent logins, even after logout
        // invalidates it. Let its documented idle expiry end the session instead;
        // logging out here breaks subsequent imports and concurrent API clients.
        return $callback($session);
    }

    private function request(User $host, string $path, array $payload): array
    {
        $base = rtrim($host->partner_api_base_url ?: self::BASE_URL, '/');
        if (! in_array($base, [self::BASE_URL, 'https://test-booking.caren.is'], true)) {
            throw new RuntimeException('Use https://booking.caren.is or https://test-booking.caren.is as the Caren API URL.');
        }

        $response = Http::acceptJson()->asJson()->timeout(25)
            ->withOptions(['allow_redirects' => false])
            ->post($base.'/vehicleapi/'.$path, $payload);
        $data = $response->json();

        if (! $response->successful() || ! is_array($data)
            || ($data['Success'] ?? true) === false || ! empty($data['Error']) || ! empty($data['Errors'])) {
            // Raw provider errors may contain credentials or session tokens.
            $code = is_array($data) && is_numeric($data['Code'] ?? null) ? ', provider code '.(int) $data['Code'] : '';
            throw new RuntimeException('Caren '.$path.' failed (HTTP '.$response->status().$code.'). Check credentials, rental access and service availability.');
        }

        return $data;
    }
}
