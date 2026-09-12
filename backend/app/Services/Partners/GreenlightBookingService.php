<?php

namespace App\Services\Partners;

use App\Exceptions\GreenlightPartnerException;
use App\Models\Car;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;

class GreenlightBookingService
{
    public function __construct(
        private readonly GreenlightPartnerClient $client,
        private readonly GreenlightSettings $settings,
        private readonly GreenlightMoney $money,
    ) {}

    public function isPartnerCar(Car $car): bool
    {
        return $car->external_provider === GreenlightSettings::PROVIDER
            && filled($car->external_vehicle_id);
    }

    public function hasAvailability(
        Car $car,
        Location $pickup,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
    ): bool {
        $pickupExternalId = $this->externalLocationId($pickup);
        $availability = $this->client->availability(
            (string) $car->external_vehicle_id,
            $pickupExternalId,
            $pickupAt->toIso8601String(),
            $dropoffAt->toIso8601String(),
        );

        return (bool) ($availability['available'] ?? false) && (int) ($availability['freeCount'] ?? 0) > 0;
    }

    /**
     * Live Greenlight sold-out days for the MyTerra date picker.
     *
     * @return array{blocked_dates: list<string>, blocked: list<array<string, mixed>>, booked: list<array<string, mixed>>, units_available: int}
     */
    public function availabilityCalendar(Car $car, ?string $from = null, ?string $to = null, ?Location $pickup = null): array
    {
        $pickupExternalId = null;
        if ($pickup && $pickup->external_provider === GreenlightSettings::PROVIDER && filled($pickup->external_id)) {
            $pickupExternalId = (string) $pickup->external_id;
        }

        $remote = $this->client->blockedDays(
            (string) $car->external_vehicle_id,
            $from,
            $to,
            $pickupExternalId,
        );

        $blockedDays = array_values(array_filter(array_map(
            'strval',
            $remote['blockedDays'] ?? [],
        )));
        $units = max(1, (int) ($remote['totalUnits'] ?? $remote['bookableUnits'] ?? $car->units_available ?? 1));

        if ($car->units_available !== $units) {
            $car->forceFill(['units_available' => $units])->save();
        }

        $blocked = array_map(static function (string $day): array {
            return [
                'id' => $day,
                'source' => 'greenlight',
                'start' => $day.'T00:00:00.000Z',
                'end' => $day.'T00:00:00.000Z',
                'units_blocked' => 1,
                'notes' => 'Greenlight sold out',
            ];
        }, $blockedDays);

        return [
            'blocked_dates' => $blockedDays,
            'blocked' => $blocked,
            'booked' => [],
            'units_available' => $units,
        ];
    }

    /**
     * @param  array<int|string, mixed>  $rentalOptions
     * @return array<string, mixed>
     */
    public function quote(
        Car $car,
        int $priceTypeId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        Location $pickup,
        Location $dropoff,
        array $rentalOptions = [],
    ): array {
        $remote = $this->client->quote($this->tripPayload($car, $priceTypeId, $pickupAt, $dropoffAt, $pickup, $dropoff, $rentalOptions));

        return $this->mapQuote($remote, $priceTypeId);
    }

    /**
     * @return array{basic_rental_cents: int, protection_upgrade_cents: int}
     */
    public function split(array $quote): array
    {
        $protection = 0;
        foreach ($quote['extras_lines'] as $line) {
            if (($line['kind'] ?? '') === 'insurance') {
                $protection += (int) $line['total_cents'];
            }
        }

        return [
            'basic_rental_cents' => (int) $quote['base_rental_cents'],
            'protection_upgrade_cents' => $protection,
        ];
    }

