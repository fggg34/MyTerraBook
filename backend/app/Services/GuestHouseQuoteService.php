<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\GuestHouse;
use App\Models\GuestHouseSeasonalPrice;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Support\Money;
use Carbon\Carbon;
use InvalidArgumentException;

class GuestHouseQuoteService
{
    public function __construct(
        private readonly GuestHouseAvailabilityService $availabilityService,
    ) {}

    /**
     * @return array{
     *   nights: int,
     *   nightly_breakdown: list<array{date: string, price_cents: int}>,
     *   base_total: int,
     *   cleaning_fee: int,
     *   security_deposit: int,
     *   discount_amount: int,
     *   tax_amount: int,
     *   total_amount: int,
     *   currency: string,
     *   coupon_id: int|null,
     *   total_formatted: string,
     *   base_total_formatted: string
     * }
     */
    public function quote(
        GuestHouse $house,
        string $checkIn,
        string $checkOut,
        int $guests,
        ?string $couponCode = null,
    ): array {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();

        if ($checkOutDate->lte($checkInDate)) {
            throw new InvalidArgumentException('Check-out must be after check-in.');
        }

        $nights = (int) $checkInDate->diffInDays($checkOutDate);

        if ($nights < $house->min_nights) {
            throw new InvalidArgumentException("Minimum stay is {$house->min_nights} night(s).");
        }

        if ($house->max_nights !== null && $nights > $house->max_nights) {
            throw new InvalidArgumentException("Maximum stay is {$house->max_nights} night(s).");
        }

        if ($guests > $house->max_guests) {
            throw new InvalidArgumentException("Maximum {$house->max_guests} guests allowed.");
        }

        $this->assertSeasonalMinimumNights($house, $checkIn, $checkOut, $nights);

        if (! $this->availabilityService->isAvailable($house, $checkIn, $checkOut)) {
            throw new InvalidArgumentException('Selected dates are not available.');
        }

        $pricing = $house->getPriceForPeriod($checkIn, $checkOut);
        $baseTotal = array_sum(array_column($pricing['nightly_breakdown'], 'price_cents'));
        $cleaningFee = (int) ($house->cleaning_fee ?? 0);
        $securityDeposit = (int) ($house->security_deposit ?? 0);

        $subtotalBeforeDiscount = $baseTotal + $cleaningFee;
        $discountCents = 0;
        $couponId = null;

        if ($couponCode !== null && trim($couponCode) !== '') {
            [$discountCents, $couponId] = $this->resolveCouponDiscount(
                trim($couponCode),
                $subtotalBeforeDiscount,
            );
        }

        $taxable = max(0, $baseTotal + $cleaningFee - $discountCents);
        $taxBips = $this->resolveTaxBips($house);
        $taxAmount = (int) floor($taxable * $taxBips / 10000);
        $totalAmount = $taxable + $taxAmount;

        $currency = $this->currencyCode();

        return [
            'nights' => $nights,
            'nightly_breakdown' => $pricing['nightly_breakdown'],
            'base_total' => $baseTotal,
            'cleaning_fee' => $cleaningFee,
            'security_deposit' => $securityDeposit,
            'discount_amount' => $discountCents,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'coupon_id' => $couponId,
            'total_formatted' => '€ '.Money::formatDecimalFromCents($totalAmount),
            'base_total_formatted' => '€ '.Money::formatDecimalFromCents($baseTotal),
        ];
    }

    private function assertSeasonalMinimumNights(
        GuestHouse $house,
        string $checkIn,
        string $checkOut,
        int $nights,
    ): void {
        $rules = GuestHouseSeasonalPrice::query()
            ->where('guest_house_id', $house->id)
            ->where('date_from', '<=', $checkOut)
            ->where('date_to', '>=', $checkIn)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->minimum_nights !== null && $nights < $rule->minimum_nights) {
                throw new InvalidArgumentException(
                    "Seasonal rule \"{$rule->name}\" requires at least {$rule->minimum_nights} night(s).",
                );
            }
        }
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    private function resolveCouponDiscount(string $code, int $subtotalBeforeDiscount): array
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if ($coupon === null) {
            return [0, null];
        }

        if ($coupon->type === 'gift' && $coupon->redemptions()->exists()) {
            return [0, null];
        }

        $vehicleOk = $coupon->vehicle_ids === null || $coupon->vehicle_ids === [];
        $minOk = $coupon->min_order_total_cents === null
            || $subtotalBeforeDiscount >= $coupon->min_order_total_cents;
        $fromOk = $coupon->valid_from === null || now()->toDateString() >= $coupon->valid_from->toDateString();
        $toOk = $coupon->valid_to === null || now()->toDateString() <= $coupon->valid_to->toDateString();

        if (! $vehicleOk || ! $minOk || ! $fromOk || ! $toOk) {
            return [0, null];
        }

        $discount = 0;
        if ($coupon->discount_type === 'fixed' && $coupon->discount_fixed_cents !== null) {
            $discount = min($subtotalBeforeDiscount, $coupon->discount_fixed_cents);
        }
        if ($coupon->discount_type === 'percentage' && $coupon->discount_percent_bips !== null) {
            $discount = (int) floor($subtotalBeforeDiscount * $coupon->discount_percent_bips / 10000);
        }

        return [$discount, $coupon->id];
    }

    private function resolveTaxBips(GuestHouse $house): int
    {
        if ($house->tax_rate_id !== null) {
            $rate = TaxRate::query()->find($house->tax_rate_id);

            return $rate?->basis_points ?? $this->defaultTaxBips();
        }

        return $this->defaultTaxBips();
    }

    private function defaultTaxBips(): int
    {
        $tax = Setting::getValue('shop.default_tax', ['basis_points' => 0]);

        return (int) ($tax['basis_points'] ?? 0);
    }

    private function currencyCode(): string
    {
        $shop = Setting::getValue('shop.currency', ['code' => 'EUR']);

        return (string) ($shop['code'] ?? 'EUR');
    }
}
