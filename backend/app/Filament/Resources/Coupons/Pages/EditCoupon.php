<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeader(): ?View
    {
        $coupon = $this->getRecord();
        if (! $coupon instanceof Coupon) {
            return null;
        }

        $expired = $coupon->valid_to && $coupon->valid_to->endOfDay()->isPast();
        $status = $expired ? 'Expired' : ($coupon->is_active ? 'Active' : 'Inactive');

        return view('filament.panel.edit-record-header', [
            'title' => $coupon->code,
            'backUrl' => CouponResource::getUrl('index'),
            'backLabel' => 'Discount codes',
            'pills' => [
                ['label' => $status, 'tone' => $status === 'Active' ? 'ok' : 'warn'],
            ],
            'actions' => $this->getCachedHeaderActions(),
            'sections' => [
                'tb-coupon-code' => 'Code',
                'tb-coupon-validity' => 'Validity',
            ],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
