<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\SpecialPrice;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FaresOverview extends Page
{
    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.fares-overview';

    protected static ?string $title = 'Fares Overview';

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $slug = 'fares-overview';

    /** @var array<int, array{id:int,name:string}> */
    public array $carOptions = [];

    /** @var array<int, int> */
    public array $selectedCarIds = [];

    public ?int $calculatorCarId = null;

    public string $pickupDate = '';

    public int $rentalDays = 1;

    public string $periodFrom = '';

    public string $periodTo = '';

    public string $currencyCode = 'EUR';

    /** @var array<int, string> */
    public array $displayDates = [];

    /** @var array<int, array{id:int,name:string,units_available:int,prices:array<int,int>}> */
    public array $overviewRows = [];

    public function mount(): void
    {
        $cars = Car::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'units_available']);

        $this->carOptions = $cars
            ->map(fn (Car $car): array => ['id' => (int) $car->id, 'name' => (string) $car->name])
            ->all();

        $this->selectedCarIds = $cars->take(2)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $this->calculatorCarId = $cars->first()?->id;

        $today = now()->toDateString();
        $this->pickupDate = $today;
        $this->periodFrom = $today;
        $this->periodTo = now()->addDays(11)->toDateString();
        $this->currencyCode = (string) data_get(Setting::getValue('shop.currency', ['code' => 'EUR']), 'code', 'EUR');

        $this->calculateRates();
    }

    public function calculateRates(): void
    {
        $from = Carbon::parse($this->periodFrom)->startOfDay();
        $to = Carbon::parse($this->periodTo)->startOfDay();

        if ($to->lessThan($from)) {
            $to = $from->copy();
            $this->periodTo = $to->toDateString();
        }

        $this->rentalDays = max(1, (int) $this->rentalDays);

        if ($this->calculatorCarId !== null && ! in_array($this->calculatorCarId, $this->selectedCarIds, true)) {
            $this->selectedCarIds[] = (int) $this->calculatorCarId;
        }

        $this->displayDates = [];
        $cursor = $from->copy();
        while ($cursor->lessThanOrEqualTo($to)) {
            $this->displayDates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $this->displayDates = array_slice($this->displayDates, 0, 21);

        if ($this->selectedCarIds === [] || $this->displayDates === []) {
            $this->overviewRows = [];

            return;
        }

        $cars = Car::query()
            ->whereIn('id', $this->selectedCarIds)
            ->orderBy('name')
            ->get(['id', 'name', 'units_available']);

        $priceTypeId = PriceType::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        $specials = SpecialPrice::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $dailyFareMap = DailyFare::query()
            ->whereIn('car_id', $cars->pluck('id')->all())
            ->where('from_days', '<=', $this->rentalDays)
            ->where('to_days', '>=', $this->rentalDays)
            ->orderByDesc('from_days')
            ->get()
            ->groupBy('car_id')
            ->map(fn ($group): ?DailyFare => $group->first());

        $rows = [];

        foreach ($cars as $car) {
            $baseCents = (int) ($dailyFareMap->get($car->id)?->price_per_day_cents ?? 0);
            $prices = [];

            foreach ($this->displayDates as $dateString) {
                $pickupAt = Carbon::parse($dateString)->startOfDay();
                $dropoffAt = $pickupAt->copy()->addDays($this->rentalDays);

                $prices[] = $this->applyOverviewSpecials(
                    $baseCents,
                    $specials->all(),
                    (int) $car->id,
                    $priceTypeId !== null ? (int) $priceTypeId : null,
                    $pickupAt,
                    $dropoffAt
                );
            }

            $rows[] = [
                'id' => (int) $car->id,
                'name' => (string) $car->name,
                'units_available' => (int) $car->units_available,
                'prices' => $prices,
            ];
        }

        $this->overviewRows = $rows;
    }

    private function applyOverviewSpecials(
        int $baseCents,
        array $specials,
        int $carId,
        ?int $priceTypeId,
        Carbon $pickupAt,
        Carbon $dropoffAt,
    ): int {
        $adjusted = $baseCents;

        foreach ($specials as $sp) {
            if ($sp->vehicle_ids !== null && $sp->vehicle_ids !== [] && ! in_array($carId, $sp->vehicle_ids, true)) {
                continue;
            }

            if ($sp->price_type_ids !== null && $sp->price_type_ids !== [] && $priceTypeId !== null
                && ! in_array($priceTypeId, $sp->price_type_ids, true)) {
                continue;
            }

            if (($sp->pickup_location_ids !== null && $sp->pickup_location_ids !== [])
                || ($sp->dropoff_location_ids !== null && $sp->dropoff_location_ids !== [])) {
                continue;
            }

            if ($sp->year !== null && (int) $sp->year !== (int) $pickupAt->year) {
                continue;
            }

            if ($sp->date_from !== null && $dropoffAt->toDateString() < $sp->date_from->toDateString()) {
                continue;
            }

            if ($sp->date_to !== null && $pickupAt->toDateString() > $sp->date_to->toDateString()) {
                continue;
            }

            if ($sp->weekdays !== null && $sp->weekdays !== []
                && ! in_array((int) $pickupAt->dayOfWeek, $sp->weekdays, true)) {
                continue;
            }

            if ($sp->type === 'discount' && $sp->value_mode === 'percentage' && $sp->value_percent_bips !== null) {
                $adjusted -= (int) floor($adjusted * (int) $sp->value_percent_bips / 10000);
            }
            if ($sp->type === 'discount' && $sp->value_mode === 'fixed' && $sp->value_fixed_cents !== null) {
                $adjusted = max(0, $adjusted - (int) $sp->value_fixed_cents);
            }
            if ($sp->type === 'charge' && $sp->value_mode === 'fixed' && $sp->value_fixed_cents !== null) {
                $adjusted += (int) $sp->value_fixed_cents;
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
}
