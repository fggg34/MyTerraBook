<?php

namespace App\Services\Partners;

use App\Models\User;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CarenPartnerClient
{
    public const BASE_URL = 'https://booking.caren.is';

    public function bookingRequest(User $host, string $path, array $payload): array
    {
        if (! in_array($path, ['class/checkavailable', 'price/getprices', 'payment/getoptions',
            'reservation/add', 'reservation/getitem', 'reservation/confirm', 'reservation/cancel', 'payment/addprepaid'], true)) {
            throw new RuntimeException('Unsupported Caren booking operation.');
        }

        // No automatic retries: a timed-out reservation/payment write may have succeeded.
        return $this->withSession($host, fn (string $session) => $this->request($host, $path, ['Session' => $session] + $payload));
    }

    public function prices(User $host, int $rentalId, int $pickupId, int $dropoffId, CarbonInterface $from, CarbonInterface $to, string $currency): array
    {
        return $this->withSession($host, function (string $session) use ($host, $rentalId, $pickupId, $dropoffId, $from, $to, $currency): array {
            $data = $this->request($host, 'class/getlist', [
                'Session' => $session, 'RentalId' => $rentalId, 'Language' => 'en-GB',
                'DateFrom' => $from->copy()->utc()->format('Y-m-d H:i'),
                'DateTo' => $to->copy()->utc()->format('Y-m-d H:i'),
                'PickupLocationId' => $pickupId, 'DropoffLocationId' => $dropoffId,
                'Currency' => $currency, 'ShowPrices' => true, 'SkipDateCheck' => false,
                'ShowAll' => false, 'ShowImages' => false,
            ]);
            if (! isset($data['Classes']) || ! is_array($data['Classes'])) {
                throw new RuntimeException('Caren did not return pricing results.');
            }

            return $data['Classes'];
        });
    }

    /**
     * One availability calendar for the rental, plus location closure ranges.
     * Caren limits this calendar endpoint to 1 request a minute and 10 an hour,
     * so callers must cache the bundle and pass every class id together.
     *
     * @param  list<int>  $classIds
     * @return array{availability: list<array<string, mixed>>, pickups: list<array<string, mixed>>, dropoffs: list<array<string, mixed>>}
     */
    public function rentalCalendar(User $host, int $rentalId, array $classIds, CarbonInterface $from, CarbonInterface $to): array
    {
        $classIds = array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $classIds),
            static fn (int $id) => $id > 0,
        )));
        if ($classIds === []) {
            throw new RuntimeException('Caren class is missing.');
        }

        return $this->withSession($host, function (string $session) use ($host, $rentalId, $classIds, $from, $to): array {
            $base = ['Session' => $session, 'RentalId' => $rentalId, 'Language' => 'en-GB'];
            $availability = $this->request($host, 'class/availabilitycalendar', $base + [
                'ClassIds' => $classIds,
                'DateFrom' => $from->format('Y-m-d').' 00:00',
                'DateTo' => $to->format('Y-m-d').' 23:00',
            ]);
            $closures = ['ShowClosedDates' => true, 'ShowHours' => false];
            $pickups = $this->request($host, 'location/getpickuplist', $base + $closures);
            $dropoffs = $this->request($host, 'location/getdropofflist', $base + $closures);

            foreach ([[$availability, 'Availability'], [$pickups, 'Locations'], [$dropoffs, 'Locations']] as [$data, $key]) {
                if (! isset($data[$key]) || ! is_array($data[$key])) {
                    throw new RuntimeException('Caren did not return an availability calendar.');
                }
            }

            return [
                'availability' => $availability['Availability'],
                'pickups' => $pickups['Locations'],
                'dropoffs' => $dropoffs['Locations'],
            ];
        });
    }

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
