<?php

namespace Tests\Unit;

use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Enums\OrderStatus;
use App\Support\AdminTableBadgeColors;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTableBadgeColorsTest extends TestCase
{
    #[Test]
    public function guest_house_pending_review_maps_to_warning(): void
    {
        $this->assertSame('warning', AdminTableBadgeColors::guestHouseStatus(GuestHouseStatus::PendingReview));
        $this->assertSame('warning', AdminTableBadgeColors::guestHouseStatus('pending_review'));
    }

    #[Test]
    public function guest_house_active_maps_to_success(): void
    {
        $this->assertSame('success', AdminTableBadgeColors::guestHouseStatus(GuestHouseStatus::Active));
    }

    #[Test]
    public function guest_house_rejected_maps_to_danger(): void
    {
        $this->assertSame('danger', AdminTableBadgeColors::guestHouseStatus(GuestHouseStatus::Rejected));
    }

    #[Test]
    public function guest_house_draft_maps_to_gray(): void
    {
        $this->assertSame('gray', AdminTableBadgeColors::guestHouseStatus(GuestHouseStatus::Draft));
    }

    #[Test]
    public function neutral_returns_gray(): void
    {
        $this->assertSame('gray', AdminTableBadgeColors::neutral());
    }

    #[Test]
    public function listing_approval_status_maps_semantically(): void
    {
        $this->assertSame('success', AdminTableBadgeColors::listingApprovalStatus(ListingApprovalStatus::Approved));
        $this->assertSame('warning', AdminTableBadgeColors::listingApprovalStatus(ListingApprovalStatus::PendingReview));
        $this->assertSame('danger', AdminTableBadgeColors::listingApprovalStatus(ListingApprovalStatus::Rejected));
        $this->assertSame('gray', AdminTableBadgeColors::listingApprovalStatus(ListingApprovalStatus::Draft));
    }

    #[Test]
    public function order_confirmed_maps_to_success(): void
    {
        $this->assertSame('success', AdminTableBadgeColors::orderStatus(OrderStatus::Confirmed));
        $this->assertSame('success', AdminTableBadgeColors::orderStatus('confirmed'));
    }

    #[Test]
    public function order_stand_by_maps_to_warning(): void
    {
        $this->assertSame('warning', AdminTableBadgeColors::orderStatus(OrderStatus::StandBy));
    }

    #[Test]
    public function order_cancelled_maps_to_danger(): void
    {
        $this->assertSame('danger', AdminTableBadgeColors::orderStatus(OrderStatus::Cancelled));
    }

    #[Test]
    public function humanize_formats_enum_values(): void
    {
        $this->assertSame('Pending review', AdminTableBadgeColors::humanize(GuestHouseStatus::PendingReview));
        $this->assertSame('Stand by', AdminTableBadgeColors::humanize('stand_by'));
    }
}
