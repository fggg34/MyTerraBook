<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Enums\GuestHouseStatus;
use App\Filament\GuestHouse\Resources\Concerns\NormalizesGuestHouseFormData;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Models\GuestHouse;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

class EditGuestHouse extends EditRecord
{
    use NormalizesGuestHouseFormData;

    protected static string $resource = GuestHouseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->normalizeGuestHouseFormDataForFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeGuestHouseFormDataForSave($data);
    }

    protected function afterSave(): void
    {
        $this->syncGalleryImages($this->record);
        $this->syncThumbnailFromGallery($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('previewStorefront')
                ->label('Preview listing')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => config('app.frontend_url').'/guesthouses/'.$this->getRecord()->slug)
                ->openUrlInNewTab()
                ->tooltip('Open this guesthouse on the site (new tab).'),
            DeleteAction::make()->extraAttributes(['class' => 'tb-hidden-action']),
        ];
    }

    public function getHeader(): ?View
    {
        $house = $this->getRecord();
        if (! $house instanceof GuestHouse) {
            return null;
        }

        $status = match ($house->status) {
            GuestHouseStatus::Active => 'Active',
            GuestHouseStatus::PendingReview => 'Pending review',
            GuestHouseStatus::Rejected => 'Rejected',
            GuestHouseStatus::Inactive => 'Inactive',
            default => 'Draft',
        };

        return view('filament.panel.edit-record-header', [
            'title' => $house->name,
            'backUrl' => GuestHouseResource::getUrl('index'),
            'backLabel' => 'Guest houses',
            'pills' => [
                ['label' => $status, 'tone' => $house->status === GuestHouseStatus::Active ? 'ok' : 'warn'],
            ],
            'actions' => $this->getCachedHeaderActions(),
            'sections' => [
                'tb-gh-photos' => 'Photos',
                'tb-gh-listing' => 'Listing card',
                'tb-gh-stay' => 'Stay details',
                'tb-gh-pricing' => 'Pricing & fees',
                'tb-gh-rules' => 'House rules & check-in',
                'tb-gh-address' => 'Address & map',
                'tb-gh-amenities' => 'Amenities',
                'tb-gh-seo' => 'SEO',
            ],
        ]);
    }
}
