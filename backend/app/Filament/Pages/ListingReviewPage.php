<?php

namespace App\Filament\Pages;

use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Filament\Resources\Cars\CarResource;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Services\Email\EmailService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Facades\FilamentView;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ListingReviewPage extends Page
{
    protected static ?string $navigationLabel = 'Host listing approvals';

    protected static ?string $title = 'Host listing approvals';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $slug = 'listing-approvals';

    protected string $view = 'filament.pages.listing-review';

    /** @var Collection<int, GuestHouse> */
    public Collection $pendingGuestHouses;

    /** @var Collection<int, Car> */
    public Collection $pendingCars;

    public string $activeTab = 'all';

    public function mount(): void
    {
        $this->refreshQueue();
    }

    public function refreshQueue(): void
    {
        $this->pendingGuestHouses = GuestHouse::query()
            ->with('host')
            ->where('status', GuestHouseStatus::PendingReview)
            ->orderByDesc('submitted_at')
            ->get();

        $this->pendingCars = Car::query()
            ->with(['host', 'subCategory.mainCategory'])
            ->where('listing_status', ListingApprovalStatus::PendingReview)
            ->orderByDesc('submitted_at')
            ->get();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->count()
            + Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public function setActiveTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'guesthouses', 'vehicles'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function getQueueItemsProperty(): Collection
    {
        $guesthouses = $this->pendingGuestHouses->map(fn (GuestHouse $house): array => [
            'key' => "guesthouse-{$house->id}",
            'entity' => 'guesthouse',
            'entity_id' => $house->id,
            'type_label' => 'Guesthouse',
            'type_color' => 'purple',
            'name' => $house->name,
            'host_name' => $house->host?->name ?? '—',
            'host_email' => $house->host?->email,
            'context_label' => 'Location',
            'context_value' => collect([$house->address, $house->city, $house->country])
                ->filter(fn (?string $part) => filled(trim((string) $part)))
                ->implode(', ') ?: '—',
            'details' => sprintf(
                '%s · %d guests · %d bed · €%s/night',
                ucfirst($house->type?->value ?? '—'),
                $house->max_guests,
                $house->bedrooms,
                number_format($house->base_price_per_night / 100, 2),
            ),
            'submitted_at' => $house->submitted_at,
            'image_url' => $house->og_image ? Storage::disk('public')->url($house->og_image) : null,
            'edit_url' => GuestHouseResource::getUrl('edit', ['record' => $house]),
        ]);

        $vehicles = $this->pendingCars->map(fn (Car $car): array => [
            'key' => "vehicle-{$car->id}",
            'entity' => 'vehicle',
            'entity_id' => $car->id,
            'type_label' => $car->subCategory?->mainCategory?->name ?? 'Vehicle',
            'type_color' => 'blue',
            'name' => $car->name,
            'host_name' => $car->host?->name ?? '—',
            'host_email' => $car->host?->email,
            'context_label' => 'Category',
            'context_value' => $car->subCategory?->name ?? '—',
            'details' => sprintf(
                '%s · %s · %d unit(s)',
                ucfirst($car->transmission ?? '—'),
                ucfirst($car->fuel_type ?? '—'),
                $car->units_available,
            ),
            'submitted_at' => $car->submitted_at,
            'image_url' => $car->main_image_path ? Storage::disk('public')->url($car->main_image_path) : null,
            'edit_url' => CarResource::getUrl('edit', ['record' => $car]),
        ]);

        $items = match ($this->activeTab) {
            'guesthouses' => $guesthouses,
            'vehicles' => $vehicles,
            default => $guesthouses->concat($vehicles),
        };

        return $items
            ->sortByDesc(fn (array $item) => $item['submitted_at']?->timestamp ?? 0)
            ->values();
    }

    public function getPendingGuestHouseCountProperty(): int
    {
        return $this->pendingGuestHouses->count();
    }

    public function getPendingVehicleCountProperty(): int
    {
        return $this->pendingCars->count();
    }

    public function getPendingTotalCountProperty(): int
    {
        return $this->pendingGuestHouseCount + $this->pendingVehicleCount;
    }

    public function approveGuestHouse(int $guestHouseId): void
    {
        $house = GuestHouse::query()->findOrFail($guestHouseId);
        abort_unless($house->status === GuestHouseStatus::PendingReview, 404);

        $house->update([
            'status' => GuestHouseStatus::Active,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        $this->sendListingEmail('listing_approved', $house->loadMissing('host')->host, $house->name);

        Notification::make()->title('Guesthouse approved')->success()->send();
        $this->refreshQueue();
    }

    public function approveCar(int $carId): void
    {
        $car = Car::query()->findOrFail($carId);
        abort_unless($car->listing_status === ListingApprovalStatus::PendingReview, 404);

        $car->update([
            'listing_status' => ListingApprovalStatus::Approved,
            'is_active' => true,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        $this->sendListingEmail('listing_approved', $car->loadMissing('host')->host, $car->name);

        Notification::make()->title('Vehicle approved')->success()->send();
        $this->refreshQueue();
    }

    private function sendListingEmail(string $templateKey, ?\App\Models\User $host, ?string $listingName, ?string $rejectionReason = null): void
    {
        if ($host?->email === null || $host->email === '') {
            return;
        }

        app(EmailService::class)->send($templateKey, $host->email, [
            'host_name' => $host->name,
            'listing_name' => (string) $listingName,
            'rejection_reason' => (string) $rejectionReason,
        ]);
    }

    public function rejectGuestHouseAction(): Action
    {
        return Action::make('rejectGuestHouse')
            ->label('Reject guesthouse')
            ->color('danger')
            ->form([
                Textarea::make('rejection_reason')->label('Reason for host')->required()->maxLength(2000),
            ])
            ->action(function (array $arguments, array $data): void {
                $house = GuestHouse::query()->findOrFail($arguments['guestHouseId']);
                $house->update([
                    'status' => GuestHouseStatus::Rejected,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
                $this->sendListingEmail('listing_rejected', $house->loadMissing('host')->host, $house->name, $data['rejection_reason']);
                Notification::make()->title('Guesthouse rejected')->warning()->send();
                $this->refreshQueue();
            });
    }

    public function requestGuestHouseChangesAction(): Action
    {
        return Action::make('requestGuestHouseChanges')
            ->label('Request changes (guesthouse)')
            ->color('warning')
            ->modalHeading('Request changes')
            ->form(self::requestChangesFormFields())
            ->action(function (array $arguments, array $data): void {
                $house = GuestHouse::query()->findOrFail($arguments['guestHouseId']);

                if ($data['edit_myself'] ?? false) {
                    $url = GuestHouseResource::getUrl('edit', ['record' => $house]);
                    $this->redirect($url, navigate: FilamentView::hasSpaMode($url));

                    return;
                }

                $house->update([
                    'status' => GuestHouseStatus::Draft,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
                Notification::make()->title('Changes requested')->info()->send();
                $this->refreshQueue();
            });
    }

    public function rejectCarAction(): Action
    {
        return Action::make('rejectCar')
            ->label('Reject vehicle')
            ->color('danger')
            ->form([
                Textarea::make('rejection_reason')->label('Reason for host')->required()->maxLength(2000),
            ])
            ->action(function (array $arguments, array $data): void {
                $car = Car::query()->findOrFail($arguments['carId']);
                $car->update([
                    'listing_status' => ListingApprovalStatus::Rejected,
                    'is_active' => false,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
                $this->sendListingEmail('listing_rejected', $car->loadMissing('host')->host, $car->name, $data['rejection_reason']);
                Notification::make()->title('Vehicle rejected')->warning()->send();
                $this->refreshQueue();
            });
    }

    public function requestCarChangesAction(): Action
    {
        return Action::make('requestCarChanges')
            ->label('Request changes (vehicle)')
            ->color('warning')
            ->modalHeading('Request changes')
            ->form(self::requestChangesFormFields())
            ->action(function (array $arguments, array $data): void {
                $car = Car::query()->findOrFail($arguments['carId']);

                if ($data['edit_myself'] ?? false) {
                    $url = CarResource::getUrl('edit', ['record' => $car]);
                    $this->redirect($url, navigate: FilamentView::hasSpaMode($url));

                    return;
                }

                $car->update([
                    'listing_status' => ListingApprovalStatus::Draft,
                    'is_active' => false,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'rejection_reason' => $data['rejection_reason'],
                ]);
                Notification::make()->title('Changes requested')->info()->send();
                $this->refreshQueue();
            });
    }

    /** @return array<int, Toggle|Textarea> */
    public static function requestChangesFormFields(): array
    {
        return [
            Toggle::make('edit_myself')
                ->label('I will edit this listing myself')
                ->helperText('Opens the admin editor. The listing stays in the approval queue until you approve it.')
                ->live(),
            Textarea::make('rejection_reason')
                ->label('Message to host')
                ->helperText('Sent to the host when you return the listing for changes.')
                ->maxLength(2000)
                ->required(fn (Get $get): bool => ! $get('edit_myself'))
                ->visible(fn (Get $get): bool => ! $get('edit_myself')),
        ];
    }

    protected function getActions(): array
    {
        return [
            $this->rejectGuestHouseAction(),
            $this->requestGuestHouseChangesAction(),
            $this->rejectCarAction(),
            $this->requestCarChangesAction(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openGuestHouses')
                ->label('All guesthouses')
                ->url(GuestHouseResource::getUrl('index'))
                ->color('gray'),
            Action::make('openCars')
                ->label('All vehicles')
                ->url(CarResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
