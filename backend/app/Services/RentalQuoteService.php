<?php

namespace App\Services;

use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\Coupon;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\SpecialPrice;
use App\Support\RentalOptionPricing;
use App\Models\TaxRate;
use App\Support\PricingCurrency;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class RentalQuoteService
{
    public function quote(
        Car $car,
        int $priceTypeId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $pickupLocationId,
        int $dropoffLocationId,
        array $rentalOptionSelections,
        ?string $couponCode
    ): array {
        if ($pickupAt->greaterThanOrEqualTo($dropoffAt)) {
            throw new InvalidArgumentException('Drop-off must be after pick-up.');
        }

        $durationMinutes = max(1, (int) $pickupAt->diffInMinutes($dropoffAt));
        $rentalDays = max(1, (int) ceil($durationMinutes / 1440));
        $defaultTaxBips = $this->defaultTaxBips();

        $this->assertRestrictions($pickupAt, $dropoffAt, $rentalDays);

        $priceType = PriceType::query()->find($priceTypeId);
        if ($priceType === null || ! $priceType->is_active) {
            throw new InvalidArgumentException('Selected rate plan is not available.');
        }

        $pricing = $this->resolveBaseRentalCharge(
            $car->id,
            $priceTypeId,
            $pickupAt,
            $dropoffAt,
            $durationMinutes,
        );

        $baseRentalCents = $pricing['base_rental_cents'];
        $specialPricing = $this->applySpecialPrices(
            $baseRentalCents,
            $car->id,
            $priceTypeId,
            $pickupLocationId,
            $dropoffLocationId,
            $pickupAt,
            $dropoffAt,
            $pricing['billable_days'],
        );
        $baseRentalCents = $specialPricing['amount_cents'];
        $baseTaxBips = $this->taxBipsForTaxRateId($priceType->tax_rate_id, $defaultTaxBips);

        $rentalOptionSelections = $this->normalizeRentalOptionSelections($rentalOptionSelections);

        $extrasCents = 0;
        $extrasLines = [];

        if ($rentalOptionSelections !== []) {
            $optionIds = array_map('intval', array_keys($rentalOptionSelections));
            $carOptions = $car->rentalOptions()
                ->whereIn('rental_options.id', $optionIds)
                ->where('rental_options.is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($rentalOptionSelections as $optionId => $quantity) {
                $optionId = (int) $optionId;
                $quantity = max(1, (int) $quantity);
                $option = $carOptions->get($optionId);
                if ($option === null) {
                    throw new InvalidArgumentException("Add-on is not available for this vehicle.");
                }

                $unitCents = RentalOptionPricing::resolveCostCents(
                    $option->pivot->cost_cents,
                    (int) $option->cost_cents,
                );
                $isDailyCost = RentalOptionPricing::resolveIsDailyCost(
                    $option->pivot->is_daily_cost,
                    (bool) $option->is_daily_cost,
                );

                $line = RentalOptionPricing::lineTotalCents(
                    $unitCents,
                    $isDailyCost,
                    $quantity,
                    $rentalDays,
                    $option->max_cost_cap_cents,
                );

                $extrasCents += $line;
                $taxBips = $this->taxBipsForTaxRateId($option->tax_rate_id, $defaultTaxBips);

                $extrasLines[] = [
                    'rental_option_id' => $option->id,
                    'name' => $option->name,
                    'quantity' => $quantity,
                    'unit_price_cents' => (int) $unitCents,
                    'total_cents' => $line,
                    'tax_bips' => $taxBips,
                ];
            }
        }

        [$feesCents, $feesLines] = $this->computeFees(
            $car,
            $pickupLocationId,
            $dropoffLocationId,
            $pickupAt,
            $dropoffAt,
            $pricing['billable_days'],
            $defaultTaxBips,
        );

        $subtotalBeforeDiscount = $baseRentalCents + $extrasCents;
        $discountCents = 0;
        $couponId = null;

        if ($couponCode !== null && $couponCode !== '') {
            $coupon = Coupon::query()
                ->where('code', strtoupper(trim($couponCode)))
                ->where('is_active', true)
                ->first();

            if ($coupon !== null && $coupon->type === 'gift' && $coupon->redemptions()->exists()) {
                $coupon = null;
            }

            if ($coupon !== null) {
                $vehicleOk = $coupon->vehicle_ids === null || $coupon->vehicle_ids === []
                    || in_array($car->id, $coupon->vehicle_ids, true);
                $minOk = $coupon->min_order_total_cents === null
                    || $subtotalBeforeDiscount >= $coupon->min_order_total_cents;
                $fromOk = $coupon->valid_from === null || now()->toDateString() >= $coupon->valid_from->toDateString();
                $toOk = $coupon->valid_to === null || now()->toDateString() <= $coupon->valid_to->toDateString();

                if ($vehicleOk && $minOk && $fromOk && $toOk) {
                    if ($coupon->discount_type === 'fixed' && $coupon->discount_fixed_cents !== null) {
                        $discountCents = min($subtotalBeforeDiscount, $coupon->discount_fixed_cents);
                    }
                    if ($coupon->discount_type === 'percentage' && $coupon->discount_percent_bips !== null) {
                        $discountCents = (int) floor($subtotalBeforeDiscount * $coupon->discount_percent_bips / 10000);
                    }
                    $couponId = $coupon->id;
                }
            }
        }

        $afterDiscount = max(0, $subtotalBeforeDiscount - $discountCents);
        $totalBeforeTax = $afterDiscount + $feesCents;

        $taxCents = $this->computeTaxCents(
            $baseRentalCents,
            $baseTaxBips,
            $extrasLines,
            $feesLines,
            $discountCents,
            $subtotalBeforeDiscount,
        );
        $totalCents = $totalBeforeTax + $taxCents;

        $car->loadMissing('host');
        $currency = $car->isOwnedByHost()
            ? PricingCurrency::forUser($car->host)
            : $this->shopCurrencyCode();

        return [
            'rental_days' => $rentalDays,
            'billable_days' => $pricing['billable_days'],
            'pricing_mode' => $pricing['pricing_mode'],
            'extra_hours_charged' => $pricing['extra_hours_charged'],
            'price_type_id' => $priceTypeId,
            'base_rental_cents' => $baseRentalCents,
            'rental_before_specials_cents' => $specialPricing['before_cents'],
            'special_discount_cents' => $specialPricing['discount_cents'],
            'special_surcharge_cents' => $specialPricing['surcharge_cents'],
            'special_prices_applied' => $specialPricing['applied'],
            'extras_cents' => $extrasCents,
            'extras_lines' => $extrasLines,
            'fees_cents' => $feesCents,
            'fees_lines' => $feesLines,
            'discount_cents' => $discountCents,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'currency' => $currency,
            'coupon_id' => $couponId,
        ];
    }

    private function resolveBaseRentalCharge(
        int $carId,
        int $priceTypeId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $durationMinutes,
    ): array {
        if ($durationMinutes < 1440) {
            $hourlyFare = HourlyFare::query()
                ->where('car_id', $carId)
                ->where('price_type_id', $priceTypeId)
                ->where('min_minutes', '<=', $durationMinutes)
                ->where('max_minutes', '>=', $durationMinutes)
                ->orderBy('min_minutes', 'desc')
                ->first();

            if ($hourlyFare !== null) {
                return [
                    'base_rental_cents' => (int) $hourlyFare->total_price_cents,
                    'billable_days' => 1,
                    'pricing_mode' => 'hourly',
                    'extra_hours_charged' => 0,
                ];
            }

            $dailyFare = $this->resolveDailyFare($carId, $priceTypeId, 1);
            if ($dailyFare === null) {
                throw new InvalidArgumentException('No hourly fare found and 1-day fallback fare is missing.');
            }

            return [
                'base_rental_cents' => (int) $dailyFare->price_per_day_cents,
                'billable_days' => 1,
                'pricing_mode' => 'hourly_fallback_to_daily',
                'extra_hours_charged' => 0,
            ];
        }

        $fullDays = max(1, (int) floor($durationMinutes / 1440));
        $extraMinutes = max(0, $durationMinutes - ($fullDays * 1440));
        $baseFare = $this->resolveDailyFare($carId, $priceTypeId, $fullDays);
        if ($baseFare === null) {
            throw new InvalidArgumentException('No daily fare configured for this vehicle, rate plan, and rental length.');
        }

        $baseRentalCents = $fullDays * (int) $baseFare->price_per_day_cents;
        $gratuityHours = max(0, (int) data_get(Setting::getValue('shop.extended_gratuity_period', ['hours' => 0]), 'hours', 0));
        $chargeableMinutes = max(0, $extraMinutes - ($gratuityHours * 60));

        if ($chargeableMinutes === 0) {
            return [
                'base_rental_cents' => $baseRentalCents,
                'billable_days' => $fullDays,
                'pricing_mode' => 'daily',
                'extra_hours_charged' => 0,
            ];
        }

        $extraHours = (int) ceil($chargeableMinutes / 60);
        $extraFare = ExtraHourFare::query()
            ->where('car_id', $carId)
            ->where('price_type_id', $priceTypeId)
            ->first();

        if ($extraFare !== null) {
            return [
                'base_rental_cents' => $baseRentalCents + ($extraHours * (int) $extraFare->charge_per_extra_hour_cents),
                'billable_days' => $fullDays,
                'pricing_mode' => 'daily_plus_extra_hours',
                'extra_hours_charged' => $extraHours,
            ];
        }

        $nextDayFare = $this->resolveDailyFare($carId, $priceTypeId, $fullDays + 1);
        if ($nextDayFare === null) {
            throw new InvalidArgumentException('No extra-hour fare defined and next-day fallback fare is missing.');
        }

        return [
            'base_rental_cents' => ($fullDays + 1) * (int) $nextDayFare->price_per_day_cents,
            'billable_days' => $fullDays + 1,
            'pricing_mode' => 'daily_fallback_next_day',
            'extra_hours_charged' => $extraHours,
        ];
    }

    private function resolveDailyFare(int $carId, int $priceTypeId, int $days): ?DailyFare
    {
        return DailyFare::query()
            ->where('car_id', $carId)
            ->where('price_type_id', $priceTypeId)
            ->where('from_days', '<=', $days)
            ->where('to_days', '>=', $days)
            ->orderBy('from_days', 'desc')
            ->first();
    }

    private function assertRestrictions(CarbonInterface $pickupAt, CarbonInterface $dropoffAt, int $rentalDays): void
    {
        $pickupDate = $pickupAt->toDateString();
        $dropoffDate = $dropoffAt->toDateString();

        $restrictions = BookingRestriction::query()
            ->where('is_active', true)
            ->where('date_from', '<=', $dropoffDate)
            ->where('date_to', '>=', $pickupDate)
            ->get();

        foreach ($restrictions as $r) {
            if ($r->min_rental_days !== null && $rentalDays < $r->min_rental_days) {
                throw new InvalidArgumentException("Rental must be at least {$r->min_rental_days} day(s) for the selected period.");
            }
            if ($r->max_rental_days !== null && $rentalDays > $r->max_rental_days) {
                throw new InvalidArgumentException("Rental may not exceed {$r->max_rental_days} day(s) for the selected period.");
            }

            $pickupDow = (int) $pickupAt->dayOfWeek;
            $dropoffDow = (int) $dropoffAt->dayOfWeek;

            if ($r->cta_weekdays !== null && $r->cta_weekdays !== [] && in_array($pickupDow, $r->cta_weekdays, true)) {
                throw new InvalidArgumentException('Pick-up is not available on the selected weekday for this period.');
            }
            if ($r->ctd_weekdays !== null && $r->ctd_weekdays !== [] && in_array($dropoffDow, $r->ctd_weekdays, true)) {
                throw new InvalidArgumentException('Drop-off is not available on the selected weekday for this period.');
            }
            if ($r->forced_pickup_weekdays !== null && $r->forced_pickup_weekdays !== []
                && ! in_array($pickupDow, $r->forced_pickup_weekdays, true)) {
                throw new InvalidArgumentException('Pick-up is only allowed on specific weekdays for this period.');
            }
        }
    }

    private function applySpecialPrices(
        int $baseRentalCents,
        int $carId,
        int $priceTypeId,
        int $pickupLocationId,
        int $dropoffLocationId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $rentalDays,
    ): array {
        $beforeCents = $baseRentalCents;
        $adjusted = $baseRentalCents;
        $applied = [];

        $specials = SpecialPrice::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        foreach ($specials as $sp) {
            if (! $this->specialMatches($sp, $carId, $priceTypeId, $pickupLocationId, $dropoffLocationId, $pickupAt, $dropoffAt)) {
                continue;
            }

            $previous = $adjusted;

            if ($sp->type === 'discount' && $sp->value_mode === 'percentage' && $sp->value_percent_bips !== null) {
                $adjusted -= (int) floor($adjusted * (int) $sp->value_percent_bips / 10000);
            }
            if ($sp->type === 'discount' && $sp->value_mode === 'fixed' && $sp->value_fixed_cents !== null) {
                $adjusted = max(0, $adjusted - (int) $sp->value_fixed_cents);
            }
            if ($sp->type === 'charge' && $sp->value_mode === 'fixed' && $sp->value_fixed_cents !== null) {
                $adjusted += (int) $sp->value_fixed_cents * $rentalDays;
            }
            if ($sp->type === 'charge' && $sp->value_mode === 'percentage' && $sp->value_percent_bips !== null) {
                $adjusted += (int) floor($adjusted * (int) $sp->value_percent_bips / 10000);
            }

            if ($sp->round_to_integer) {
                $adjusted = (int) round($adjusted);
            }

            $delta = $adjusted - $previous;
            if ($delta !== 0) {
                $applied[] = [
                    'name' => $sp->name,
                    'type' => $sp->type,
                    'is_promotion' => (bool) $sp->is_promotion,
                    'amount_cents' => abs($delta),
                    'direction' => $delta < 0 ? 'discount' : 'charge',
                ];
            }
        }

        $amountCents = max(0, $adjusted);

        return [
            'amount_cents' => $amountCents,
            'before_cents' => $beforeCents,
            'discount_cents' => max(0, $beforeCents - $amountCents),
            'surcharge_cents' => max(0, $amountCents - $beforeCents),
            'applied' => $applied,
        ];
    }

    private function specialMatches(
        SpecialPrice $sp,
        int $carId,
        int $priceTypeId,
        int $pickupLocationId,
        int $dropoffLocationId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
    ): bool {
        if ($sp->year !== null && (int) $pickupAt->year !== (int) $sp->year) {
            return false;
        }

        $pickupDate = $pickupAt->toDateString();
        $dropoffDate = $dropoffAt->toDateString();

        if ($sp->date_from !== null && $dropoffDate < $sp->date_from->toDateString()) {
            return false;
        }
        if ($sp->date_to !== null && $pickupDate > $sp->date_to->toDateString()) {
            return false;
        }

        if ($sp->vehicle_ids !== null && $sp->vehicle_ids !== [] && ! in_array($carId, $sp->vehicle_ids, true)) {
            return false;
        }
        if ($sp->price_type_ids !== null && $sp->price_type_ids !== []
            && ! in_array($priceTypeId, $sp->price_type_ids, true)) {
            return false;
        }
        if ($sp->pickup_location_ids !== null && $sp->pickup_location_ids !== []
            && ! in_array($pickupLocationId, $sp->pickup_location_ids, true)) {
            return false;
        }
        if ($sp->dropoff_location_ids !== null && $sp->dropoff_location_ids !== []
            && ! in_array($dropoffLocationId, $sp->dropoff_location_ids, true)) {
            return false;
        }

        if ($sp->weekdays !== null && $sp->weekdays !== []) {
            $pickupDow = (int) $pickupAt->dayOfWeek;
            if (! in_array($pickupDow, $sp->weekdays, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{0: int, 1: list<array{label: string, amount_cents: int}>}
     */
    private function computeFees(
        Car $car,
        int $pickupLocationId,
        int $dropoffLocationId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $rentalDays,
        int $defaultTaxBips,
    ): array {
        $feesCents = 0;
        $feesLines = [];
        $carId = $car->id;
        $isHostOwned = $car->isOwnedByHost();

        $locationFeeQuery = LocationFee::query()
            ->with(['pickupLocation', 'dropoffLocation'])
            ->where('is_active', true)
            ->where(function ($query) use ($pickupLocationId, $dropoffLocationId): void {
                $query->where(function ($q) use ($pickupLocationId, $dropoffLocationId): void {
                    $q->where('pickup_location_id', $pickupLocationId)
                        ->where('dropoff_location_id', $dropoffLocationId);
                })->orWhere(function ($q) use ($pickupLocationId, $dropoffLocationId): void {
                    $q->where('apply_inverted', true)
                        ->where('pickup_location_id', $dropoffLocationId)
                        ->where('dropoff_location_id', $pickupLocationId);
                });
            });

        if ($isHostOwned) {
            $locationFeeQuery->where('car_id', $carId);
        } else {
            $locationFeeQuery->whereNull('car_id');
        }

        $locationFees = $locationFeeQuery->orderBy('id')->get();

        if (! $isHostOwned && $pickupLocationId !== $dropoffLocationId && $locationFees->where('is_one_way_fee', true)->isEmpty()) {
            $globalOneWay = LocationFee::query()
                ->where('is_active', true)
                ->whereNull('car_id')
                ->where('is_one_way_fee', true)
                ->orderBy('id')
                ->first();

            if ($globalOneWay !== null && ! $locationFees->contains('id', $globalOneWay->id)) {
                $locationFees->push($globalOneWay);
            }
        }

        foreach ($locationFees as $fee) {
            if ($fee->is_one_way_fee && $pickupLocationId === $dropoffLocationId) {
                continue;
            }

            $baseCost = $this->resolveLocationFeeCostForRentalDays($fee, $rentalDays);
            $amount = $fee->multiply_by_days
                ? $baseCost * $rentalDays
                : $baseCost;

            if ($amount <= 0) {
                continue;
            }

            $feesCents += $amount;
            $pickupName = $fee->pickupLocation?->name ?? 'Pick-up';
            $dropoffName = $fee->dropoffLocation?->name ?? 'Drop-off';
            $label = $fee->is_one_way_fee
                ? "One-way fee ({$pickupName} → {$dropoffName})"
                : "Location fee ({$pickupName} → {$dropoffName})";
            $feesLines[] = [
                'label' => $label,
                'kind' => $fee->is_one_way_fee ? 'one_way_fee' : 'location_fee',
                'amount_cents' => $amount,
                'tax_bips' => $this->taxBipsForTaxRateId($fee->tax_rate_id, $defaultTaxBips),
            ];
        }

        $oohQuery = OutOfHoursFee::query()->where('is_active', true)->orderBy('id');
        if ($isHostOwned) {
            $oohQuery->whereJsonContains('vehicle_ids', $carId);
        }

        foreach ($oohQuery->cursor() as $ooh) {
            if (! $this->oohMatchesVehicle($ooh, $carId, $isHostOwned)) {
                continue;
            }

            $pickupCharge = (int) ($ooh->pickup_cost_cents ?? $ooh->cost_cents ?? 0);
            $dropoffCharge = (int) ($ooh->dropoff_cost_cents ?? $ooh->cost_cents ?? 0);
            $pickupAmount = 0;
            $dropoffAmount = 0;

            if (in_array($ooh->applies_to, ['pickup', 'both'], true) && $this->oohMatchesEndpoint($ooh, $pickupAt, $pickupLocationId)) {
                $pickupAmount = $pickupCharge;
            }
            if (in_array($ooh->applies_to, ['dropoff', 'both'], true) && $this->oohMatchesEndpoint($ooh, $dropoffAt, $dropoffLocationId)) {
                $dropoffAmount = $dropoffCharge;
            }

            $combined = $pickupAmount + $dropoffAmount;
            if ($combined <= 0) {
                continue;
            }

            if ($ooh->max_combined_charge_cents !== null && $combined > (int) $ooh->max_combined_charge_cents) {
                $cap = (int) $ooh->max_combined_charge_cents;
                $pickupAmount = (int) round($cap * ($pickupAmount / $combined));
                $dropoffAmount = $cap - $pickupAmount;
            }

            $oohName = ($ooh->name !== null && $ooh->name !== '') ? $ooh->name : 'Out-of-hours';
            $taxBips = $this->taxBipsForTaxRateId($ooh->tax_rate_id, $defaultTaxBips);

            if ($pickupAmount > 0) {
                $feesCents += $pickupAmount;
                $feesLines[] = [
                    'label' => "Out-of-hours pick-up: {$oohName}",
                    'kind' => 'out_of_hours_pickup',
                    'amount_cents' => $pickupAmount,
                    'tax_bips' => $taxBips,
                ];
            }

            if ($dropoffAmount > 0) {
                $feesCents += $dropoffAmount;
                $feesLines[] = [
                    'label' => "Out-of-hours drop-off: {$oohName}",
                    'kind' => 'out_of_hours_dropoff',
                    'amount_cents' => $dropoffAmount,
                    'tax_bips' => $taxBips,
                ];
            }
        }

        return [$feesCents, $feesLines];
    }

    private function oohMatchesVehicle(OutOfHoursFee $fee, int $carId, bool $isHostOwned = false): bool
    {
        if ($isHostOwned) {
            return in_array($carId, $fee->vehicle_ids ?? [], true);
        }

        if ($fee->vehicle_ids === null || $fee->vehicle_ids === []) {
            return true;
        }

        return in_array($carId, $fee->vehicle_ids, true);
    }

    private function oohMatchesEndpoint(OutOfHoursFee $fee, CarbonInterface $at, int $locationId): bool
    {
        if ($fee->location_ids !== null && $fee->location_ids !== []
            && ! in_array($locationId, $fee->location_ids, true)) {
            return false;
        }

        if ($fee->weekday_filter !== null && $fee->weekday_filter !== []
            && ! in_array((int) $at->dayOfWeek, $fee->weekday_filter, true)) {
            return false;
        }

        $minutes = $at->hour * 60 + $at->minute;
        $from = $this->timeStringToMinutes((string) $fee->time_from);
        $to = $this->timeStringToMinutes((string) $fee->time_to);

        return $this->minutesInWindow($minutes, $from, $to);
    }

    private function timeStringToMinutes(string $time): int
    {
        $time = substr($time, 0, 8);
        $parts = array_map('intval', explode(':', $time));

        return ($parts[0] ?? 0) * 60 + ($parts[1] ?? 0);
    }

    private function minutesInWindow(int $minutes, int $fromMin, int $toMin): bool
    {
        if ($fromMin <= $toMin) {
            return $minutes >= $fromMin && $minutes < $toMin;
        }

        return $minutes >= $fromMin || $minutes < $toMin;
    }

    private function resolveLocationFeeCostForRentalDays(LocationFee $fee, int $rentalDays): int
    {
        $overrides = $fee->day_overrides;
        if (is_array($overrides) && array_key_exists((string) $rentalDays, $overrides)) {
            return max(0, (int) $overrides[(string) $rentalDays]);
        }

        return (int) $fee->cost_cents;
    }

    private function computeTaxCents(
        int $baseRentalCents,
        int $baseTaxBips,
        array $extrasLines,
        array $feesLines,
        int $discountCents,
        int $subtotalBeforeDiscount,
    ): int {
        $lines = [];
        $lines[] = ['amount_cents' => $baseRentalCents, 'tax_bips' => $baseTaxBips, 'discountable' => true];

        foreach ($extrasLines as $line) {
            $lines[] = [
                'amount_cents' => (int) $line['total_cents'],
                'tax_bips' => (int) ($line['tax_bips'] ?? 0),
                'discountable' => true,
            ];
        }
        foreach ($feesLines as $line) {
            $lines[] = [
                'amount_cents' => (int) $line['amount_cents'],
                'tax_bips' => (int) ($line['tax_bips'] ?? 0),
                'discountable' => false,
            ];
        }

        $discountableIndexes = [];
        foreach ($lines as $idx => $line) {
            if ($line['discountable']) {
                $discountableIndexes[] = $idx;
            }
        }

        $remainingDiscount = min(max(0, $discountCents), max(0, $subtotalBeforeDiscount));
        $lastDiscountableIdx = end($discountableIndexes);

        foreach ($discountableIndexes as $idx) {
            $amount = max(0, (int) $lines[$idx]['amount_cents']);
            if ($remainingDiscount <= 0 || $amount <= 0) {
                $lines[$idx]['discount_share'] = 0;
                continue;
            }

            if ($idx === $lastDiscountableIdx) {
                $share = min($remainingDiscount, $amount);
            } else {
                $share = min($amount, (int) floor($discountCents * ($amount / max(1, $subtotalBeforeDiscount))));
            }

            $lines[$idx]['discount_share'] = $share;
            $remainingDiscount -= $share;
        }

        if ($remainingDiscount > 0 && $lastDiscountableIdx !== false) {
            $lines[$lastDiscountableIdx]['discount_share'] = min(
                (int) $lines[$lastDiscountableIdx]['amount_cents'],
                (int) (($lines[$lastDiscountableIdx]['discount_share'] ?? 0) + $remainingDiscount)
            );
        }

        $taxTotal = 0;

        foreach ($lines as $line) {
            $amount = (int) $line['amount_cents'];
            if ($amount <= 0) {
                continue;
            }

            $discountShare = (int) ($line['discount_share'] ?? 0);
            $taxableAmount = max(0, $amount - $discountShare);
            $taxTotal += (int) floor($taxableAmount * (int) $line['tax_bips'] / 10000);
        }

        return $taxTotal;
    }

    /**
     * Accepts either a list of option IDs ([1, 2]) or a map of id => quantity ([1 => 2]).
     *
     * @return array<int, int>
     */
    private function normalizeRentalOptionSelections(array $selections): array
    {
        if ($selections === []) {
            return [];
        }

        $normalized = [];

        if (array_is_list($selections)) {
            foreach ($selections as $id) {
                $optionId = (int) $id;
                if ($optionId > 0) {
                    $normalized[$optionId] = ($normalized[$optionId] ?? 0) + 1;
                }
            }

            return $normalized;
        }

        foreach ($selections as $optionId => $quantity) {
            $optionId = (int) $optionId;
            if ($optionId > 0) {
                $normalized[$optionId] = max(1, (int) $quantity);
            }
        }

        return $normalized;
    }

    private function defaultTaxBips(): int
    {
        return (int) data_get(Setting::getValue('shop.default_tax', ['basis_points' => 0]), 'basis_points', 0);
    }

    private function shopCurrencyCode(): string
    {
        return PricingCurrency::shopDefault();
    }

    private function taxBipsForTaxRateId(?int $taxRateId, int $defaultTaxBips): int
    {
        if ($taxRateId === null) {
            return $defaultTaxBips;
        }

        $rate = TaxRate::query()->find($taxRateId);
        if ($rate === null) {
            return $defaultTaxBips;
        }

        return max(0, (int) $rate->basis_points);
    }
}
