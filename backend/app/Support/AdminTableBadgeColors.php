<?php

namespace App\Support;

use App\Enums\BlogPostStatus;
use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Enums\NewsletterCampaignStatus;
use App\Enums\OrderStatus;
use BackedEnum;

class AdminTableBadgeColors
{
    public static function neutral(): string
    {
        return 'gray';
    }

    public static function humanize(mixed $state): string
    {
        $value = $state instanceof BackedEnum ? $state->value : (string) $state;

        return str_replace('_', ' ', ucfirst($value));
    }

    public static function guestHouseStatus(GuestHouseStatus|string $status): string
    {
        $value = $status instanceof GuestHouseStatus ? $status->value : (string) $status;

        return match ($value) {
            'active' => 'success',
            'pending_review' => 'warning',
            'rejected' => 'danger',
            'draft', 'inactive' => 'gray',
            default => 'gray',
        };
    }

    public static function listingApprovalStatus(ListingApprovalStatus|string $status): string
    {
        $value = $status instanceof ListingApprovalStatus ? $status->value : (string) $status;

        return match ($value) {
            'approved' => 'success',
            'pending_review' => 'warning',
            'rejected' => 'danger',
            'draft' => 'gray',
            default => 'gray',
        };
    }

    public static function orderStatus(OrderStatus|string $status): string
    {
        $value = $status instanceof OrderStatus ? $status->value : (string) $status;

        return match ($value) {
            'confirmed' => 'success',
            'stand_by' => 'warning',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public static function guestHouseBookingStatus(GuestHouseBookingStatus|string $status): string
    {
        $value = $status instanceof GuestHouseBookingStatus ? $status->value : (string) $status;

        return match ($value) {
            'confirmed', 'completed' => 'success',
            'pending' => 'warning',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public static function blogPostStatus(BlogPostStatus|string $status): string
    {
        $value = $status instanceof BlogPostStatus ? $status->value : (string) $status;

        return match ($value) {
            'published' => 'success',
            'draft' => 'gray',
            default => 'gray',
        };
    }

    public static function newsletterCampaignStatus(NewsletterCampaignStatus|string $status): string
    {
        $value = $status instanceof NewsletterCampaignStatus ? $status->value : (string) $status;

        return match ($value) {
            'sent' => 'success',
            'draft' => 'gray',
            default => 'gray',
        };
    }

    public static function emailLogStatus(string $status): string
    {
        return match ($status) {
            'queued', 'sent' => 'success',
            'failed' => 'danger',
            default => 'gray',
        };
    }
}
