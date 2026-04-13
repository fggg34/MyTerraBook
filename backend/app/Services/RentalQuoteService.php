<?php

namespace App\Services;

use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\Coupon;
use App\Models\DailyFare;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\RentalOption;
use App\Models\Setting;
use App\Models\SpecialPrice;
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

        $rentalDays = max(1, (int) ceil($pickupAt->diffInMinutes($dropoffAt) / 1440));

        $this->assertRestrictions($pickupAt, $dropoffAt, $rentalDays);

        $dailyFare = DailyFare::query()
            ->where('car_id', $car->id)
            ->where('price_type_id', $priceTypeId)
            ->where('from_days', '<=', $rentalDays)
            ->where('to_days', '>=', $rentalDays)
            ->first();

        if ($dailyFare === null) {
            throw new InvalidArgumentException('No daily fare configured for this vehicle, rate plan, and rental length.');
        }

        $baseRentalCents = $rentalDays * $dailyFare->price_per_day_cents;
        $baseRentalCents = $this->applySpecialPrices(
            $baseRentalCents,
            $car->id,
            $pickupLocationId,
            $dropoffLocationId,
            $pickupAt,
            $dropoffAt,
            $rentalDays,
        );

        $extrasCents = 0;
        $extrasLines = [];

        if ($rentalOptionSelections !== []) {
            $optionIds = array_keys($rentalOptionSelections);
            $options = RentalOption::query()
                ->whereIn('id', $optionIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($rentalOptionSelections as $optionId => $quantity) {
                $optionId = (int) $optionId;
                $quantity = max(1, (int) $quantity);
                $option = $options->get($optionId);
                if ($option === null) {
                    continue;
                }

                if (! $car->rentalOptions()->whereKey($optionId)->exists()) {
                    throw new InvalidArgumentException("Add-on {$option->name} is not available for this vehicle.");
                }

                $line = $option->is_daily_cost
                    ? $quantity * $option->cost_cents * $rentalDays
                    : $quantity * $option->cost_cents;

                if ($option->max_cost_cap_cents !== null) {
                    $line = min($line, $option->max_cost_cap_cents);
                }

                $extrasCents += $line;
                $unitPriceCents = $option->is_daily_cost ? $option->cost_cents : $option->cost_cents;

                $extrasLines[] = [
                    'rental_option_id' => $option->id,
                    'name' => $option->name,
                    'quantity' => $quantity,
                    'unit_price_cents' => (int) $unitPriceCents,
                    'total_cents' => $line,
                ];
            }
        }

        [$feesCents, $feesLines] = $this->computeFees(
            $car->id,
            $pickupLocationId,
            $dropoffLocationId,
            $pickupAt,
            $dropoffAt,
            $rentalDays,
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

        $taxBips = (int) (Setting::getValue('shop.default_tax', ['basis_points' => 0])['basis_points'] ?? 0);
        $taxCents = (int) floor($totalBeforeTax * $taxBips / 10000);
        $totalCents = $totalBeforeTax + $taxCents;

        $currency = (string) (Setting::getValue('shop.currency', ['code' => 'EUR'])['code'] ?? 'EUR');

        return [
            'rental_days' => $rentalDays,
            'price_type_id' => $priceTypeId,
            'base_rental_cents' => $baseRentalCents,
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
        int $pickupLocationId,
        int $dropoffLocationId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $rentalDays,
    ): int {
        $adjusted = $baseRentalCents;

        $specials = SpecialPrice::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        foreach ($specials as $sp) {
            if (! $this->specialMatches($sp, $carId, $pickupLocationId, $dropoffLocationId, $pickupAt, $dropoffAt)) {
                continue;
            }

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
        }

        return max(0, $adjusted);
    }

    private function specialMatches(
        SpecialPrice $sp,
        int $carId,
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
        int $carId,
        int $pickupLocationId,
        int $dropoffLocationId,
        CarbonInterface $pickupAt,
        CarbonInterface $dropoffAt,
        int $rentalDays,
    ): array {
        $feesCents = 0;
        $feesLines = [];

        $locationFees = LocationFee::query()
            ->where('is_active', true)
            ->where('pickup_location_id', $pickupLocationId)
            ->where('dropoff_location_id', $dropoffLocationId)
            ->get();

        foreach ($locationFees as $fee) {
            if ($fee->is_one_way_fee && $pickupLocationId === $dropoffLocationId) {
                continue;
            }

            $amount = $fee->multiply_by_days
                ? (int) $fee->cost_cents * $rentalDays
                : (int) $fee->cost_cents;

            if ($amount <= 0) {
                continue;
            }

            $feesCents += $amount;
            $label = $fee->is_one_way_fee ? 'One-way fee' : 'Location fee';
            $feesLines[] = [
                'label' => $label,
                'amount_cents' => $amount,
            ];
        }

        foreach (OutOfHoursFee::query()->where('is_active', true)->orderBy('id')->cursor() as $ooh) {
            if (! $this->oohMatchesVehicle($ooh, $carId)) {
                continue;
            }

            $sumForRule = 0;

            if (in_array($ooh->applies_to, ['pickup', 'both'], true) && $this->oohMatchesEndpoint($ooh, $pickupAt, $pickupLocationId)) {
                $sumForRule += (int) $ooh->cost_cents;
            }
            if (in_array($ooh->applies_to, ['dropoff', 'both'], true) && $this->oohMatchesEndpoint($ooh, $dropoffAt, $dropoffLocationId)) {
                $sumForRule += (int) $ooh->cost_cents;
            }

            if ($ooh->max_combined_charge_cents !== null) {
                $sumForRule = min($sumForRule, (int) $ooh->max_combined_charge_cents);
            }

            if ($sumForRule > 0) {
                $feesCents += $sumForRule;
                $feesLines[] = [
                    'label' => 'Out-of-hours fee #'.$ooh->id,
                    'amount_cents' => $sumForRule,
                ];
            }
        }

        return [$feesCents, $feesLines];
    }

    private function oohMatchesVehicle(OutOfHoursFee $fee, int $carId): bool
    {
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
}
