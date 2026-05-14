<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateLocation extends CreateRecord
{
    protected static string $resource = LocationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(): void
    {
        parent::mount();

        // #region agent log
        @file_put_contents(
            '/Users/anxhelo/Desktop/MyTerraRental/.cursor/debug-89c176.log',
            json_encode([
                'sessionId' => '89c176',
                'runId' => 'initial',
                'hypothesisId' => 'H1',
                'location' => 'CreateLocation.php:mount',
                'message' => 'CreateLocation runtime page config',
                'data' => [
                    'pageClasses' => $this->getPageClasses(),
                    'maxContentWidth' => $this->getMaxContentWidth() instanceof \UnitEnum ? $this->getMaxContentWidth()->value : $this->getMaxContentWidth(),
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-location-form-page',
            'ir-location-form-page--create',
        ];
    }
}
