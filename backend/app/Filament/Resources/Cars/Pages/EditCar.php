<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCar extends EditRecord
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Back')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(fn (): string => CarResource::getUrl('index'))
                ->tooltip('Return to vehicles list'),
            Action::make('previewStorefront')
                ->label('Preview')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => config('app.frontend_url').'/cars/'.$this->getRecord()->getKey())
                ->openUrlInNewTab()
                ->tooltip('Open this vehicle on the rental site (new tab).'),
            DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->tooltip('Save changes (⌘S or Ctrl+S)');
    }
}
