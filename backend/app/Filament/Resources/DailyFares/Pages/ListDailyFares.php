<?php

namespace App\Filament\Resources\DailyFares\Pages;

use App\Filament\Resources\DailyFares\DailyFareResource;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\PriceType;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Html as SchemaHtml;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class ListDailyFares extends ListRecords
{
    protected static string $resource = DailyFareResource::class;

    #[Url]
    public ?int $benchCarId = null;

    #[Url]
    public ?int $benchPriceTypeId = null;

    /** @var 'daily'|'hourly'|'extra_hours' */
    #[Url]
    public string $fareTab = 'daily';

    public ?int $insertFromDays = 1;

    public ?int $insertToDays = 1;

    public ?string $insertPriceIsk = null;

    public int $benchDayPage = 1;

    public int $benchPerPage = 15;

    /** @var array<int, int> */
    public array $selectedBenchDays = [];

    public ?int $insertHourlyFromHours = 1;

    public ?int $insertHourlyToHours = 2;

    public ?string $insertHourlyTotalIsk = null;

    public int $benchHourPage = 1;

    public int $benchHourPerPage = 15;

    /** @var array<int, int> */
    public array $selectedBenchHours = [];

    public ?string $extraHourChargeIsk = null;

    public bool $showUpdateFaresModal = false;

    public ?string $bulkUpdatePriceIsk = null;

    public function mount(): void
    {
        parent::mount();

        if ($this->benchCarId === null) {
            $firstCarId = Car::query()->orderBy('name')->value('id');
            $this->benchCarId = $firstCarId !== null ? (int) $firstCarId : null;
        }
        if ($this->benchPriceTypeId === null) {
            $firstPtId = PriceType::query()->orderBy('name')->value('id');
            $this->benchPriceTypeId = $firstPtId !== null ? (int) $firstPtId : null;
        }

        $this->normalizeFareTab();
        $this->loadExtraHourChargeForm();
    }

    public function updatedFareTab(): void
    {
        $this->normalizeFareTab();
        $this->benchDayPage = 1;
        $this->benchHourPage = 1;
        $this->selectedBenchDays = [];
        $this->selectedBenchHours = [];
        $this->showUpdateFaresModal = false;
        $this->loadExtraHourChargeForm();
    }

    public function updatedBenchCarId(): void
    {
        $this->benchDayPage = 1;
        $this->benchHourPage = 1;
        $this->selectedBenchDays = [];
        $this->selectedBenchHours = [];
        $this->loadExtraHourChargeForm();
    }

    public function updatedBenchPriceTypeId(): void
    {
        $this->benchDayPage = 1;
        $this->benchHourPage = 1;
        $this->selectedBenchDays = [];
        $this->selectedBenchHours = [];
        $this->loadExtraHourChargeForm();
    }

    public function setBenchDayPage(int $page): void
    {
        $this->benchDayPage = max(1, $page);
    }

    public function setBenchHourPage(int $page): void
    {
        $this->benchHourPage = max(1, $page);
    }

    public function insertFareBand(): void
    {
        if (! $this->ensureBenchContextReady()) {
            return;
        }

        $data = $this->validateWithNotification([
            'benchCarId' => $this->benchCarId,
            'benchPriceTypeId' => $this->benchPriceTypeId,
            'insertFromDays' => $this->insertFromDays,
            'insertToDays' => $this->insertToDays,
            'insertPriceIsk' => $this->insertPriceIsk,
        ], [
            'benchCarId' => ['required', 'integer', 'exists:cars,id'],
            'benchPriceTypeId' => ['required', 'integer', 'exists:price_types,id'],
            'insertFromDays' => ['required', 'integer', 'min:1'],
            'insertToDays' => ['required', 'integer', 'min:1', 'gte:insertFromDays'],
            'insertPriceIsk' => ['required', 'numeric', 'min:0'],
        ]);

        if ($data === null) {
            return;
        }

        $cents = (int) round(((float) $data['insertPriceIsk']) * 100);

        DailyFare::query()->create([
            'car_id' => $data['benchCarId'],
            'price_type_id' => $data['benchPriceTypeId'],
            'from_days' => (int) $data['insertFromDays'],
            'to_days' => (int) $data['insertToDays'],
            'price_per_day_cents' => $cents,
        ]);

        Notification::make()
            ->title('Daily fare inserted')
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    public function insertHourlyFareBand(): void
    {
        if (! $this->ensureBenchContextReady()) {
            return;
        }

        $data = $this->validateWithNotification([
            'benchCarId' => $this->benchCarId,
            'benchPriceTypeId' => $this->benchPriceTypeId,
            'insertHourlyFromHours' => $this->insertHourlyFromHours,
            'insertHourlyToHours' => $this->insertHourlyToHours,
            'insertHourlyTotalIsk' => $this->insertHourlyTotalIsk,
        ], [
            'benchCarId' => ['required', 'integer', 'exists:cars,id'],
            'benchPriceTypeId' => ['required', 'integer', 'exists:price_types,id'],
            'insertHourlyFromHours' => ['required', 'integer', 'min:1', 'max:72'],
            'insertHourlyToHours' => ['required', 'integer', 'min:1', 'max:72', 'gte:insertHourlyFromHours'],
            'insertHourlyTotalIsk' => ['required', 'numeric', 'min:0'],
        ]);

        if ($data === null) {
            return;
        }

        $minMinutes = (int) $data['insertHourlyFromHours'] * 60;
        $maxMinutes = (int) $data['insertHourlyToHours'] * 60;
        $cents = (int) round(((float) $data['insertHourlyTotalIsk']) * 100);

        HourlyFare::query()->create([
            'car_id' => $data['benchCarId'],
            'price_type_id' => $data['benchPriceTypeId'],
            'min_minutes' => $minMinutes,
            'max_minutes' => $maxMinutes,
            'total_price_cents' => $cents,
        ]);

        Notification::make()
            ->title('Hourly fare inserted')
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    public function saveExtraHourFare(): void
    {
        if (! $this->ensureBenchContextReady()) {
            return;
        }

        $data = $this->validateWithNotification([
            'benchCarId' => $this->benchCarId,
            'benchPriceTypeId' => $this->benchPriceTypeId,
            'extraHourChargeIsk' => $this->extraHourChargeIsk,
        ], [
            'benchCarId' => ['required', 'integer', 'exists:cars,id'],
            'benchPriceTypeId' => ['required', 'integer', 'exists:price_types,id'],
            'extraHourChargeIsk' => ['required', 'numeric', 'min:0'],
        ]);

        if ($data === null) {
            return;
        }

        $cents = (int) round(((float) $data['extraHourChargeIsk']) * 100);

        ExtraHourFare::query()->updateOrCreate(
            [
                'car_id' => (int) $data['benchCarId'],
                'price_type_id' => (int) $data['benchPriceTypeId'],
            ],
            [
                'charge_per_extra_hour_cents' => $cents,
            ]
        );

        Notification::make()
            ->title('Extra hour charge saved')
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    public function openUpdateFaresModal(): void
    {
        if ($this->fareTab === 'extra_hours') {
            return;
        }

        if ($this->fareTab === 'daily' && count($this->selectedBenchDays) < 1) {
            Notification::make()
                ->title('Select at least one day row')
                ->warning()
                ->send();

            return;
        }

        if ($this->fareTab === 'hourly' && count($this->selectedBenchHours) < 1) {
            Notification::make()
                ->title('Select at least one duration row')
                ->warning()
                ->send();

            return;
        }

        $this->bulkUpdatePriceIsk = null;
        $this->showUpdateFaresModal = true;
    }

    public function closeUpdateFaresModal(): void
    {
        $this->showUpdateFaresModal = false;
    }

    public function applyBulkFareUpdateFromModal(): void
    {
        if ($this->fareTab === 'hourly') {
            $this->applyBulkUpdateHourlyFares();

            return;
        }

        $this->applyBulkUpdateFares();
    }

    protected function applyBulkUpdateFares(): void
    {
        if (! $this->ensureBenchContextReady()) {
            return;
        }

        $data = $this->validateWithNotification([
            'bulkUpdatePriceIsk' => $this->bulkUpdatePriceIsk,
            'selectedBenchDays' => $this->selectedBenchDays,
            'benchCarId' => $this->benchCarId,
            'benchPriceTypeId' => $this->benchPriceTypeId,
        ], [
            'bulkUpdatePriceIsk' => ['required', 'numeric', 'min:0'],
            'selectedBenchDays' => ['required', 'array', 'min:1'],
            'selectedBenchDays.*' => ['integer', 'min:1', 'max:366'],
            'benchCarId' => ['required', 'integer'],
            'benchPriceTypeId' => ['required', 'integer'],
        ]);

        if ($data === null) {
            return;
        }

        $perDayCents = (int) round(((float) $data['bulkUpdatePriceIsk']) * 100);
        $days = array_map('intval', $data['selectedBenchDays']);
        sort($days);
        $from = min($days);
        $to = max($days);

        DailyFare::query()->create([
            'car_id' => (int) $data['benchCarId'],
            'price_type_id' => (int) $data['benchPriceTypeId'],
            'from_days' => $from,
            'to_days' => $to,
            'price_per_day_cents' => $perDayCents,
        ]);

        $this->selectedBenchDays = [];
        $this->showUpdateFaresModal = false;
        $this->dispatch('$refresh');
    }

    protected function applyBulkUpdateHourlyFares(): void
    {
        if (! $this->ensureBenchContextReady()) {
            return;
        }

        $data = $this->validateWithNotification([
            'bulkUpdatePriceIsk' => $this->bulkUpdatePriceIsk,
            'selectedBenchHours' => $this->selectedBenchHours,
            'benchCarId' => $this->benchCarId,
            'benchPriceTypeId' => $this->benchPriceTypeId,
        ], [
            'bulkUpdatePriceIsk' => ['required', 'numeric', 'min:0'],
            'selectedBenchHours' => ['required', 'array', 'min:1'],
            'selectedBenchHours.*' => ['integer', 'min:1', 'max:72'],
            'benchCarId' => ['required', 'integer'],
            'benchPriceTypeId' => ['required', 'integer'],
        ]);

        if ($data === null) {
            return;
        }

        $hours = array_map('intval', $data['selectedBenchHours']);
        sort($hours);
        $fromHours = min($hours);
        $toHours = max($hours);
        $minMinutes = $fromHours * 60;
        $maxMinutes = $toHours * 60;
        $totalCents = (int) round(((float) $data['bulkUpdatePriceIsk']) * 100);

        HourlyFare::query()->create([
            'car_id' => (int) $data['benchCarId'],
            'price_type_id' => (int) $data['benchPriceTypeId'],
            'min_minutes' => $minMinutes,
            'max_minutes' => $maxMinutes,
            'total_price_cents' => $totalCents,
        ]);

        $this->selectedBenchHours = [];
        $this->showUpdateFaresModal = false;
        $this->dispatch('$refresh');
    }

    /**
     * @return list<array{day: int, total_cents: int}>
     */
    public function getBenchDayRowsProperty(): array
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            return [];
        }

        $maxDay = 60;
        $rows = [];
        for ($day = 1; $day <= $maxDay; $day++) {
            $fare = $this->resolveDailyFareForBench($day);
            $perDay = $fare !== null ? (int) $fare->price_per_day_cents : 0;
            $rows[] = [
                'day' => $day,
                'total_cents' => $day * $perDay,
            ];
        }

        $offset = ($this->benchDayPage - 1) * $this->benchPerPage;

        return array_slice($rows, $offset, $this->benchPerPage);
    }

    public function getBenchDayTotalProperty(): int
    {
        return 60;
    }

    public function getBenchDayTotalPagesProperty(): int
    {
        return (int) max(1, (int) ceil($this->benchDayTotal / $this->benchPerPage));
    }

    /**
     * @return list<array{hour: int, duration_minutes: int, total_cents: int}>
     */
    public function getBenchHourRowsProperty(): array
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            return [];
        }

        $rows = [];
        for ($hour = 1; $hour <= $this->benchHourTotal; $hour++) {
            $durationMinutes = $hour * 60;
            $fare = $this->resolveHourlyFareForBench($durationMinutes);
            $totalCents = $fare !== null ? (int) $fare->total_price_cents : 0;
            $rows[] = [
                'hour' => $hour,
                'duration_minutes' => $durationMinutes,
                'total_cents' => $totalCents,
            ];
        }

        $offset = ($this->benchHourPage - 1) * $this->benchHourPerPage;

        return array_slice($rows, $offset, $this->benchHourPerPage);
    }

    public function getBenchHourTotalProperty(): int
    {
        return 24;
    }

    public function getBenchHourTotalPagesProperty(): int
    {
        return (int) max(1, (int) ceil($this->benchHourTotal / $this->benchHourPerPage));
    }

    /**
     * @return list<array{extra_hours: int, total_cents: int}>
     */
    public function getBenchExtraHourRowsProperty(): array
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            return [];
        }

        $fare = ExtraHourFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->first();

        $perHourCents = $fare !== null ? (int) $fare->charge_per_extra_hour_cents : 0;
        $rows = [];
        for ($h = 1; $h <= 12; $h++) {
            $rows[] = [
                'extra_hours' => $h,
                'total_cents' => $h * $perHourCents,
            ];
        }

        return $rows;
    }

    /**
     * @return Collection<int, HourlyFare>
     */
    public function getHourlyBandRecordsProperty(): Collection
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            return collect();
        }

        return HourlyFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->orderBy('min_minutes')
            ->get();
    }

    public function getExtraHourBandRecordProperty(): ?ExtraHourFare
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            return null;
        }

        return ExtraHourFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->first();
    }

    private function normalizeFareTab(): void
    {
        if (! in_array($this->fareTab, ['daily', 'hourly', 'extra_hours'], true)) {
            $this->fareTab = 'daily';
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, mixed> | null
     */
    private function validateWithNotification(array $payload, array $rules): ?array
    {
        try {
            return Validator::make($payload, $rules)->validate();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Please check the form values')
                ->body((string) collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            return null;
        }
    }

    private function ensureBenchContextReady(): bool
    {
        if (! Car::query()->exists()) {
            Notification::make()
                ->title('No vehicles found')
                ->body('Create at least one vehicle first.')
                ->warning()
                ->send();

            return false;
        }

        if (! PriceType::query()->exists()) {
            Notification::make()
                ->title('No price types found')
                ->body('Create at least one price type first.')
                ->warning()
                ->send();

            return false;
        }

        if (! $this->benchCarId || ! Car::query()->whereKey($this->benchCarId)->exists()) {
            Notification::make()
                ->title('Select a vehicle')
                ->warning()
                ->send();

            return false;
        }

        if (! $this->benchPriceTypeId || ! PriceType::query()->whereKey($this->benchPriceTypeId)->exists()) {
            Notification::make()
                ->title('Select a price type')
                ->warning()
                ->send();

            return false;
        }

        return true;
    }

    private function loadExtraHourChargeForm(): void
    {
        if (! $this->benchCarId || ! $this->benchPriceTypeId) {
            $this->extraHourChargeIsk = null;

            return;
        }

        $row = ExtraHourFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->first();

        $this->extraHourChargeIsk = $row !== null
            ? (string) (((int) $row->charge_per_extra_hour_cents) / 100)
            : null;
    }

    private function resolveDailyFareForBench(int $days): ?DailyFare
    {
        return DailyFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->where('from_days', '<=', $days)
            ->where('to_days', '>=', $days)
            ->orderBy('from_days', 'desc')
            ->first();
    }

    private function resolveHourlyFareForBench(int $durationMinutes): ?HourlyFare
    {
        return HourlyFare::query()
            ->where('car_id', $this->benchCarId)
            ->where('price_type_id', $this->benchPriceTypeId)
            ->where('min_minutes', '<=', $durationMinutes)
            ->where('max_minutes', '>=', $durationMinutes)
            ->orderBy('min_minutes', 'desc')
            ->first();
    }

    public function getTitle(): string | Htmlable
    {
        $car = Car::query()->find($this->benchCarId);

        if ($car) {
            return $car->name.' - Insert Fares';
        }

        return parent::getTitle();
    }

    public function content(Schema $schema): Schema
    {
        $page = $this;

        return $schema
            ->components([
                SchemaHtml::make(function () use ($page): HtmlString {
                    return new HtmlString(view('filament.resources.daily-fares.fare-workbench', [
                        'fares' => $page,
                    ])->render());
                }),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make()
                    ->visible(fn (): bool => $this->fareTab === 'daily'),
                SchemaHtml::make(function () use ($page): HtmlString {
                    return new HtmlString(view('filament.resources.daily-fares.fare-workbench-footer', [
                        'fares' => $page,
                    ])->render());
                })->visible(fn (): bool => $this->fareTab !== 'daily'),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-daily-fares-page',
            'ir-daily-fares-page--list',
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        $query = static::getResource()::getEloquentQuery();

        if ($this->benchCarId) {
            $query->where('car_id', $this->benchCarId);
        }

        if ($this->benchPriceTypeId) {
            $query->where('price_type_id', $this->benchPriceTypeId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
