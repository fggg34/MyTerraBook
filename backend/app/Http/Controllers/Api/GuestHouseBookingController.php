<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GuestHouseBookingRequest;
use App\Http\Resources\Api\GuestHouseBookingResource;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Services\Email\EmailService;
use App\Services\Email\GuestHouseBookingEmailPayload;
use App\Services\GuestHouseAvailabilityService;
use App\Services\GuestHouseQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GuestHouseBookingController extends Controller
{
    public function __construct(
        private readonly GuestHouseQuoteService $quoteService,
        private readonly GuestHouseAvailabilityService $availabilityService,
        private readonly EmailService $email,
    ) {}

    public function store(GuestHouseBookingRequest $request): JsonResponse
    {
        $house = GuestHouse::query()
            ->where('slug', $request->string('guest_house_slug'))
            ->where('status', GuestHouseStatus::Active)
            ->firstOrFail();

        $checkIn = $request->string('check_in')->toString();
        $checkOut = $request->string('check_out')->toString();

        if ($this->availabilityService->hasBookingConflict($house->id, $checkIn, $checkOut)) {
            return response()->json(['message' => 'These dates are no longer available.'], 409);
        }

        try {
            $quote = $this->quoteService->quote(
                $house,
                $checkIn,
                $checkOut,
                $request->integer('guests_count'),
                $request->input('coupon_code'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $booking = DB::transaction(function () use ($request, $house, $checkIn, $checkOut, $quote) {
            if ($this->availabilityService->hasBookingConflict($house->id, $checkIn, $checkOut)) {
                throw new InvalidArgumentException('These dates are no longer available.');
            }

            return GuestHouseBooking::query()->create([
                'guest_house_id' => $house->id,
                'user_id' => $request->user()?->id,
                'status' => GuestHouseBookingStatus::Pending,
                'guest_name' => $request->string('guest_name'),
                'guest_email' => $request->string('guest_email'),
                'guest_phone' => $request->string('guest_phone'),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $quote['nights'],
                'guests_count' => $request->integer('guests_count'),
                'base_total' => $quote['base_total'],
                'cleaning_fee' => $quote['cleaning_fee'],
                'security_deposit' => $quote['security_deposit'],
                'discount_amount' => $quote['discount_amount'],
                'tax_amount' => $quote['tax_amount'],
                'total_amount' => $quote['total_amount'],
                'coupon_code' => $request->input('coupon_code'),
                'coupon_id' => $quote['coupon_id'],
                'special_requests' => $request->input('special_requests'),
            ]);
        });

        $booking->load('guestHouse');

        $this->sendBookingEmails($booking, $house);

        return response()->json([
            'data' => new GuestHouseBookingResource($booking),
        ], 201);
    }

    private function sendBookingEmails(GuestHouseBooking $booking, GuestHouse $house): void
    {
        $payload = GuestHouseBookingEmailPayload::for($booking);

        if ($booking->guest_email) {
            $this->email->send('gh_booking_received', $booking->guest_email, $payload);
        }

        if ($hostEmail = $house->host?->email) {
            $this->email->send('gh_booking_new_host', $hostEmail, $payload);
        }
    }
}