    /**
     * @param  array<int|string, mixed>  $rentalOptions
     * @return array<string, mixed>
     */
    public function createReservation(
        Car $car,
        int $priceTypeId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        Location $pickup,
        Location $dropoff,
        array $rentalOptions,
        string $customerName,
        string $customerEmail,
        string $customerPhone,
        string $dateOfBirth,
        ?string $customerCountry = null,
        ?string $notes = null,
    ): array {
        $parts = preg_split('/\s+/', trim($customerName)) ?: [];
        $firstName = $parts[0] ?? 'Guest';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $firstName;

        return $this->client->createReservation([
            ...$this->tripPayload($car, $priceTypeId, $pickupAt, $dropoffAt, $pickup, $dropoff, $rentalOptions),
            'driver' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'dateOfBirth' => $dateOfBirth,
                'country' => $customerCountry,
                'comments' => $notes ?: 'Booked on MyTerra',
            ],
        ]);
    }

    public function cancelIfNeeded(Order $order): void
    {
        if ($order->external_provider !== GreenlightSettings::PROVIDER || ! filled($order->external_reference)) {
            return;
        }

        try {
            $this->client->cancelReservation((string) $order->external_reference);
        } catch (GreenlightPartnerException $e) {
            Log::warning('Greenlight cancel failed', [
                'order_id' => $order->id,
                'reference' => $order->external_reference,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int|string, mixed>  $rentalOptions
     * @return array<string, mixed>
     */
    private function tripPayload(
        Car $car,
        int $priceTypeId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        Location $pickup,
        Location $dropoff,
        array $rentalOptions,
    ): array {
        return [
            'vehicleId' => (string) $car->external_vehicle_id,
            'pickupLocationId' => $this->externalLocationId($pickup),
            'dropOffLocationId' => $this->externalLocationId($dropoff),
            'pickupAt' => $pickupAt->toIso8601String(),
            'returnAt' => $dropoffAt->toIso8601String(),
            'insurancePlanId' => $this->insurancePlanId($priceTypeId),
            'addons' => [],
        ];
    }

    private function externalLocationId(Location $location): string
    {
        if ($location->external_provider === GreenlightSettings::PROVIDER && filled($location->external_id)) {
            return (string) $location->external_id;
        }

        throw new GreenlightPartnerException('This pick-up or drop-off location is not mapped to Greenlight.');
    }

    private function insurancePlanId(int $priceTypeId): string
    {
        $plans = $this->settings->insurancePlanIds();
        if ($plans === []) {
            $plans = array_values(array_filter(array_map(
                fn (array $plan): string => (string) ($plan['id'] ?? ''),
                $this->client->insurancePlans(),
            )));
            $this->settings->storeInsurancePlanIds($plans);
        }

        if ($plans === []) {
            throw new GreenlightPartnerException('Greenlight has no insurance plans to attach to this booking.');
        }

        $typeIds = PriceType::query()->where('is_active', true)->orderBy('id')->pluck('id')->all();
        $index = array_search($priceTypeId, $typeIds, true);
        if ($index === false) {
            $index = 0;
        }

        return $plans[min($index, count($plans) - 1)];
    }

    /**
     * @param  array<string, mixed>  $remote
     * @return array<string, mixed>
     */
    private function mapQuote(array $remote, int $priceTypeId): array
    {
        $base = $this->money->fromIsk((int) ($remote['baseRateIsk'] ?? 0));
        $insurance = $this->money->fromIsk((int) ($remote['extrasTotalIsk'] ?? 0));
        $addons = $this->money->fromIsk((int) ($remote['addonsTotalIsk'] ?? 0));
        $feesIsk = (int) ($remote['locationFeeIsk'] ?? 0)
            + (int) ($remote['outOfHoursFeeIsk'] ?? 0)
            + (int) ($remote['oneWayFeeIsk'] ?? 0);
        $fees = $this->money->fromIsk($feesIsk);
        $discount = $this->money->fromIsk((int) ($remote['discountIsk'] ?? 0));
        $total = $this->money->fromIsk((int) ($remote['totalIsk'] ?? 0));

        $extrasLines = [];
        if ($insurance['cents'] > 0) {
            $extrasLines[] = [
                'rental_option_id' => null,
                'kind' => 'insurance',
                'name' => 'Insurance',
                'quantity' => 1,
                'unit_price_cents' => $insurance['cents'],
                'total_cents' => $insurance['cents'],
            ];
        }
        if ($addons['cents'] > 0) {
            $extrasLines[] = [
                'rental_option_id' => null,
                'kind' => 'addon',
                'name' => 'Add-ons',
                'quantity' => 1,
                'unit_price_cents' => $addons['cents'],
                'total_cents' => $addons['cents'],
            ];
        }

        $feesLines = [];
        foreach (($remote['lines'] ?? []) as $line) {
            $key = (string) ($line['key'] ?? '');
            if (! in_array($key, ['location', 'out_of_hours', 'one_way'], true)) {
                continue;
            }
            $mapped = $this->money->fromIsk((int) ($line['amountIsk'] ?? 0));
            if ($mapped['cents'] <= 0) {
                continue;
            }
            $feesLines[] = [
                'label' => (string) ($line['label'] ?? 'Fee'),
                'amount_cents' => $mapped['cents'],
            ];
        }

        return [
            'rental_days' => (int) ($remote['days'] ?? 1),
            'billable_days' => (int) ($remote['days'] ?? 1),
            'pricing_mode' => 'greenlight',
            'extra_hours_charged' => 0,
            'price_type_id' => $priceTypeId,
            'base_rental_cents' => $base['cents'],
            'rental_before_specials_cents' => $base['cents'],
            'special_discount_cents' => 0,
            'special_surcharge_cents' => 0,
            'special_prices_applied' => [],
            'extras_cents' => $insurance['cents'] + $addons['cents'],
            'extras_lines' => $extrasLines,
            'fees_cents' => $fees['cents'],
            'fees_lines' => $feesLines,
            'discount_cents' => $discount['cents'],
            'tax_cents' => 0,
            'total_cents' => $total['cents'],
            'currency' => $total['currency'],
            'coupon_id' => null,
            'greenlight_quote' => $remote,
        ];
    }
}
