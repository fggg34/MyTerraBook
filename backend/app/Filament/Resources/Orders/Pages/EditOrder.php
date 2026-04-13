<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Confirm order')
                ->color('success')
                ->visible(fn () => $this->record->order_status === OrderStatus::Pending
                    || $this->record->order_status === OrderStatus::StandBy)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->transitionOrderStatus(OrderStatus::Confirmed);
                    $this->refreshFormData(['order_status']);
                }),
            DeleteAction::make(),
        ];
    }
}
